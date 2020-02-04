<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

    class Amasty_Feed_Model_Attribute_Compound_Stockavailabilitybasedqty extends Amasty_Feed_Model_Attribute_Compound_Abstract
    {
        function prepareCollection($collection){
            $collection->joinIsInStock();
            $collection->joinQty();
        }

        function getCompoundData($productData){
            $hlr = Mage::helper("amfeed");
            
            $childs = $this->getFeed()->getProductChildData($productData['entity_id']);
            
            $inStock = FALSE;
            
            if (is_array($childs)) {
                foreach($childs as $child){
                    if ($child['qty'] > 0) {
                        $inStock = TRUE;
                        break;
                    }
                }
            } else {
                $inStock = $productData['is_in_stock'] == 1;
            }
            
            return $inStock ?
                "Yes" : "No";
        }
        
        function hasFilterCondition(){
            return true;
        }
        
        function validateFilterCondition($productData, $operator, $valueCode){
            return Amasty_Feed_Model_Field_Condition::compare($operator, $this->getCompoundData($productData), $valueCode);
        }
  
//        function hasFilterCondition(){
//            return true;
//        }
        
    }