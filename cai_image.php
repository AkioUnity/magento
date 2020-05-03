<?php
ini_set('max_execution_time', 0);
ini_set('display_errors', 1);
require_once('app/Mage.php');
umask(0);
Mage::app();
// $chunk = 50;
//https://pricebusters.furniture/cai_image.php
$importdate = date("d-m-Y H:i:s", strtotime("now"));
$log = "started at: " . $importdate . "--------------";
Mage::log($log, null, 'cai_image.log', true);

// The API
$_products = Mage::getModel('catalog/product')->getCollection()
    ->addAttributeToFilter(
        array(
            array('attribute' => 'is_coaster', 'eq' => '1')
        )
    )->addAttributeToFilter(
        array(
            array('attribute' => 'status', 'eq' => '1')
        )
    ); // The Query for magento

// Taking Out Products From Query
$cn = 0;
foreach ($_products as $Product) {
    $_product = Mage::getModel('catalog/product')->load($Product->getId());
    if ($_product->getImage() == '' || $_product->getSmallImage() == '' || $_product->getThumbnail() == '') {
        $prods[] = $Product->getSku();
        $cn++;
//            if ($cn == 1)
//                break;
    }
}

$productChunks = array_chunk($prods, 20); // Creating Product Chunks In the form of array
$prodDetails = array();
$fl = 0;
try {
    foreach ($productChunks as $productChunk) {
        $filtercode = _sendRequest('getFilter?ProductNumber=' . implode(',', $productChunk));
        $productList = _sendRequest('GetProductList?filtercode=' . $filtercode . '&customernumber=16998');
        foreach ($productList as $pList) {
            // SKU Matched, so we will start the Image URL Fetching
            $sku = $pList->ProductNumber;
            $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
            $prodNums['prodSku'] = $sku;
            $prodImages = array();
            for ($i = 1; $i <= $pList->NumImages; $i++) {
                $prodImages[] = "http://assets.coasteramer.com/productpictures/" . $sku . "/" . $i . "x900.jpg";
            }
            $prodNums['prodImages'] = $prodImages;
            array_push($prodDetails, $prodNums);
        }
//    break;
        sleep(rand(1, 2));
    }
    print_r($prodDetails);
    $newProdDetails = array_reverse($prodDetails);
//'http://assets.coasteramer.com/productpictures/107822/1.jpg')));
//https://pricebusters.furniture/media/imports/551001-1.jpg
    foreach ($newProdDetails as $pDetails) {
        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $pDetails['prodSku']);
        $id=0;
        foreach ($pDetails['prodImages'] as $key => $pImage) {
            $id++;
            $urlToImage = $pImage;
            $mySaveDir = Mage::getBaseDir('media') . DS . 'imports' . DS; // media/imports
            $filename = $pDetails['prodSku'] . '-' . $id .'.jpg';
            $path = $mySaveDir . $filename;
            file_get_contents($urlToImage);
            file_put_contents($path, file_get_contents($urlToImage));
            if ($id == 1)
                $product->addImageToMediaGallery($path, array('image', 'thumbnail', 'small_image'), false, false);
            else
                $product->addImageToMediaGallery($path, null, false, false);
            $product->save();
            $log = $pDetails['prodSku'].' '.$filename;
            Mage::log($log, null, 'cai_image.log', true);
        }
    }
} catch (Exception $e) {
    Mage::logException($e);
    print_r($e);
}
echo date('d-m-Y h:i:s a') . ' >>successfully. <br>';
$importdate = date("d-m-Y H:i:s", strtotime("now"));
$log = "finished at: " . $importdate;
Mage::log($log, null, 'cai_image.log', true);


function _sendRequest($endpoint)
{
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

die();

echo date('d-m-Y h:i:s a') . ' >> Coster products are updated successfully. <br>';
