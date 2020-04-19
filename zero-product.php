<?php
/**
 * File: Update The Product Quatity
 * Coaster catalogs 
 * @SGDEV
 * v:1.0
 */
ini_set('max_execution_time', 0);
//set_time_limit(0);
require_once('app/Mage.php'); 
umask(0);
Mage::app();
echo date('d-m-Y h:i:s a') . ' >> Started updating....<br>';
$zeroPriceProducts = Mage::getModel('catalog/product')->getCollection()
    ->addAttributeToFilter(
          array(
             array('attribute'=>'price', 'eq'=>'0'),
             array('attribute'=>'price', 'isnull'=>true),
          )
    );
$ids = $zeroPriceProducts->getAllIds();
Mage::getSingleton('catalog/product_action')->updateAttributes(
    $ids,
    array('status' => 2),
    0
);

echo date('d-m-Y h:i:s a') . ' >> Products are updated successfully. <br>';

