<?php
/**   per a day
 * File: Update Product Price as per Coster (and update the price by *1.92)
 * Coaster catalogs
 * @SGDEV
 * v:1.1
 */
//https://pricebusters.furniture/coster-cost-sync.php
ini_set('max_execution_time', 0);
//set_time_limit(0);

require_once('app/Mage.php');
umask(0);
Mage::app();

print_r (date('d-m-Y h:i:s a') . ' >> Started updating cost....<br>');

$allProducts = Mage::getModel('catalog/product')->getCollection()
    ->addAttributeToFilter(
        array(
            array('attribute'=>'status', 'eq'=>'1'),
        )
    );
$ids = $allProducts->getAllIds();
foreach ($allProducts as $zProduct) {
    $_product = Mage::getModel('catalog/product')->load($zProduct->getId());
    if ($_product->getIsCoaster() == '1') {
        $prods[] = $zProduct->getSku();
    }
}
$chunk = 50;
//$totalProduct = count($prods);
$productChunks = array_chunk($prods, $chunk);
foreach ($productChunks as $productChunk) {
    $filtercode = _sendRequest('getFilter?ProductNumber=' . implode(',', $productChunk));
    $productPriceList = _sendRequest('GetPriceList?filtercode=' . $filtercode . '&customernumber=16998');
    //print_r($productPriceList);
    foreach ($productPriceList[0]->PriceList as $ppl) {
        $productSku = $ppl->ProductNumber;
        $productPrice = $ppl->Price;
        //print_r($productPrice = $ppl->Price);
        if ($productPrice != '') {
            $updateProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $productSku);
            $curPrice = $updateProduct->getPrice();
            if ($curPrice == 0)
                echo $productSku . '- $' . $productPrice . "\n";
            $updateProduct->setPrice($productPrice * 1.92);
//                    $updateProduct->setStatus(1);
            $updateProduct->save();
        }
    }
    sleep(1);
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