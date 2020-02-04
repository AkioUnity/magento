<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

    class Amasty_Feed_Model_Attribute_Compound_Mappedcategory extends Amasty_Feed_Model_Attribute_Compound_Abstract
    {
        function prepareCollection($collection){
            $collection->joinCategories();
        }
        
        function getCategoryValue($productData, $mapper){
            $categoryId = $this->_getCategoryId($productData);

            $value =  $this->_getCategoryName($categoryId);

            foreach($mapper as $category){
                if ($category->getCategoryId() == $categoryId){
                    $value = $category->getVariable();
                    break;
                }
            }

            return $value;
        }
    }