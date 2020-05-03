<?php
echo "<pre>";
// per a day
//https://pricebusters.furniture/coster-isdisabled-sync_xai.php
//4/6- total 3848  disable-1, enable- 2747  (before remove)
//4/7- total 3293  disable-1  enable- 2747  before manual delete
//4/7- total 2748  disable-1  enable- 2747  coaster-2645 manual delete  disabled item sku-5900 Silverton Platinum  id=13258
//4/12- total 2748 enable-2057 disable-1
//4/15 - total 3210  enable-2370  disable-1
ini_set('max_execution_time', 0);
require_once('app/Mage.php');
umask(0);
Mage::app();
$chunk = 50;

$importdate = date("d-m-Y H:i:s", strtotime("now"));
$log = "started at: " . $importdate . "--------------";
Mage::log($log, null, 'coster-isdisabled-sync_xai.log', true);
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
//echo ('count:'.count($prods).'<br>');

$productChunks = array_chunk($prods, 50);
Mage::register('isSecureArea', true);
foreach ($productChunks as $productChunk) {
//    print_r($productChunk);
    try {
        $filtercode = _sendRequest('getFilter?ProductNumber=' . implode(',', $productChunk));
        $productList = _sendRequest('GetProductList?filtercode=' . $filtercode . '&customernumber=16998');
        foreach ($productList as $product) {
            $sku = $product->ProductNumber;
            $updateProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
            if ($updateProduct) {
                if ($product->IsDiscontinued) {
                    try {
                        $updateProduct->delete();
                        $log = "Deleted: " . $sku;
                        Mage::log($log, null, 'coster-isdisabled-sync_xai.log', true);
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                } else {  //not Discontinued
//                    if ($updateProduct->getStatus()==0) {  //status0=0
//                        $updateProduct->setStatus(1); //enable
//                        $updateProduct->save();
//                        $log = $sku . ' enable';
//                        Mage::log($log, null, 'coster-isdisabled-sync_xai.log', true);
//                    }
                }
            } else {
                $log = "No Product: " . $sku;
                Mage::log($log, null, 'coster-isdisabled-sync_xai.log', true);
            }
//        sleep(rand(1,2));
        }
        sleep(rand(1, 2));
    } catch (Exception $e) {
        Mage::logException($e);
    }
}
Mage::unregister('isSecureArea');

$importdate = date("d-m-Y H:i:s", strtotime("now"));
$log = "finished at: " . $importdate."\n";
Mage::log($log, null, 'coster-isdisabled-sync_xai.log', true);

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

