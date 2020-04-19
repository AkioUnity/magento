<?php
/**
 * File: Update The Product Price
 * Coaster catalogs 
 * @SGDEV
 * v:1.0
 */
ini_set('max_execution_time', 0);
//set_time_limit(0);
require_once('app/Mage.php'); 
umask(0);
Mage::app();
$chunk = 50;
echo date('d-m-Y h:i:s a') . ' >> Started updating....<br>';
$connection = Mage::getModel('core/resource')->getConnection('core_read');

$zeroPriceProducts = Mage::getModel('catalog/product')->getCollection()
    ->addAttributeToFilter(
      array(
         array('attribute'=>'price', 'eq'=>'0'),
         array('attribute'=>'price', 'isnull'=>true),
    )
);
foreach( $zeroPriceProducts as $zProduct) {
    $_product = Mage::getModel('catalog/product')->load($zProduct->getId());
    if( $_product->getImageUrl() != '') {
        $prods[] = $zProduct->getSku();
    }
}
echo "<pre>";
$totalProduct = count($prods);
$productChunks = array_chunk($prods , 50);
foreach($productChunks as $productChunk) {
    $filtercode = _sendRequest('getFilter?ProductNumber='.implode(',', $productChunk));
    $productPriceList =  _sendRequest('GetPriceList?filtercode='.$filtercode.'&customernumber=16998');
    print_r($productPriceList);
    foreach($productChunk as $productSku) {
        
        foreach( $productPriceList[0]->PriceList as $ppl) {
            if($ppl->ProductNumber == $productSku) {
                $productPrice = $ppl->Price;
                print_r($productPrice = $ppl->Price);
                if($productPrice != '') {
                    $updateProduct = Mage::getModel('catalog/product')->loadByAttribute('sku',$productSku);
                    $updateProduct->setPrice($productPrice * 1.92);
                    $updateProduct->setStatus(1);
                    $updateProduct->save();
                }
            }
        }
    }
    sleep(rand(3,5));
}


function _sendRequest($endpoint) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => "http://api.coasteramer.com/api/product/" . $endpoint,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 240,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array("keycode: E122443B8549416BAA0629ED0C"),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
      return false;
    } else {
      return json_decode($response);
    }
}

die();


/* ====== Cost Update Area ====== */
$chunk = 50;

for($i=0; $i <= $chunkcount; $i++) {
    $offest = $i * $chunk;
    $query = "SELECT * FROM `xcentia_coster_product` WHERE `inventory_status` = '1' LIMIT " . $offest . ", " . $chunk;
    $rows  = $connection->fetchAll($query);
    
    if(count($rows) > 0) {

        foreach($rows as $values) {
            print_r($values);
            die();
            $prods[] = $values['sku'];
        }
        print_r(implode(',', $prods));
        $filtercode = _sendRequest('getFilter?ProductNumber='.implode(',', $prods));

        print_r($filtercode);
        die;


        foreach ($rows as $values) {

            
            // $products = _sendRequest('GetProductList?filterCode='.$filtercode);
            $products = _sendRequest('GetPriceList?filterCode='.$filtercode.'&customerNumber=16998');
            print_r($products);
            // GetPriceList?filterCode={filterCode}&customerNumber={customerNumber}

            
            $productId = Mage::getModel('catalog/product')->getIdBySku($values['sku']);
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);

            if ($stockItem->getId() > 0 and $stockItem->getManageStock()) {
                
                /*if($values['qty'] > 0) {
                    $stockItem->setQty((int)$values['qty']);
                    $stockItem->setIsInStock(1);
                } else {
                    $stockItem->setQty(0);
                    $stockItem->setIsInStock(0); 
                }*/

                try {
                    if($values['qty'] > 0) {
                        /* $stockItem->setQty((int)$values['qty']);
                        $stockItem->setIsInStock(1);
                        $stockItem->save();
                        echo  'updating qty for SKU ['.$values['sku'].'] Qty: ['.$values['qty'] . "]\n"; */

                        // Update Price
                        // $stockItem->setPrice($products);

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
    sleep(rand(3,5));
}

/* ====== Cost Update Area Ends ====== */
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
    echo $chunkcount = $totalProducts / $chunk;
    for($i=0; $i <= $chunkcount; $i++) {
        $offest = $i * $chunk;
        $query = "SELECT * FROM `xcentia_coster_product` WHERE `inventory_status` = '1' LIMIT " . $offest . ", " . $chunk;
        $rows  = $connection->fetchAll($query);
        
        if(count($rows) > 0) {

            foreach($rows as $values) {
                print_r($values);
                die();
                $prods[] = $values['sku'];
            }
            print_r(implode(',', $prods));
            $filtercode = _sendRequest('getFilter?ProductNumber='.implode(',', $prods));

            print_r($filtercode);
            die;


            foreach ($rows as $values) {

                
                $products = _sendRequest('GetProductList?filterCode='.$filtercode);

                
                $productId = Mage::getModel('catalog/product')->getIdBySku($values['sku']);
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);

                if ($stockItem->getId() > 0 and $stockItem->getManageStock()) {
                    
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
                            echo  'updating qty for SKU ['.$values['sku'].'] Qty: ['.$values['qty'] . "]\n";
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
        sleep(rand(3,5));
    } 

} else {
    echo date('d-m-Y h:i:s a') . ' >> No product found. <br>';
}

echo date('d-m-Y h:i:s a') . ' >> Coster products are updated successfully. <br>';
