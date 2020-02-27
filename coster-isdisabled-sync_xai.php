<?php
/**
 * File: Disable Coster Product if the product discontinued at coster connect
 * http://coasterconnect.coasteramer.com
 * Coaster catalogs 
 * @SGDEV
 * v:1.1
 */
echo "<pre>";
ini_set('max_execution_time', 0);
//set_time_limit(0);
require_once('app/Mage.php'); 
umask(0);
Mage::app();
$chunk = 50;
echo date('d-m-Y h:i:s a') . ' >> Started updating....<br>';
//$connection = Mage::getModel('core/resource')->getConnection('core_read');
//Status =>  1:Enabled, 2:Disabled
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
foreach($productChunks as $productChunk) {
    print_r($productChunk);
    $filtercode = _sendRequest('getFilter?ProductNumber='.implode(',', $productChunk));
    $productList =  _sendRequest('GetProductList?filtercode='.$filtercode.'&customernumber=16998');
    foreach($productList as $product) {
        $sku = $product->ProductNumber;
        $status = ($product->IsDiscontinued) ? 2 : 1;
        if ($status==2)
            echo 'Product: '.$sku.' has status: '.$status.'<br>';
        $updateProduct = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
        $updateProduct->setStatus($status);
        $updateProduct->save();
        sleep(rand(1,2));
    } 
    sleep(rand(2,3));
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
