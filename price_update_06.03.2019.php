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

$products = '360035, 
360032,
881074,
881075,
360041,
801125,
360049,
360052,
360056,
360059,
360047,
360037,
801928,
705888,
461114,
206285,
206295,
108101,
461105,
360064,
360065,
802489,
802488,
508184,
600436,
603170,
506753,
802596,
802588,
508121,
721378,
721438,
300940,
300836,
802492,
802576,
108151,
108168,
206392,
801972,
106399,
190251,
190255,
930066,
930070,
950997,
950977,
190285,
300515F,
300768Q,
222411Q,
206291Q,
300694T,
300694Q,
300694F,
301121Q,
301133Q,
301160F,
301160Q,
302075T,
302075F,
302075Q,
302075KE,
302075KW,
302101Q,
302101F,
302107Q,
400961T,
107821,
721457,
802013,
360080,
650303,
650313,
721881,
802666,
802659,
802141,
802479,
802480,
801950,
108441,
190512,
207019,
212439,
302036Q,
508481,
508381,
508383,
508392,
930131,
930132,
930137,
722268,
360139,
360143,
950822,
950829,
950830,
722271,
951050,
951051,
215712,
215720,
190712,
802241,
950832,
950836,
950837,
950839,
950810,
722418,
302132,
302133,
302334Q,
910209,
190751,
802669,
722222,
950905,
951032,
910202,
802031,
802170,
721828,
721788,
721531,
722498,
722499,
722508,
722509,
722519,
108175,
951009,
315702,
721891,
190976,
801848,
300694KEB1,
300694KWB1,
190755,
721787,
951072,
802848,
508541,
508542,
508543,
802235,
CP48RD-10,
CP50RD-10,
722572,
722562,
722563,
360185,
708072,
505395,
505396,
505397,
508511,
508512,
508513,
508631,
508632,
508634,
508635,
980031,
903033,
508721,
508722,
951737,
192075,
722351,
722373,
950876,
804221,
950849,
109418,
551325,
215841Q';

$products = array_map('trim', explode(',',$products));
echo json_encode($products , true);
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
print_r($products);
//die;
while($Products) {
  echo 'sgdev';
  //$_product = Mage::getModel('catalog/product')->load($Product->getId());
  var_dump($Product); die;
  $_product = Mage::getModel('catalog/product')->load($Product);
  $pWeight = $_product->getWeight();
  $pStatus = $_product->getStatus();
  if($pWeight >= 70 && $pWeight <= 150) {
    if($_product->getIsCoaster() == '1') {
      $coasterProduct = 'Coaster';
      $OldPrice = $_product->getPrice();
      $NewPrice = $_product->getPrice() + 20;      

      if($pStatus == '1') {
        $enabled_product_array_temp = array(
          'SKU'=> $Product->getSku(),
          'Name'=> $_product->getName(),
          'OldPrice'=> $OldPrice,
          'NewPrice'=> $NewPrice,
          'IsCoaster'=> $coasterProduct,
          'Weight'=> $pWeight,
          'Status'=> $productStatus[$pStatus]
        );
        array_push($enabled_product_array, $enabled_product_array_temp);

        //$_product->setPrice($NewPrice);
        //$_product->save();
        //echo $_product->getPrice() . ' of (enabled) ' . $_product->getSku() . '<br>';
      } else if($pStatus == '2') {
        /* $disabled_product_array_temp = array(
          'SKU'=> $_product->getSku(),
          'Name'=> $_product->getName(),
          'OldPrice'=> $OldPrice,
          'NewPrice'=> $NewPrice,
          'IsCoaster'=> $coasterProduct,
          'Weight'=> $pWeight,
          'Status'=> $productStatus[$pStatus]
        );
        array_push($disabled_product_array, $disabled_product_array_temp); */

        //echo $_product->getPrice() . ' of (disabled) ' . $_product->getSku() . '<br>';
      }
    } else {
      //$coasterProduct = 'Not Coaster';
    }
    /* if($i == 10) {
      break;
    }
    $i++; */
  }
  sleep(rand(0,1));
}
//echo '<pre>'; 
//print_r($enabled_product_array); 
//array_to_csv_download2($enabled_product_array, "Enabled_Product_" . date("Y-m-d") . ".csv");
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