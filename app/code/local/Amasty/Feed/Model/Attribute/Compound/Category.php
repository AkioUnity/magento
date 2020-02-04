<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

    class Amasty_Feed_Model_Attribute_Compound_Category extends Amasty_Feed_Model_Attribute_Compound_Abstract
    {
        protected $_childCategories = array();
        
        function prepareCollection($collection){
            $collection->joinCategories();
        }
        
        function getCompoundData($productData){
            
            $ret = parent::getCompoundData($productData);
            $categoryId = $this->_getCategoryId($productData);
            $ret = $this->_getCategoryName($categoryId);

            return $ret;
            
        }
        
        function hasCondition(){
            return true;
        }
        
        function hasFilterCondition(){
            return true;
        }
        
        function validateFilterCondition($productData, $operator, $valueCode){
            $ret = false;
            $productValue = $this->_getCategoryId($productData);
            $valueCode = explode(",", $valueCode);

            $ret = $this->_validateFilterCondition($operator, $productValue, $valueCode);

            if (!$ret){
                $categoryIds = $this->_getCategoryIds($productData);
                foreach($categoryIds as $categoryId){
                    $ret = $this->_validateFilterCondition($operator, $categoryId, $valueCode);
                    if ($ret){
                        break;
                    }
                }
            }

            return $ret;
        }

        protected function _validateFilterCondition($operator, $productValue, $valueCode){
            $ret = false;

            switch ($operator) {
                case "eq":
                    $ret = in_array($productValue, $valueCode);
                    break;
                case "neq":
                    $ret = !in_array($productValue, $valueCode);
                    break;
            }

            return $ret;
        }
        
        function prepareCondition($collection, $operator, $condVal, &$attributesFields){
            $collection->joinCategories();
                
            $operator = $operator == 'eq' ? 'in' : 'nin';
                
            $attributesFields[] = array(
                'attribute' => 'category_id', 
                $operator => $condVal > 0 ? $this->_digCategories(array($condVal)) : null
            );
        }

        protected function _digCategories($ids)
        {

            $allIds = $ids;

            do {
                /** @var Mage_Catalog_Model_Resource_Category_Collection $categories */
                $categories = Mage::getModel('catalog/category')->getCollection();
                $categories->addAttributeToFilter('parent_id', array('in' => $ids));
                $ids = $categories->getAllIds();
                $allIds = array_merge($allIds, $ids);
            } while ($ids);

            return $allIds;
        }
    }