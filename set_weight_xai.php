<?php
echo "<pre>";
ini_set('max_execution_time', 0);
require_once('app/Mage.php'); 
umask(0);
//https://pricebusters.furniture/set_weight_xai.php
Mage::app();
$chunk = 50;
echo date('d-m-Y h:i:s a') . ' >> Set Weight....<br>';
//$connection = Mage::getModel('core/resource')->getConnection('core_read');
//Status =>  1:Enabled, 2:Disabled
$collection = Mage::getModel('catalog/product')->getCollection();
//$collection->addAttributeToSelect('weight');
$allProducts = $collection->addAttributeToFilter(
        array(
            array('attribute'=>'weight', 'lt'=>200),
        )
    );
$allProducts = $collection->addAttributeToFilter(
    array(
        array('attribute'=>'box_height', 'lt'=>0.2),
    )
);
$cn=0;
echo 'cnt:'.count($allProducts);
foreach( $allProducts as $zProduct) {
    $cn++;
    if ($cn>50)
        break;
    $_product = Mage::getModel('catalog/product')->load($zProduct->getId());
    $_product->setWeight(200);
    $_product->save();
    print_r($cn.' ');
    print_r('  '.$_product->getWeight());
    print_r(' Box:'.$_product->getBoxHeight());
    echo ('<br>');

//    if( $_product->getIsCoaster() == '1' ) {
//        $prods[] = $zProduct->getSku();
//    }
}
echo date('d-m-Y h:i:s a') . ' >> Weight updated successfully. <br>';
