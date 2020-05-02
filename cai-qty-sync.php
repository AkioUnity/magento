<?php
echo "<pre>";
//https://pricebusters.furniture/cai-qty-sync.php
//not run anymore
ini_set('max_execution_time', 0);
require_once('app/Mage.php');
umask(0);
Mage::app();
$chunk = 50;

$importdate = date("d-m-Y H:i:s", strtotime("now"));
$log = "started at: ".$importdate;
Mage::log($log, null, 'cai-qty-sync.log', true);

//Status =>  1:Enabled, 2:Disabled
$allProducts = Mage::getModel('catalog/product')->getCollection()
    ->addAttributeToFilter(
        array(
            array('attribute' => 'is_coaster', 'eq' => '1')
        )
    )
    ->addAttributeToFilter(
        array(
            array('attribute' => 'status', 'neq' => '2'),
        )
    );
foreach ($allProducts as $zProduct) {
    $_product = Mage::getModel('catalog/product')->load($zProduct->getId());
    $prods[] = $zProduct->getSku();
}
echo('count:' . count($prods) . '<br>');
$productChunks = array_chunk($prods, 50);
echo('count:' . count($productChunks) . '<br>');
$cn = 0;
foreach ($productChunks as $productChunk) {
    $cn++;
    if ($cn < 9)
        continue;
    echo "CN:" . $cn . "\n";
//    print_r($productChunuttyk);
    try {
        $filtercode = _sendRequest('getFilter?ProductNumber=' . implode(',', $productChunk));
//        echo 'filter:'.$filtercode."\n";
        //At,TX,FL    0-TX, 1-AT
        $productList = _sendRequest('GetInventoryList?filtercode=' . $filtercode . '&customernumber=16998');
        $inventoryList = $productList[0]->InventoryList;
//        echo 'count:'.count($productList)."\n";
        for ($i = 0; $i < count($inventoryList); $i++) {
            $product = $inventoryList[$i];
            $sku = $product->ProductNumber;
            $updateProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($updateProduct->getId());
            if ($updateProduct && $stockItem->getId() > 0 && $stockItem->getManageStock()) {
                $qty = $product->QtyAvail ? $product->QtyAvail : $productList[1]->InventoryList[$i]->QtyAvail;
                if ($qty == 0) {  //status=1
                    $updateProduct->setStatus(0);
                    $updateProduct->save();
                    $stockItem->setQty(0);
                    $stockItem->setIsInStock(0);
                    $stockItem->save();
//                echo $sku . ' out of stock <br>';
                } else {
                    $updateProduct->setStatus(1);
                    $updateProduct->save();
                    $stockItem->setQty((int)($qty));
                    $stockItem->setIsInStock(1);
                    $stockItem->save();
//                echo $sku . ' qty:'.$qty.' <br>';
                }
            } else {
                $log = 'No Product: ' . $sku . ',' . $updateProduct . ',' . $stockItem->getId() . ',' . $stockItem->getManageStock();
                Mage::log($log, null, 'cai-qty-sync.log', true);
            }
//        sleep(rand(1,2));
        }
    } catch (Exception $e) {
        Mage::logException($e);
    }
    sleep(rand(1, 2));
//    return;
}
function _sendRequest($endpoint)
{
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

echo date('d-m-Y h:i:s a') . ' >> Coster products are updated successfully. <br>';
