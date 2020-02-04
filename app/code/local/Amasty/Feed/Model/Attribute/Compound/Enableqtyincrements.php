<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

class Amasty_Feed_Model_Attribute_Compound_Enableqtyincrements extends Amasty_Feed_Model_Attribute_Compound_Abstract
{
    protected $_configEnableQtyIncrements;

    function prepareCollection($collection)
    {
        $collection->joinIsInStock();
    }

    protected function _getEnableQtyIncrements($productData)
    {
        if (array_key_exists('use_config_enable_qty_inc', $productData) &&
            $productData['use_config_enable_qty_inc'] === '1') {
            if ($this->_configEnableQtyIncrements === null){
                $this->_configEnableQtyIncrements = (int) Mage::getStoreConfigFlag(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_ENABLE_QTY_INCREMENTS);
            }
            return $this->_configEnableQtyIncrements;
        }

        return array_key_exists('enable_qty_increments', $productData) ?
            $productData['enable_qty_increments'] : 0;
    }

    function getCompoundData($productData)
    {
        return $this->_getEnableQtyIncrements($productData);
    }
}