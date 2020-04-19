<?php
/**
 * File: Update Coaster product selling price to 20%
 * @SGDEV
 * v:1.0
 */
ini_set('display_errors', 1);
set_time_limit(0);
require_once('app/Mage.php'); 
umask(0);
Mage::app();
//echo '>> List Of Product wight in between 70lbs and 150lbs :<br>';

$products = '';

$products = array_map('trim', explode(',', $products));
echo "<pre>";
//$Products = Mage::getModel('catalog/product')->getCollection(); 
//$ids = $Products->getAllIds();
//echo '>>Total Product: ' . count($ids) . '<br>';

$enabled_product_array = array();
$disabled_product_array = array();
$productStatus = array(
  '1'=>'Enabled',
  '2'=>'Disabled'
);
$i = 0;
foreach($products as $sku) {
  //echo 'sgdev';
  //print_r($sku); die;
  //$_product = Mage::getModel('catalog/product')->load($Product->getId());
  //echo $sku = (string)$sku;
  $_product =  Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
  //print_r($_product->getName() . 'fdg');
  //print_r($_product);
  if($_product) {
    $pWeight = $_product->getWeight(); 
    $pStatus = $_product->getStatus();
    if($_product->getIsCoaster() == '1') {
      $coasterProduct = 'Coaster';
    } else {
      $coasterProduct = 'Not Coaster';
    }
    $OldPrice = $_product->getPrice();
    $NewPrice = $_product->getPrice() + 20; 
    $_product->setPrice($NewPrice);
    $_product->save();
    echo $OldPrice . ' -> '  . $NewPrice . ' : ' . $_product->getSku() . ' - updated<br>';
    /*$enabled_product_array_temp = array(
      'SKU'=> $sku,
      'Name'=> $_product->getName(),
      'OldPrice'=> $OldPrice,
      'NewPrice'=> $NewPrice,
      'IsCoaster'=> $coasterProduct,
      'Weight'=> $pWeight,
      'Status'=> $productStatus[$pStatus]
    );

    array_push($enabled_product_array, $enabled_product_array_temp);
      if($i == 10) {
      //break;
    }
    $i++; */
  }
}
//echo '<pre>'; 
//print_r($enabled_product_array); 
array_to_csv_download2($enabled_product_array, "Enabled_Product_" . date("Y-m-d") . ".csv");
//sleep(rand(1,2));
//array_to_csv_download2($disabled_product_array, "Disabled_Product_" . date("Y-m-d") . ".csv");
//print_r($disabled_product_array); 
//echo '</pre>';
die;

function array_to_csv_download1($array, $filename, $delimiter=";") {
  $f = fopen('php://output', 'w');
  foreach ($array as $line) { 
      fputcsv($f, $line, $delimiter); 
  }
  fseek($f, 0);
  header('Content-Type: application/csv');
  header('Content-Disposition: attachment; filename="'.$filename.'";');
  fpassthru($f);
}

function array_to_csv_download2($array, $filename = "export.csv", $delimiter=",") {
  header('Content-Type: application/csv');
  header('Content-Disposition: attachment; filename="'.$filename.'";');
  $f = fopen('php://output', 'w');    
  foreach ($array as $line) {
      fputcsv($f, $line, $delimiter);
  }
} 