<?php
require_once('app/Mage.php');
umask(0);
Mage::app('admin');
set_time_limit(0);

$productCollection = Mage::getModel('catalog/product')
     ->getCollection()
     ->addAttributeToSelect('*')
     ->joinField('qty',
                 'cataloginventory/stock_item',
                 'qty',
                 'product_id=entity_id',
                 '{{table}}.is_in_stock=0',
                 'left')
     ->addAttributeToFilter('qty', array("eq" => 0));


$csv = '';
$_columns = array(
     "Sku",
     "Qty",
     "Price"
);
$data = array();
// prepare CSV header...
foreach ($_columns as $column) {
       $data[] = '"'.$column.'"';
}
$csv .= implode(',', $data)."\n";

echo "<h2>Simple Products with 0 quantity and outof Stock</h2>";
foreach($productCollection as $product) { //print_r($product->getData());exit;
    
     $data = array();
                $data[] = $product->getSku();
                $data[] = 0;
                $data[] = $product->getPrice();
    
    if($product->getTypeId() == 'simple')
        echo $product->getName() . " | " . $product->getSku() . "<br>";
}
echo 'Done';
?>