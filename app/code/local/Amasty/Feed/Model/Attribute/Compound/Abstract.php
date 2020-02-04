<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

    class Amasty_Feed_Model_Attribute_Compound_Abstract extends Varien_Object
    {
        protected $_feed;
        protected $_attributesCodes = array();

        const CATEGORIES_SHORT_PATH = true;
        
        public function init($feed){
            $this->_feed = $feed;
            return $this;
        }
        
        public function getFeed(){
            return $this->_feed;
        }
        
        function getAttributesCodes(){
            return $this->_attributesCodes;
        }
        
        function prepareCollection($collection){
            
        }
        
        function getCompoundData($productData){
            return null;
        }
        
        function hasCondition(){
            return false;
        }
        
        function hasFilterCondition(){
            return false;
        }
        
        function validateFilterCondition($productData, $operatorCode, $valueCode){
            return Amasty_Feed_Model_Field_Condition::compare($operatorCode, $this->getCompoundData($productData), $valueCode);
        }
        
        function prepareCondition($collection, $operator, $condVal, &$attributesFields){
            
        }
        
        protected function _getCategoryId($productData){
            $categoryId = $productData['category_id'];
            
            if (!$categoryId || $categoryId == Mage::app()->getStore()->getRootCategoryId()) {
                $categoryIds = $this->_getCategoryIds($productData);
            
                if (count($categoryIds) > 0) {
                    $categoryId = $categoryIds[count($categoryIds) - 1];
                }
            }
            
            return $categoryId;
        }

        protected function _getCategoryIds($productData){
            $ret = array();
            $categoryIds = isset($productData['category_ids']) ?
                                    $productData['category_ids'] : null;
            if ($categoryIds) {
                $ret = explode(',', $categoryIds);
            }
            return $ret;
        }

        protected function _getCategoryName($categoryId){
            $ret = "";

            if (self::CATEGORIES_SHORT_PATH){
                $categories = $this->getFeed()->getProductCategoriesLast();

                if (isset($categories[$categoryId]))
                    $ret = $categories[$categoryId];

            } else {
                $categories = $this->getFeed()->getProductCategories();

                if (isset($categories[$categoryId]))
                    $ret = $categories[$categoryId];
            }

            return $ret;

        }
    }