<?php
//https://pricebusters.furniture/cai_disable_qty.php
echo "<pre>";
ini_set('max_execution_time', 0);
require_once('app/Mage.php');
umask(0);
Mage::app();
$chunk = 50;
echo date('d-m-Y h:i:s a') . ' >> ...<br>';
$allProducts = Mage::getModel('catalog/product')->getCollection()
    ->addAttributeToFilter(
        array(
            array('attribute'=>'status', 1),
        )
    );
$cn=0;
foreach( $allProducts as $zProduct) {
    $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($zProduct);
    if ($stock->getQty()==0){
        $_product = Mage::getModel('catalog/product')->load($zProduct->getId());
        echo $_product->getSku().' <br>';
//        $cn++;
//        if ($cn>20)
//            break;
        $_product->setStatus(0);
        $_product->save();
    }
}
echo date('d-m-Y h:i:s a') . ' >> updated successfully. <br>';

