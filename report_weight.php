<?php
/**
 * File: Report generator script 
 * @SGDEV
 * v:1.0
 */
ini_set('max_execution_time', 2000);
ini_set('display_errors', 1);
set_time_limit(0);
require_once('app/Mage.php'); 
umask(0);
Mage::app();
//echo '>> List Of Product wight in between 70lbs and 150lbs :<br>';

$Products = Mage::getModel('catalog/product')->getCollection(); 
$ids = $Products->getAllIds();
//echo '>>Total Product: ' . count($ids) . '<br>';

$enabled_product_array = array();
$disabled_product_array = array();
$Zero_Weight_Product_array = array();
$productStatus = array(
  '1'=>'Enabled',
  '2'=>'Disabled'
);
$i = 0;
foreach($Products as $Product) {
  $_product = Mage::getModel('catalog/product')->load($Product->getId());
  
  $pStatus = $_product->getStatus(); 
  if($pStatus == '1') {
    //$pWeight = $_product->getWeight();
    if($_product->getIsCoaster() == '1') {
      $coasterProduct = 'Coaster';
    } else {
      $coasterProduct = 'Not Coaster';
    }
    $pLength = $_product->getBoxLength(); 
    //echo '<br>';
    if($pLength == '') {
      $pLength = 0;
    }

    if( $pLength == 0.1) {
      $enabled_product_array_temp = array(
        'SKU'=> $Product->getSku(),
        'Name'=> $_product->getName(),
        'IsCoaster'=> $coasterProduct,
        'Length'=> $pLength,
        'Status'=> $productStatus[$pStatus]
      );
      array_push($enabled_product_array, $enabled_product_array_temp);
    }
    if($i == 10) {
      //break;
    }
    $i++;
  }

  
  /* if($pWeight <= 0) {
    if($_product->getIsCoaster() == '1') {
      $coasterProduct = 'Coaster';
    } else {
      $coasterProduct = 'Not Coaster';
    }
    $Zero_Weight_Product_array_temp = array(
      'SKU'=> $Product->getSku(),
      'Name'=> $_product->getName(),
      'IsCoaster'=> $coasterProduct,
      'Weight'=> $pWeight,
      'Status'=> $productStatus[$pStatus]
    );
    array_push($Zero_Weight_Product_array, $Zero_Weight_Product_array_temp);    
  } */

  /*if($pWeight >= 70 && $pWeight <= 150) {
    if($_product->getIsCoaster() == '1') {
      $coasterProduct = 'Coaster';
    } else {
      $coasterProduct = 'Not Coaster';
    }
    if($pStatus == '1') {
      $enabled_product_array_temp = array(
        'SKU'=> $Product->getSku(),
        'Name'=> $_product->getName(),
        'IsCoaster'=> $coasterProduct,
        'Weight'=> $pWeight,
        'Status'=> $productStatus[$pStatus]
      );
      array_push($enabled_product_array, $enabled_product_array_temp);
    } else if($pStatus == '2') {
      $disabled_product_array_temp = array(
        'SKU'=> $_product->getSku(),
        'Name'=> $_product->getName(),
        'IsCoaster'=> $coasterProduct,
        'Weight'=> $pWeight,
        'Status'=> $productStatus[$pStatus]
      );
      array_push($disabled_product_array, $disabled_product_array_temp);
    }
    if($i == 10) {
      //break;
    }
    $i++;
  }*/
  //sleep(rand(0,1));
}
//print_r($enabled_product_array);
//die;
//echo '<pre>'; 
//print_r($enabled_product_array); 
array_to_csv_download2($enabled_product_array, "Enabled_Product_" . date("Y-m-d") . ".csv");
//sleep(rand(1,2));
//array_to_csv_download2($disabled_product_array, "Disabled_Product_" . date("Y-m-d") . ".csv");
//array_to_csv_download2($Zero_Weight_Product_array, "Zero_Weight_Product_" . date("Y-m-d") . ".csv");
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