<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

    class Amasty_Feed_Model_Attribute_Compound_Categories extends Amasty_Feed_Model_Attribute_Compound_Abstract
    {
        const CATEGORIES_SEPARATOR = ',';

        function prepareCollection($collection){
            $collection->joinCategories();
        }
        
        function getCompoundData($productData){

            $ret = parent::getCompoundData($productData);
           
            $categoryIds = $this->_getCategoryIds($productData);

            $arrNames = array();

            foreach($categoryIds as $categoryId){
                $arrNames[] = $this->_getCategoryName($categoryId);
            }

            return implode(self::CATEGORIES_SEPARATOR, $arrNames);
        }
    }