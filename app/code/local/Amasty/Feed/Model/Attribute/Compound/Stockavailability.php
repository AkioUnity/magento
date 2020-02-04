<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

    class Amasty_Feed_Model_Attribute_Compound_Stockavailability extends Amasty_Feed_Model_Attribute_Compound_Isinstock
    {
        protected $_values = array(
            0 => "No",
            1 => "Yes"
        );
        
        function prepareCollection($collection){
            $collection->joinIsInStock();
        }
        
        function getCompoundData($productData){
            $isInStock = $this->_getIsInStock($productData);
            return isset($this->_values[$isInStock]) ?
                $this->_values[$isInStock] :
                NULL;
                
        }
        
        function hasFilterCondition(){
            return true;
        }
        
        function validateFilterCondition($productData, $operator, $valueCode){
            return Amasty_Feed_Model_Field_Condition::compare($operator, $this->getCompoundData($productData), $valueCode);
        }
        
        function hasCondition(){
            return true;
        }
        
        function prepareCondition($collection, $operator, $condVal, &$attributesFields){
            $collection->joinIsInStock();

            $attributesFields[] = array(
                'attribute' => 'is_in_stock',
                $operator => isset($this->_values[$condVal]) ? 
                    $this->_values[$condVal] :
                    $condVal
            );
        }
    }