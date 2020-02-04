<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

    class Amasty_Feed_Model_Attribute_Compound_Categoryname extends Amasty_Feed_Model_Attribute_Compound_Abstract
    {
        function prepareCollection($collection){
            $collection->joinCategories();
        }
        
        function getCompoundData($productData){
            
            $ret = parent::getCompoundData($productData);
            
            $categoryId = $this->_getCategoryId($productData);

            $ret = $this->_getCategoryName($categoryId);
            
            return $ret;
            
        }
    }