<?php
//https://pricebusters.furniture/cai_product.php
//echo "<pre>";
ini_set('max_execution_time', 0);
require_once('app/Mage.php');
umask(0);
Mage::app();
$chunk = 50;
//print_r (date('d-m-Y h:i:s a') . ' >> Started ....<br>');
//$connection = Mage::getModel('core/resource')->getConnection('core_read');
//Status =>  1:Enabled, 2:Disabled
//$allProducts = Mage::getModel('catalog/product')->getCollection()
//    ->addAttributeToFilter(
//        array(
//            array('attribute'=>'status', 'neq'=>'2'),
//        )
//    );
//echo (count($allProducts));
$sku='506412';
$curProduct = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
//print_r($curProduct);
//print_r($curProduct->getStatus());
print_r($curProduct->getData());
//print_r($curProduct['_data']);
//_dataSaveAllowed:protected
//$curProduct->setStatus(1);Hello.
//I have done multiple jobs with CSS, HTML, Javascript, PHP, Website Design which are the skills required to get this job done.
//will provide a complete solution with unique design and development time as per your requirement.
//I will get this project(Front-End Developer ) done with complete attention to detail.
//I'm interested to hear more about the project and about the subject matter of the lectures.
//Best regards.
//Xai
//$curProduct->save();

$filtercode = _sendRequest('getFilter?ProductNumber='.$sku);
$productList =  _sendRequest('GetProductList?filtercode='.$filtercode.'&customernumber=16998');
print_r($productList);
foreach($productList as $product) {
    $sku = $product->ProductNumber;
    $status = ($product->IsDiscontinued) ? 0 : 1;
    if ($status==0)
        echo 'Product: '.$sku.' has status: '.$status.'<br>';
    $updateProduct = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
    $updateProduct->setStatus($status);
    $updateProduct->save();
    sleep(rand(1,2));
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