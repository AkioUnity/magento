<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

    class Amasty_Feed_Model_Attribute_Compound_Isinstock extends Amasty_Feed_Model_Attribute_Compound_Abstract
    {
        protected $_configManageStock;

        function prepareCollection($collection){
            $collection->joinIsInStock();
        }

        protected function _getManageStock($productData)
        {
            if (array_key_exists('use_config_manage_stock', $productData) &&
                $productData['use_config_manage_stock'] === '1') {
                if ($this->_configManageStock === null){
                    $this->_configManageStock = (int) Mage::getStoreConfigFlag(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);
                }
                return $this->_configManageStock;
            }

            return array_key_exists('manage_stock', $productData) &&
                $productData['manage_stock'] === '1' ? 1 : 0;
        }

        protected function _getIsInStock($productData)
        {
            if (!$this->_getManageStock($productData)) {
                return true;
            }

            return array_key_exists('is_in_stock', $productData) &&
                $productData['is_in_stock'] === '1' ? 1 : 0;
        }
        
        function getCompoundData($productData){
            $hlr = Mage::helper("amfeed");

            return $this->_getIsInStock($productData) ?
                $hlr->__("In Stock") : $hlr->__("Out of Stock");
        }
    }