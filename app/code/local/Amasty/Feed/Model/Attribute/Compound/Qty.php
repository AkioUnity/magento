<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

    class Amasty_Feed_Model_Attribute_Compound_Qty extends Amasty_Feed_Model_Attribute_Compound_Abstract
    {
        function prepareCollection($collection){
            $collection->joinQty();
        }
        
        function getCompoundData($productData){
            return $productData['qty'];
        }
        
        function hasCondition(){
            return true;
        }
        
        function prepareCondition($collection, $operator, $condVal, &$attributesFields){
            $collection->joinQty();
                
            $attributesFields[] = array(
                'attribute' => 'qty', 
                $operator => $condVal
            );
        }
        
        function hasFilterCondition(){
            return true;
        }
        
        function validateFilterCondition($productData, $operator, $valueCode){
            return Amasty_Feed_Model_Field_Condition::compare($operator, $productData['qty'], $valueCode);
        }
    }