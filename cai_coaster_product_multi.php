<?php
//https://pricebusters.furniture/cai_product.php
//echo "<pre>";
ini_set('max_execution_time', 0);
require_once('app/Mage.php');
umask(0);
Mage::app();
print_r (date('d-m-Y h:i:s a') . ' >> Started ....<br>');

$allProducts = Mage::getModel('catalog/product')->getCollection()
    ->addAttributeToFilter(
        array(
            array('attribute'=>'is_coaster', '1'),
        )
    );
echo (count($allProducts).'<br>');
$cn=0;
foreach( $allProducts as $zProduct) {
    $_product = Mage::getModel('catalog/product')->load($zProduct->getId());
    if( $_product->getIsCoaster() == '1' ) {
        $sku=$zProduct->getSku();
        $curProduct = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
//print_r($curProduct->getStatus());
//        print_r($curProduct->getData());
        $filtercode = _sendRequest('getFilter?ProductNumber='.$sku);
        $productList =  _sendRequest('GetProductList?filtercode='.$filtercode.'&customernumber=16998');
        foreach($productList as $product) {
//    print_r($product);
            $cn++;
            $sku = $product->ProductNumber;
            $status = ($product->IsDiscontinued) ? 0 : 1;
            echo ($sku.":".$status.":".$product->PackQty);
            sleep(rand(1,2));
            if ($cn>10)
                die;
        }
    }
}
print_r (date('d-m-Y h:i:s a') . ' >> done ....<br>');
//$sku='3080-1';


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