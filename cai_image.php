<?php
/**
 * File: Update The Product Price
205461QB3
 *
 * @SGDEV
 * v:1.0
 */
ini_set('max_execution_time', 0);
ini_set('display_errors', 1);
//set_time_limit(0);
require_once('app/Mage.php');
umask(0);
Mage::app();
// $chunk = 50;
//https://pricebusters.furniture/cai_image.php
echo "<pre>";
echo date('d-m-Y h:i:s a') . ' >> Started updating....<br>';

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
$cn=0;
foreach ($_products as $Product) {
    $_product = Mage::getModel('catalog/product')->load($Product->getId());
    if ($_product->getImage() == '' || $_product->getSmallImage() == '' || $_product->getThumbnail() == '') {
            $prods[] = $Product->getSku();
            $cn++;
//            if ($cn == 1)
//                break;
    }
}

//echo '<pre>'; print_r($prods); echo 'xx</pre>';
//die();

$productChunks = array_chunk($prods, 10); // Creating Product Chunks In the form of array
print_r($productChunks);
$prodDetails = array();
$fl = 0;

foreach ($productChunks as $productChunk) {
    $filtercode = _sendRequest('getFilter?ProductNumber='.implode(',', $productChunk));
    $productList =  _sendRequest('GetProductList?filtercode='.$filtercode.'&customernumber=16998');
//    print_r($productList);
    foreach ($productList as $pList) {
        // SKU Matched, so we will start the Image URL Fetching
        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $pList->ProductNumber);
        $prodNums['prodSku'] = $pList->ProductNumber;
        $prodImages = array();
        for ($i = 1; $i <= $pList->NumImages; $i++) {
            /* ====== Image Code ====== */
            // $urlToImage = "http://assets.coasteramer.com/productpictures/".$pList->ProductNumber."/".($i+1)."x900.jpg";
            $prodImages[] = "http://assets.coasteramer.com/productpictures/" . $pList->ProductNumber . "/" . $i . "x900.jpg";
        }
        $prodNums['prodImages'] = $prodImages;

        array_push($prodDetails, $prodNums);
    }
//    break;
    sleep(rand(1, 2));
}
echo '<pre>';
print_r($prodDetails);
echo 'xx</pre>';
// die();

$incI = 1;
$newProdDetails = array_reverse($prodDetails);
//'http://assets.coasteramer.com/productpictures/107822/1.jpg')));
foreach ($newProdDetails as $pDetails) {
    $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $pDetails['prodSku']);
    foreach ($pDetails['prodImages'] as $key => $pImage) {

        // $product = Mage::getModel('catalog/product')->load($product);
        $urlToImage = $pImage;
        $mySaveDir = Mage::getBaseDir('media') . DS . 'my_images' . DS; // media/my_images
        // $mySaveDir = 'coaster_images_full/';
        $filename = $pDetails['prodSku'] . '-' . time() . basename($urlToImage);
        $completeSaveLoc = $mySaveDir . $filename;
        // echo basename($urlToImage);
        file_get_contents($urlToImage);
        // die();
        try {
            file_put_contents($completeSaveLoc, file_get_contents($urlToImage));
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }

        try {
            // $product->setMediaGalleryImages($images);
            $product->addImageToMediaGallery($completeSaveLoc, array('image', 'thumbnail', 'small_image'), false, false);
            // $product->setStatus(1);
            $product->save();
            // print_r($product);
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }


        /*$urlToImage = $pImages;
        $filename = $pDetails['prodSku'].'-'.end(explode('/', $urlToImage));
        $source = $urlToImage;
        $dest = Mage::getBaseDir('media') . DS . 'coaster_import_images'. DS . $filename;
        //$dest = Mage::getBaseDir('media') . DS . 'imports'. DS . $filename;*/

        //echo '<br/>'; echo copy($source, $dest);

        /*
          $imgName = $prod['prodSku'].'-'.end(explode('/', $img));
          $source = $img;
          $dest = 'myimg/'.$imgName;
          @copy($source, $dest);
        */

        /* ====== Image Code Ends ====== */
    }

    /* if($fl == 1){
      echo '1 file Uploaded - '.$urlToImage;
      die();
    } */
    // $product->setStatus(1);

    $incI++;
//    die();
}
echo date('d-m-Y h:i:s a') . ' >> Coster products are updated successfully. <br>';


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

die();

echo date('d-m-Y h:i:s a') . ' >> Coster products are updated successfully. <br>';
