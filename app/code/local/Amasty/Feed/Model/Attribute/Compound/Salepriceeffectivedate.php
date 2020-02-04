<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

    class Amasty_Feed_Model_Attribute_Compound_Salepriceeffectivedate extends Amasty_Feed_Model_Attribute_Compound_Abstract
    {
        function prepareCollection($collection){
            $collection->addAttribute('special_to_date', $this->_feed->getStoreId());
            $collection->addAttribute('special_from_date', $this->_feed->getStoreId());
        }
                        
        function getCompoundData($productData){
            
            $salePriceFffectiveDate = '';
            $specialFromDate = $productData['special_from_date'];
            $specialToDate = $productData['special_to_date'];
            
            $frmDate = "Y-m-d";
            if ($frmDate && !empty($specialFromDate) && !empty($specialToDate)) {
                $specialFromDate = date($frmDate, strtotime($specialFromDate)) . "T00:00-0800"; 
                $specialToDate = date($frmDate, strtotime($specialToDate)) . "T00:00-0800"; 
            }
                
            if (!empty($specialFromDate) && !empty($specialToDate)){
                $salePriceFffectiveDate = $specialFromDate.'/'.$specialToDate;
            }

            return $salePriceFffectiveDate;
        }
    }