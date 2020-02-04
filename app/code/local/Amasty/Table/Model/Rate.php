<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Table
 */
class Amasty_Table_Model_Rate extends Mage_Core_Model_Abstract
{
    const MAX_LINE_LENGTH = 50000;
    const COL_NUMS = 19;
    const HIDDEN_COLUMNS = 2;
    const BATCH_SIZE = 50000;
    const COUNTRY = 0;
    const STATE = 1;
    const ZIP_FROM = 3;
    const NUM_ZIP_FROM = 18;
    const ZIP_TO = 4;
    const NUM_ZIP_TO = 19;
    const PRICE_TO = 6;
    const WEIGHT_TO = 8;
    const QTY_TO = 10;
    const SHIPPING_TYPE = 11;
    const ALGORITHM_SUM = 0;
    const ALGORITHM_MAX = 1;
    const ALGORITHM_MIN = 2;
    const MAX_VALUE = 99999999;

    protected $_shippingTypes = array();
    protected $_existingShippingTypes = array();

    public function _construct()
    {
        parent::_construct();
        $this->_init('amtable/rate');
    }

    public function findBy($request, $collection)
    {
        if (!$request->getAllItems()) {
            return array();
        }

        if($collection->getSize() == 0)
        {
            return array();
        }

        $methodIds = array();
        foreach ($collection as $method)
        {
            $methodIds[] = $method -> getMethodId();

        }

        // calculate price and weight
        $allowFreePromo = Mage::getStoreConfig('carriers/amtable/allow_promo');
        $ignoreVirtual   = Mage::getStoreConfig('carriers/amtable/ignore_virtual');

        $configurableSetting = Mage::getStoreConfig('carriers/amtable/configurable_child');
        $bundleSetting = Mage::getStoreConfig('carriers/amtable/bundle_child');

        $items = $request->getAllItems();

        $collectedTypes = array();

        foreach($items as $item)
        {

            $typeId = $item->getProduct()->getTypeId();
            $shipmentType = $item->getProduct()->getShipmentType();

            if ($item->getParentItemId())
                continue;

            if (($item->getHasChildren() && $typeId == 'configurable' && $configurableSetting == '0') ||
                ($item->getHasChildren() && $typeId == 'bundle' && $bundleSetting == '2') ||
                ($item->getHasChildren() && $typeId == 'bundle' && $bundleSetting == '0' && $shipmentType == '1'))
            {
                foreach ($item->getChildren() as $child) {
                    $this->getShippingTypes($child);
                }

            } else {
                $this->getShippingTypes($item);
            }
        }

        $this->_shippingTypes = $this->_existingShippingTypes;
        $this->_shippingTypes[] = 0;

        $this->_shippingTypes = array_unique($this->_shippingTypes);
        $this->_existingShippingTypes = array_unique($this->_existingShippingTypes);

        $allCosts = array();

        $allRates = $this->getResourceCollection();
        $allRates->addMethodFilters($methodIds);
        $ratesTypes = array();

        foreach ($allRates as $singleRate){
            $ratesTypes[$singleRate->getMethodId()][]= $singleRate->getShippingType();
        }

        $intersectTypes = array();

        foreach ($ratesTypes as $key => $value){

            $intersectTypes[$key] = array_intersect($this->_shippingTypes,$value);
            arsort($intersectTypes[$key]);
            $methodIds = array($key);
            $allTotals =  $this->calculateTotals($request, $ignoreVirtual, $allowFreePromo,'0');

            foreach ($intersectTypes[$key] as $shippingType){

                $totals = $this->calculateTotals($request, $ignoreVirtual, $allowFreePromo,$shippingType);

                if ($allTotals['qty'] > 0 && (!Mage::getStoreConfig('carriers/amtable/dont_split') || $allTotals['qty'] == $totals['qty'])) {

                    if ($shippingType == 0)
                        $totals = $allTotals;

                    $allTotals['not_free_price'] -= $totals['not_free_price'];
                    $allTotals['not_free_weight'] -= $totals['not_free_weight'];
                    $allTotals['not_free_qty'] -= $totals['not_free_qty'];
                    $allTotals['qty'] -= $totals['qty'];

                    $allRates = $this->getResourceCollection();
                    $allRates->addAddressFilters($request);
                    $allRates->addTotalsFilters($totals,$shippingType);
                    $allRates->addMethodFilters($methodIds);
                    foreach($this->calculateCosts($allRates, $totals, $request,$shippingType) as $key => $cost){

                        if (($totals['not_free_qty'] < 1) && ($totals['qty'] < 1))
                            continue;

                        if ($totals['not_free_qty'] < 1)
                            $cost['cost'] = 0;

                        $method = Mage::getModel('amtable/method')->load($key);
                        if (empty($allCosts[$key])){
                            $allCosts[$key]['cost'] = $cost['cost'];
                            $allCosts[$key]['time'] = $cost['time'];
                        }
                        else {
                            switch ($method->getSelectRate()) {
                                case self::ALGORITHM_MAX:
                                    if ($allCosts[$key]['cost'] < $cost['cost']) {
                                        $allCosts[$key]['cost'] = $cost['cost'];
                                        $allCosts[$key]['time'] = $cost['time'];
                                    }
                                    break;
                                case self::ALGORITHM_MIN:
                                    if ($allCosts[$key]['cost'] > $cost['cost']) {
                                        $allCosts[$key]['cost'] = $cost['cost'];
                                        $allCosts[$key]['time'] = $cost['time'];
                                    }
                                    break;
                                default:
                                    $allCosts[$key]['cost'] += $cost['cost'];
                                    $allCosts[$key]['time'] = $cost['time'];
                            }
                        }

                        $collectedTypes[$key][] = $shippingType;
                        $freeTypes[$key]= $method->getFreeTypes();
                    }
                }
            }
        }

        //do not show method if quote has "unsuitable" items
        foreach ($allCosts as $key => $cost){
            //1.if the method contains rate with type == All
            if (in_array('0',$collectedTypes[$key]))
                continue;
            //2.if every item in quote has applicable rates in the method
            $extraTypes = array_diff($this->_existingShippingTypes,$collectedTypes[$key]);
            if (!$extraTypes)
                continue;
            //3.if every item has (2) OR is in method's free_types
            if (!array_diff($extraTypes,$freeTypes[$key]))
                continue;

            //else — do not show the method;
            unset($allCosts[$key]);
        }

        $minRates = Mage::getModel('amtable/method')->getCollection()->hashMinRate();
        $maxRates = Mage::getModel('amtable/method')->getCollection()->hashMaxRate();

        foreach ($allCosts as $key  => $rate){
            if ($maxRates[$key] != '0.00' && $maxRates[$key] < $rate['cost']){
                $allCosts[$key]['cost'] = $maxRates[$key];
            }

            if ($minRates[$key] != '0.00' && $minRates[$key] > $rate['cost']){
                $allCosts[$key]['cost'] = $minRates[$key];
            }
        }



        return $allCosts;
    }

    protected function getShippingTypes($item)
    {
        $product = Mage::getModel('catalog/product')->load($item->getProduct()->getEntityId());
        if ($product->getAmShippingType()){
            $this->_existingShippingTypes[] = $product->getAmShippingType();
        } else {
            $this->_existingShippingTypes[] = 0;
        }
    }

    protected function calculateCosts($allRates, $totals, $request,$shippingType)
    {
        $shippingFlatParams  =  array('country', 'state', 'city');
        $shippingRangeParams =  array('price', 'qty', 'weight');

        $minCounts = array();   // min empty values counts per method
        $results   = array();
        foreach ($allRates as $rate){

            $rate = $rate->getData();

            $emptyValuesCount = 0;

            if(empty($rate['shipping_type'])){
                $emptyValuesCount++;
            }

            foreach ($shippingFlatParams as $param){
                if (empty($rate[$param])){
                    $emptyValuesCount++;
                }
            }

            foreach ($shippingRangeParams as $param) {
                if ((ceil($rate[$param . '_from']) == 0) && (ceil($rate[$param . '_to']) == self::MAX_VALUE)) {
                    $emptyValuesCount++;
                }
            }

            if (empty($rate['zip_from']) && empty($rate['zip_to']) ){
                $emptyValuesCount++;
            }

            if (!$totals['not_free_price'] && !$totals['not_free_qty'] && !$totals['not_free_weight']){
                $cost = 0;
            }
            else {
                $cost =  $rate['cost_base'] +  $totals['not_free_price'] * $rate['cost_percent'] / 100 + $totals['not_free_qty'] * $rate['cost_product'] + $totals['not_free_weight'] * $rate['cost_weight'];
            }

            $id   = $rate['method_id'];
            if ((empty($minCounts[$id]) && empty($results[$id])) || ($minCounts[$id] > $emptyValuesCount) || (($minCounts[$id] == $emptyValuesCount) && ($cost > $results[$id]))){
                $minCounts[$id] = $emptyValuesCount;
                $results[$id]['cost']   =  $cost;
                $results[$id]['time']   =  $rate['time_delivery'];
            }

        }

        return $results;
    }

    protected function calculateTotals($request, $ignoreVirtual, $allowFreePromo,$shippingType)
    {
        $totals = $this->initTotals();
        $weightModel = Mage::getModel('amtable/weight');
        $newItems = array();

        //reload child items

        $isCalculateLater = array();
        $configurableSetting = Mage::getStoreConfig('carriers/amtable/configurable_child');
        $bundleSetting = Mage::getStoreConfig('carriers/amtable/bundle_child');

        foreach ($request->getAllItems() as $item) {


            if ($item->getParentItemId())
               continue;

            if ($ignoreVirtual && $item->getProduct()->isVirtual())
                continue;

            $typeId = $item->getProduct()->getTypeId();
            $shipmentType = $item->getProduct()->getShipmentType();

            if (($item->getHasChildren() && $typeId == 'configurable' && $configurableSetting == '0') ||
                ($item->getHasChildren() && $typeId == 'bundle' && $bundleSetting == '2') ||
                ($item->getHasChildren() && $typeId == 'bundle' && $bundleSetting == '0' && $shipmentType == '1')){


                 $qty = 0;
                 $notFreeQty =0;
                 $price = 0;
                 $weight = 0;
                 $itemQty = 0;

                $flagOfPersist = false;
                foreach ($item->getChildren() as $child) {
                    $flagOfPersist = false;
                    $product = Mage::getModel('catalog/product')->load($child->getProduct()->getEntityId());

                    if (($product->getAmShippingType() != $shippingType) && ($shippingType != 0))
                        continue;


                    $flagOfPersist = true;
                    $itemQty = $child->getQty() * $item->getQty();
                    $qty        +=  $itemQty ;
                    $notFreeQty += ($itemQty - $this->getFreeQty($child, $allowFreePromo));
                    $price  += $child->getPrice() * $itemQty;
                    $weight += $weightModel->getItemWeight($child) * $itemQty;
                    $totals['tax_amount']       += $child->getBaseTaxAmount() + $child->getBaseHiddenTaxAmount();
                    $totals['discount_amount']  += $child->getBaseDiscountAmount();
                }

                if ($typeId == 'bundle'){
                  //  if ($flagOfPersist == false)
                   //     continue;
                  //  $qty        = $item->getQty();

                    if ($item->getProduct()->getWeightType() == 1){
                        $weight = $weightModel->getItemWeight($item);
                    }

                    if ($item->getProduct()->getPriceType() == 1){
                        $price   = $item->getPrice();
                    }

                    if ($item->getProduct()->getSkuType() == 1){
                        $totals['tax_amount']       += $item->getBaseTaxAmount() + $item->getBaseHiddenTaxAmount();
                        $totals['discount_amount']  += $item->getBaseDiscountAmount();
                    }

                    $notFreeQty = ($qty - $this->getFreeQty($item, $allowFreePromo));
                    $totals['qty']              += $qty;
                    $totals['not_free_qty']     += $notFreeQty;
                    $totals['not_free_price'] += $price;
                    $totals['not_free_weight'] += $weight;

                }elseif ($typeId == 'configurable'){

                    if ($flagOfPersist == false)
                        continue;


                    $qty     = $item->getQty();
                    $price   = $item->getPrice();
                    $weight  = $weightModel->getItemWeight($item);
                    $notFreeQty = ($qty - $this->getFreeQty($item, $allowFreePromo));
                    $totals['qty']              += $qty;
                    $totals['not_free_qty']     += $notFreeQty;
                    $totals['not_free_price'] += $price * $notFreeQty;
                    $totals['not_free_weight'] += $weight * $notFreeQty;
                    $totals['tax_amount']       += $item->getBaseTaxAmount() + $item->getBaseHiddenTaxAmount();
                    $totals['discount_amount']  += $item->getBaseDiscountAmount();

                } else { // for grouped and custom not simple products
                    $qty     = $item->getQty();
                    $price   = $item->getPrice();
                    $weight  =  $weightModel->getItemWeight($item);
                    $notFreeQty = ($qty - $this->getFreeQty($item, $allowFreePromo));
                    $totals['qty']              += $qty;
                    $totals['not_free_qty']     += $notFreeQty;
                    $totals['not_free_price'] += $price * $notFreeQty;
                    $totals['not_free_weight'] += $weight * $notFreeQty;
                }

            } else {
                $product = Mage::getModel('catalog/product')->load($item->getProduct()->getEntityId());
                if (($product->getAmShippingType() != $shippingType) && ($shippingType != 0))
                    continue;

                if ($item->getParentItemId())
                    continue;


                $qty        = $item->getQty();
                $notFreeQty = ($qty - $this->getFreeQty($item, $allowFreePromo));
                $totals['not_free_price'] += $item->getBasePrice() * $notFreeQty;
                $totals['not_free_weight'] += $weightModel->getItemWeight($item) * $notFreeQty;
                $totals['qty']              += $qty;
                $totals['not_free_qty']     += $notFreeQty;
                $totals['tax_amount']       += $item->getBaseTaxAmount() + $item->getBaseHiddenTaxAmount();
                $totals['discount_amount']  += $item->getBaseDiscountAmount();
            }


        }// foreach

        // fix magento bug
        if ($totals['qty'] != $totals['not_free_qty'])
            $request->setFreeShipping(false);

        $afterDiscount = Mage::getStoreConfig('carriers/amtable/after_discount');
        $includingTax =  Mage::getStoreConfig('carriers/amtable/including_tax');

        if ($afterDiscount)
            $totals['not_free_price'] -= $totals['discount_amount'];

        if($includingTax)
            $totals['not_free_price'] += $totals['tax_amount'];

        if ($totals['not_free_price'] < 0)
            $totals['not_free_price'] = 0;

        if ($request->getFreeShipping() && $allowFreePromo)
            $totals['not_free_price'] = $totals['not_free_weight'] = $totals['not_free_qty'] = 0;

        foreach ($totals as $key => $value){
            $totals[$key] = round($value,2);
        }


        return $totals;
    }

    public function getFreeQty($item, $allowFreePromo)
    {
        $freeQty = 0;

        if ($item->getFreeShipping() && $allowFreePromo)
            $freeQty = ((is_numeric($item->getFreeShipping())) && ($item->getFreeShipping() <= $item->getQty())) ? $item->getFreeShipping() : $item->getQty();

        return $freeQty;
    }

    public function import($methodId, $fileName)
    {
        $err = array();

        $fp = fopen($fileName, 'r');
        if (!$fp){
            $err[] = Mage::helper('amtable')->__('Can not open file %s .', $fileName);
            return $err;
        }
        $methodId = intval($methodId);
        if (!$methodId){
            $err[] = Mage::helper('amtable')->__('Specify a valid method ID.');
            return $err;
        }

        $countryCodes = $this->getCountries();
        $stateCodes   = $this->getStates();
        $countryNames = $this->getCountriesName();
        $stateNames   = $this->getStatesName();
        $typeLabels   = Mage::helper('amtable')->getTypes();

        $data = array();
        $dataIndex = 0;

        $currLineNum  = 0;
        while (($line = fgetcsv($fp, self::MAX_LINE_LENGTH, ',', '"')) !== false) {

            $currLineNum++;

            if ($currLineNum == 1 && $line[0] == 'Country')
            {
                continue;
            }

            if ((count($line) + self::HIDDEN_COLUMNS) != self::COL_NUMS){
               $err[] = 'Line #' . $currLineNum . ': warning, expected number of columns is ' . self::COL_NUMS;
               if (count($line) > self::COL_NUMS)
               {
                   for ($i = 0; $i < count($line) - self::COL_NUMS; $i++){
                        unset($line[self::COL_NUMS + $i]);
                   }
               }

                if (count($line) < self::COL_NUMS)
                {
                    for ($i = 0; $i <  self::COL_NUMS - count($line); $i++){
                        $line[count($line) + $i] = 0;
                    }
                }
            }

            $dataZipFrom = Mage::helper('amtable')->getDataFromZip($line[self::ZIP_FROM]);
            $dataZipTo = Mage::helper('amtable')->getDataFromZip($line[self::ZIP_TO]);
            $line[self::NUM_ZIP_FROM] = $dataZipFrom['district'];
            $line[self::NUM_ZIP_TO] = $dataZipTo['district'];

            for ($i = 0; $i < self::COL_NUMS - self::HIDDEN_COLUMNS; $i++) {
               $line[$i] = str_replace(array("\r", "\n", "\t", "\\" ,'"', "'", "*"), '', $line[$i]);
                if ($i != self::COL_NUMS - self::HIDDEN_COLUMNS - 1 && preg_match('/^([0-9]+?\,)+?[0-9]+?\.[0-9]+$/', $line[$i])) {
                    $line[$i] = str_replace(",", '', $line[$i]);
                }
            }

            $countries = array('');
            if ($line[self::COUNTRY]){
                $countries = explode(',', $line[self::COUNTRY]);
            } else {
                $line[self::COUNTRY] = '0';
            }
            $states = array('');
            if ($line[self::STATE]){
                $states = explode(',', $line[self::STATE]);
            }

            $types = array('');
            if ($line[self::SHIPPING_TYPE]){
                $types = explode(',', $line[self::SHIPPING_TYPE]);
            }

            $zips = array('');
            if ($line[self::ZIP_FROM]){
                $zips = explode(',', $line[self::ZIP_FROM]);
            }

            if(!$line[self::PRICE_TO]) $line[self::PRICE_TO] =  self::MAX_VALUE;
            if(!$line[self::WEIGHT_TO]) $line[self::WEIGHT_TO] =  self::MAX_VALUE;
            if(!$line[self::QTY_TO]) $line[self::QTY_TO] =  self::MAX_VALUE;

            foreach ($types as $type){
               if ($type == 'All'){
                    $type = 0;
                }
                if ($type && empty($typeLabels[$type])) {
                    if (in_array($type, $typeLabels)){
                        $typeLabels[$type] = array_search($type, $typeLabels);
                    }  else {
                        $err[] = 'Line #' . $currLineNum . ': invalid type code ' . $type;
                        continue;
                    }

                }
                $line[self::SHIPPING_TYPE] = $type ? $typeLabels[$type] : '';
            }

            foreach ($countries as $country){
                $country = html_entity_decode($country);
                if ($country == 'All'){
                    $country = 0;
                }

                if ($country && empty($countryCodes[$country])) {
                    if (in_array($country, $countryNames)){
                        $countryCodes[$country] = array_search($country, $countryNames);
                    }  else {
                        $err[] = 'Line #' . $currLineNum . ': invalid country code ' . $country;
                        continue;
                    }

                }
                $line[self::COUNTRY] = $country ? $countryCodes[$country] : '0';

                foreach ($states as $state){

                    if ($state == 'All'){
                        $state = '';
                    }

                    if ($state && empty($stateCodes[$state][$country])) {
                        if (in_array($state, $stateNames)){
                            $stateCodes[$state][$country] = array_search($state, $stateNames);
                        } else {
                            $err[] = 'Line #' . $currLineNum . ': invalid state code ' . $state;
                            continue;
                        }

                    }
                    $line[self::STATE] = $state ? $stateCodes[$state][$country] : '';

                    foreach ($zips as $zip){
                        $line[self::ZIP_FROM] = $zip;


                        $data[$dataIndex] = $line;
                        $dataIndex++;

                        if ($dataIndex > self::BATCH_SIZE){
                            $err = $this->returnErrors($data, $methodId,$currLineNum, $err);
                            $data = array();
                            $dataIndex = 0;
                        }
                    }
                }// states  
            }// countries 
        } // end while read  
        fclose($fp);

        if ($dataIndex){
            $err = $this->returnErrors($data, $methodId,$currLineNum, $err);
        }

        return $err;
    }


    public function returnErrors($data, $methodId,$currLineNum, $err)
    {
        $errText = $this->getResource()->batchInsert($methodId, $data);

        if ($errText){
            foreach ($data as $key => $value){
                $newData[$key] = array_slice($value, 0, 12);
                $oldData[$key] = array_slice($value,12);
            }

            foreach ($newData AS $key => $arrNewData) {
                $newData[$key] = serialize($arrNewData);
            }
            $newData = array_unique($newData);
            foreach ($newData AS $key => $strNewData) {
                $newData[$key] = unserialize($strNewData);
            }

            $checkedData = array();
            foreach ($newData as $key => $value){
                $checkedData[] = array_merge($value,$oldData[$key]);
            }

            $errText = $this->getResource()->batchInsert($methodId, $checkedData);
            if ($errText){
                $err[] = 'Line #' . $currLineNum . ': duplicated conditions before this line have been skipped';
            } else {
                $err[] = 'Your csv file has been automatically cleared of duplicates and successfully uploaded';
            }
        }

        return $err;
    }

    public function getCountries()
    {
        $hash = array();

        $collection = Mage::getResourceModel('directory/country_collection');
        foreach ($collection as $item){
            $hash[$item->getIso3Code()] = $item->getCountryId();
            $hash[$item->getIso2Code()] = $item->getCountryId();
        }

        return $hash;
    }

    public function getStates()
    {
        $hash = array();

        $collection = Mage::getResourceModel('directory/region_collection');
        foreach ($collection as $state){
            $hash[$state->getCode()][$state->getCountryId()] = $state->getRegionId();
        }

        return $hash;
    }
    public function getCountriesName()
    {
        $hash = array();
        $collection = Mage::getResourceModel('directory/country_collection');
        foreach ($collection as $item){
            $country_name=Mage::app()->getLocale()->getCountryTranslation($item->getIso2Code());
            $hash[$item->getCountryId()] = str_replace("'",'',$country_name);

        }
        return $hash;
    }


    public function getStatesName()
    {
        $hash = array();

        $collection = Mage::getResourceModel('directory/region_collection');
        $countryHash = $this->getCountriesName();
        foreach ($collection as $state){
            $string = $countryHash[$state->getCountryId()].'/'.$state->getDefaultName();
            $hash[$state->getRegionId()] =  str_replace("'",'',$string);
        }
        return $hash;
    }

    public function initTotals()
    {
        $totals = array(
            'not_free_price'     => 0,
            'not_free_weight'    => 0,
            'qty'                => 0,
            'not_free_qty'       => 0,
            'tax_amount'         => 0,
            'discount_amount'    => 0,
        );
        return $totals;
    }

    public function deleteBy($methodId)
    {
        return $this->getResource()->deleteBy($methodId);
    }
}
