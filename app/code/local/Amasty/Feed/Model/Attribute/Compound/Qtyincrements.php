<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

class Amasty_Feed_Model_Attribute_Compound_Qtyincrements extends Amasty_Feed_Model_Attribute_Compound_Abstract
{
    protected $_configQtyIncrements;

    function prepareCollection($collection)
    {
        $collection->joinIsInStock();
    }

    protected function _getQtyIncrements($productData)
    {
        if (array_key_exists('use_config_qty_increments', $productData) &&
            $productData['use_config_qty_increments'] === '1') {
            if ($this->_configQtyIncrements === null){
                $this->_configQtyIncrements = (int) Mage::getStoreConfigFlag(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_QTY_INCREMENTS);
            }
            return $this->_configQtyIncrements;
        }

        return array_key_exists('qty_increments', $productData) ?
            $productData['qty_increments'] : 0;
    }

    function getCompoundData($productData)
    {
        return $this->_getQtyIncrements($productData);
    }
}