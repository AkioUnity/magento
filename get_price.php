<?php
//https://pricebusters.furniture/get_price.php
//echo "<pre>";
ini_set('max_execution_time', 0);
require_once('app/Mage.php');
umask(0);
Mage::app();
$chunk = 50;
print_r (date('d-m-Y h:i:s a') . ' >> Started ....<br>');

$sku='192818';
$curProduct = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
//print_r($curProduct->getStatus());
print_r($curProduct->getData());
$filtercode = _sendRequest('getFilter?ProductNumber='.$sku);
$productList =  _sendRequest('GetPriceList?filtercode='.$filtercode.'&customernumber=16998');
foreach( $productList[0]->PriceList as $ppl) {
    $productPrice = $ppl->Price;
    if($productPrice != '') {
        $curPrice=$curProduct->getPrice();
        if ($curPrice==0)
            echo $sku.'- $'.$productPrice . "\n";
//        $curProduct->setPrice($productPrice * 1.92);
//        $curProduct->save();
    }
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