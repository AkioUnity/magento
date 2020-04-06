<?php
//https://pricebusters.furniture/cai_product_delete.php
echo "<pre>";
ini_set('max_execution_time', 0);
require_once('app/Mage.php');
umask(0);
Mage::app();
$sku='2253W';
Mage::register('isSecureArea', true);
$curProduct = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
try {
    $curProduct->delete();
    echo $sku . " Deleted<br>";
} catch (Exception $e) {
    echo $e;
    echo $sku . " Not Deleted<br>";
    echo $e->getTraceAsString();
}
Mage::unregister('isSecureArea');
echo date('d-m-Y h:i:s a') . ' >> updated successfully. <br>';

