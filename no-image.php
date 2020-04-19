<?php
/**
 * File: Update The Product Price
 * Coaster catalogs 
 * @SGDEV
 * v:1.0
 */
ini_set('max_execution_time', 2000);
ini_set('display_errors', 1);
set_time_limit(0);
require_once('app/Mage.php'); 
umask(0);
Mage::app();
echo '>> List Of Product SKU (no-image):<br>';
/*$_product = Mage::getModel('catalog/product')->load('315701F');
var_dump($_product->getImage());
echo 'Image:' . $_product->getImage() . ':';
die;*/
$Products = Mage::getModel('catalog/product')->getCollection(); 
$ids = $Products->getAllIds();
echo '>>Total Product: ' . count($ids) . '<br>';
$SKUs = array();
foreach($Products as $Product) {
  $_product = Mage::getModel('catalog/product')->load($Product->getId());
  if($_product->getImage() == '' || $_product->getImage() == 'NULL' || $_product->getImage() == NULL){
    $SKUs[] = $Product->getSku();
    echo $Product->getSku() . ' : ' . $Product->getId() . ' : ' . $_product->getImage() . '<br>';
  } else {
    echo $Product->getSku() . ' : ' . $Product->getId() . ' : ' . $_product->getImage() . '<br>';
  }
}
echo '<pre>'; 
print_r($SKUs); 
echo '</pre>';
die;
//315701F