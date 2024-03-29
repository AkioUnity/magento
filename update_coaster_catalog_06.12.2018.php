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

$connection = Mage::getModel('core/resource')->getConnection('core_read');

/**
 * Get Products from Coster 
 */
//$query      = "SELECT * FROM `xcentia_coster_product`";
$query      = "SELECT * FROM `xcentia_coster_product` LIMIT 10";
$rows       = $connection->fetchAll($query);

echo "<p>Total product: 10719</p>" ; //. count($rows);
echo "<p>Raw Data:</p>";
foreach ($rows as $values) {
    echo "<pre>";
    print_r($values);
    //echo "</pre>";
    
    $sku            = $values['sku'];
    $costerContent  = json_decode($values['content'], TRUE);
    $productName    = $costerContent['Name'];    
    $box_width      = $costerContent['BoxSize']['Width'];
    $box_length     = $costerContent['BoxSize']['Length'];
    $box_height     = $costerContent['BoxSize']['Height'];
    $product        = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku); 
    if ($product){
        $product->setBox_width($box_width);
        $product->setBox_length($box_length);
        $product->setBox_height($box_height);
        $product->save();
    } else {
        echo "Not a valid product. [SKU: " . $sku . ']';
    }
    
    echo "</pre>";
    die;
}


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
