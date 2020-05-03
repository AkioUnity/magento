<?php
//https://pricebusters.furniture/coster-cost-sync.php
ini_set('max_execution_time', 0);
require_once('app/Mage.php');
umask(0);
Mage::app();
$importdate = date("d-m-Y H:i:s", strtotime("now"));
$log = "started at: " . $importdate . "--------------";
Mage::log($log, null, 'coster-cost-sync.log', true);

$allProducts = Mage::getModel('catalog/product')->getCollection()
    ->addAttributeToFilter(
        array(
            array('attribute' => 'is_coaster', 'eq' => '1')
        )
    )
    ->addAttributeToFilter(
        array(
            array('attribute' => 'status', 'eq' => '1'),
        )
    );
$ids = $allProducts->getAllIds();
foreach ($allProducts as $zProduct) {
    $_product = Mage::getModel('catalog/product')->load($zProduct->getId());
    $prods[] = $zProduct->getSku();
}
$chunk = 50;
//$totalProduct = count($prods);
$productChunks = array_chunk($prods, $chunk);
foreach ($productChunks as $productChunk) {
    try {
        $filtercode = _sendRequest('getFilter?ProductNumber=' . implode(',', $productChunk));
        $productPriceList = _sendRequest('GetPriceList?filtercode=' . $filtercode . '&customernumber=16998');
        //print_r($productPriceList);
        foreach ($productPriceList[0]->PriceList as $ppl) {
            $productSku = $ppl->ProductNumber;
            $productPrice = $ppl->Price * 1.92;
            //print_r($productPrice = $ppl->Price);
            if ($productPrice != '') {
                $updateProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $productSku);
                $curPrice = $updateProduct->getPrice();
                if ($curPrice != $productPrice) {
                    $updateProduct->setPrice($productPrice);
                    $updateProduct->save();
                    $log = $productSku . " update price: " . $productPrice;
                    Mage::log($log, null, 'coster-cost-sync.log', true);
                }
            }
        }
        sleep(1);
    } catch (Exception $e) {
        Mage::logException($e);
    }
}

$importdate = date("d-m-Y H:i:s", strtotime("now"));
$log = "finished at: " . $importdate;
Mage::log($log, null, 'coster-cost-sync.log', true);

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
