<?php
//https://pricebusters.furniture/cai_product_status.php
echo "<pre>";
ini_set('max_execution_time', 0);
require_once('app/Mage.php');
umask(0);
Mage::app();
$chunk = 50;
echo date('d-m-Y h:i:s a') . ' >> Started updating....<br>';
$allProducts = Mage::getModel('catalog/product')->getCollection()
    ->addAttributeToFilter(
        array(
            array('attribute'=>'status', 'eq'=>'2'),
        )
    );
foreach( $allProducts as $zProduct) {
    $_product = Mage::getModel('catalog/product')->load($zProduct->getId());
    $_product->setStatus(0);
    $_product->save();
}
echo date('d-m-Y h:i:s a') . ' >> updated successfully. <br>';

