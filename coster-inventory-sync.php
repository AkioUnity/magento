<?php
/**   per 5min
 * File: Update Product Stock Value
 * http://coasterconnect.coasteramer.com
 * Coaster catalogs 
 * @SGDEV
 * v:1.1
 */
ini_set('max_execution_time', 0);
//set_time_limit(0);
require_once('app/Mage.php'); 
umask(0);
Mage::app();

$connection = Mage::getModel('core/resource')->getConnection('core_read');
$chunk = 50;
echo "<pre>";
echo date('d-m-Y h:i:s a') . ' >> :::::: PRICEBUSTER PRODUCT UPDATER ::::::  <br>';
echo date('d-m-Y h:i:s a') . ' >> Initializing Catalog Updater Engine  <br>';
echo date('d-m-Y h:i:s a') . ' >> Checking products...  <br>';
$query1 = "SELECT count(*) as no_of_products FROM `xcentia_coster_product`";
$rows1  = $connection->fetchRow($query1);
$totalProducts = $rows1['no_of_products'];
if($totalProducts > 0) {
    echo date('d-m-Y h:i:s a') . ' >> Total ' . $totalProducts . ' products found. <br>';
    echo date('d-m-Y h:i:s a') . ' >> Preparing products for update...  <br>';
    $chunkcount = $totalProducts / $chunk;
    for($i=0; $i <= $chunkcount; $i++) {
        $offest = $i * $chunk;
        $query = "SELECT * FROM `xcentia_coster_product` WHERE `inventory_status` = '1' LIMIT " . $offest . ", " . $chunk;
        $rows  = $connection->fetchAll($query);
        
        if(count($rows) > 0) {
            foreach ($rows as $values) {
                $productId = Mage::getModel('catalog/product')->getIdBySku($values['sku']);
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);

                if ($stockItem->getId() > 0 && $stockItem->getManageStock()) {
                    
                    /*if($values['qty'] > 0) {
                        $stockItem->setQty((int)$values['qty']);
                        $stockItem->setIsInStock(1);
                    } else {
                        $stockItem->setQty(0);
                        $stockItem->setIsInStock(0); 
                    }*/

                    try {
                        if($values['qty'] > 0) {
                            $stockItem->setQty((int)$values['qty']);
                            $stockItem->setIsInStock(1);
                            $stockItem->save();
                            echo 'updating qty for SKU ['.$values['sku'].'] Qty: ['.$values['qty'] . "]\n";
                        } 
                        /*else {
                            $stockItem->setQty(0);
                            $stockItem->setIsInStock(0); 
                        }*/

                        /*$stockItem->setManageStock(1)
                        ->setUseConfigManageStock(1)
                        ->setQty(1)
                        ->setIsInStock(1)
                        ->save();*/
                        
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                }
            }
        }
        /*if($i == 2) {
            die;
        }*/
        sleep(rand(1,2));
    } 

} else {
    echo date('d-m-Y h:i:s a') . ' >> No product found. <br>';
}

echo date('d-m-Y h:i:s a') . ' >> Coster products are updated successfully. <br>';