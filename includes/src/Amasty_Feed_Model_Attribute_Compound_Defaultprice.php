<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

    class Amasty_Feed_Model_Attribute_Compound_Defaultprice extends Amasty_Feed_Model_Attribute_Compound_Abstract
    {
        protected $_code = 'price';

        function prepareCollection($collection){
            $collection->joinPrice();
        }

        function getCompoundData($productData){
            $price = !empty($productData[$this->_code]) ? $productData[$this->_code] : $productData['price'];
            return $productData[$this->_code];
        }
    }