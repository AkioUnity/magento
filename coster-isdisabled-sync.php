<?php
/**
 * File: Disable Coster Product if the product discontinued at coster connect
 * Status =>  1:Enabled, 2:Disabled
 * http://coasterconnect.coasteramer.com
 * Coaster catalogs 
 * @SGDEV
 * v:1.3
 * LUD: 16/01/2020
 */
echo "<pre>";
ini_set('max_execution_time', 0);
set_time_limit(0);
require_once('app/Mage.php'); 
umask(0);
Mage::app();
//$connection = Mage::getModel('core/resource')->getConnection('core_read');
$coster_products = get_coster_products();
//print_r($coster_products);
foreach($coster_products as $key=>$coster_product) {   
    if($coster_product['status'] == 'NC') { 
        print_r($coster_product);
        $productChunk = $coster_product['product_chunk'];
        print_r($productChunk);
        $updatedProdutsOfCurrentChunk = array();
        if(!isset($coster_product['filtercode']) || $coster_product['filtercode'] == '') {
            $filtercode = _sendRequest('getFilter?ProductNumber='.implode(',', $productChunk));
        } else {
            $filtercode = $coster_product['filtercode'];
        }
        $productList =  _sendRequest('GetProductList?filtercode='.$filtercode.'&customernumber=16998');
        foreach($productList as $product) {
            $sku = $product->ProductNumber;
            $status = ($product->IsDiscontinued) ? 2 : 1;
            echo 'Product: '.$sku.' has status: '.$status.'<br>';
            $updateProduct = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
            $updateProduct->setStatus($status);
            $updateProduct->save(); 
            array_push($updatedProdutsOfCurrentChunk, $sku);
        } 
        $otherDisabledProdutsOfCurrentChunk = array_diff($productChunk, $updatedProdutsOfCurrentChunk);
        print_r($otherDisabledProdutsOfCurrentChunk);
        foreach($otherDisabledProdutsOfCurrentChunk as $disableProduct) {
            $sku = $disableProduct;
            $status =2;
            echo 'Product: '.$sku.' has status: '.$status.'<br>';
            $updateProduct = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
            $updateProduct->setStatus($status);
            $updateProduct->save();
        }
        $coster_products[$key]['status'] = 'C';
        update_coster_product_helper($coster_products);
    }
    sleep(1);
}

function get_coster_products() {
    $getFromLive = TRUE;
    $cronHelper = (__DIR__) . '/cronhelper/';
    $costerProductsJson = $cronHelper . 'costerproducts.json';
    if(file_exists($costerProductsJson)) {
        $productJsonContent = file_get_contents($costerProductsJson);    
        if($productJsonContent) {
            $productJsonContent = json_decode($productJsonContent, TRUE);
            if( strtotime(date('Y-m-d H:i:s')) < strtotime($productJsonContent['expire_date']) ) {
                $getFromLive = FALSE;
                $coster_products = $productJsonContent['coster_products'];
            }
        } 
    } 
    if($getFromLive) {
        $allProducts = Mage::getModel('catalog/product')->getCollection()
        ->addAttributeToFilter(
            array(
                array('attribute'=>'status', 'eq'=>'1'),
            )
        );
        $ids = $allProducts->getAllIds();
        foreach( $allProducts as $zProduct) {
            $_product = Mage::getModel('catalog/product')->load($zProduct->getId());
            if( $_product->getIsCoaster() == '1' ) {
                $prods[] = $zProduct->getSku();
            }
        }    
        $productChunks = array_chunk($prods , 50);
        $coster_products = array();
        foreach($productChunks as $pc) {
            $filtercode = _sendRequest('getFilter?ProductNumber='.implode(',', $pc));
            $cp_temp = array(
                'status' => 'NC',
                'filtercode' => $filtercode,
                'product_chunk' => $pc
            );
            array_push($coster_products, $cp_temp);
        }  
    }  
    return $coster_products;
}
function update_coster_product_helper($coster_products) {
    $cronHelper = (__DIR__) . '/cronhelper/';
    $costerProductsJson = $cronHelper . 'costerproducts.json';
    $expire_date = date('Y-m-d H:i:s', strtotime("+2 day", strtotime(date('Y-m-d H:i:s'))));
    if(file_exists($costerProductsJson)) {
        $productJsonContent = file_get_contents($costerProductsJson);    
        if($productJsonContent) {
            $productJsonContent = json_decode($productJsonContent, TRUE);
            if( strtotime(date('Y-m-d H:i:s')) < strtotime($productJsonContent['expire_date']) ) {
                $expire_date = $productJsonContent['expire_date'];
            }
        }
    } 
    $jsonContent = array(
        'expire_date' => $expire_date,
        'coster_products' => $coster_products
    );
    $fp = fopen($costerProductsJson, 'w');
    fwrite($fp, json_encode($jsonContent));
    fclose($fp);
    chmod($costerProductsJson, 0777);
}


function _sendRequest($endpoint) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => "http://api.coasteramer.com/api/product/" . $endpoint,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 240,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array("keycode: E122443B8549416BAA0629ED0C"),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
      return false;
    } else {
      return json_decode($response);
    }
}
echo date('d-m-Y h:i:s a') . ' >> Coster products are updated successfully. <br>';
