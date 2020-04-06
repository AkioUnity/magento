<?php
//https://pricebusters.furniture/cai_delete_multi.php
echo "<pre>";
ini_set('max_execution_time', 0);
require_once('app/Mage.php'); 
umask(0);
//https://pricebusters.furniture/set_weight_xai.php
Mage::app();
$chunk = 50;
echo date('d-m-Y h:i:s a') . ' >> ...<br>';
//$connection = Mage::getModel('core/resource')->getConnection('core_read');
//Status =>  1:Enabled, 2:Disabled
$collection = Mage::getModel('catalog/product')->getCollection();
//$collection->addAttributeToSelect('weight');
$allProducts = $collection->addAttributeToFilter(
        array(
            array('attribute'=>'status', 0),
        )
    );

Mage::register('isSecureArea', true);
echo 'cnt:'.count($allProducts).'<br>';
foreach( $allProducts as $zProduct) {
    $curProduct = Mage::getModel('catalog/product')->load($zProduct->getId());
    try {
        $curProduct->delete();
    } catch (Exception $e) {
        echo $e;
        echo $e->getTraceAsString();
    }
}
Mage::unregister('isSecureArea');
echo date('d-m-Y h:i:s a') . ' >> successfully. <br>';
