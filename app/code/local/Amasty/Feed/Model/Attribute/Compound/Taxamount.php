<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

    class Amasty_Feed_Model_Attribute_Compound_Taxamount extends Amasty_Feed_Model_Attribute_Compound_Abstract
    {
        protected $_applyTaxAfterDiscount;
        function prepareCollection($collection)
        {
            $collection->joinTaxPercents($this->getFeed()->getStore());
        }

        protected function _applyTaxAfterDiscount()
        {
            if ($this->_applyTaxAfterDiscount === null){
                $this->_applyTaxAfterDiscount = Mage::helper('tax')->applyTaxAfterDiscount($this->getFeed()->getStore());
            }

            return $this->_applyTaxAfterDiscount;
        }

        function getCompoundData($productData)
        {
            if ($this->_applyTaxAfterDiscount()) {
                $total = $productData['final_price'];
            } else {
                $total = $productData['price'];
            }

            $taxPercent = $productData['tax_percents'] /100;

            $taxAmount = $total * $taxPercent;

            return $taxAmount;
        }
    }