<?php

/**
 * MageWorx
 * MageWorx SeoXTemplates Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoXTemplates
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SeoXTemplates_Helper_Store extends Mage_Core_Helper_Abstract
{
    /**
     * Retrive store ids
     * @return array
     */
    public function getAllStoreIds()
    {
        $allStores  = Mage::app()->getStores();
        $storeIds[] = 0;
        foreach ($allStores as $_storeId => $store) {
            $storeIds[] = $_storeId;
        }
        return $storeIds;
    }

    /**
     *
     * @param int $id
     * @return boolean
     */
    public function isFirstStoreId($id)
    {
        $storeIds = $this->getAllEnabledStoreIds();
        if ($id == array_shift($storeIds)) {
            return true;
        }
        return false;
    }

    /**
     *
     * @param int $id
     * @return boolean
     */
    public function isLastStoreId($id)
    {
        $storeIds = $this->getAllEnabledStoreIds();
        if ($id == array_pop($storeIds)) {
            return true;
        }
        return false;
    }

   /**
    *
    * @param int $id
    * @return int
    * @throws Exception
    */
    public function getNextStoreId($id)
    {
        $storeIds = $this->getAllEnabledStoreIds();

        $keys    = array_keys($storeIds, $id);
        $current = array_shift($keys);
        $lastKey = $current + 1;
        if (array_key_exists($lastKey, $storeIds)) {
            return $storeIds[$lastKey];
        }
        throw new Exception('Next Store ID not found, use "isLastStoreId" check');
    }

    /**
     *
     * @return array
     */
    public function getAllEnabledStoreIds()
    {
        $stores   = $this->getAllEnabledStore();
        $storeIds = array();
        foreach ($stores as $store) {
            $storeIds[] = $store->getStoreId();
        }
        return $storeIds;
    }

    /**
     *
     * @return array
     */
    public function getAllEnabledStore()
    {
        $allStores = Mage::app()->getStores();
        $stores    = array();
        foreach ($allStores as $store) {
            if ($store->getIsActive() == 1) {
                $stores[] = $store;
            }
        }
        return $stores;
    }

    /**
     * @param int $id
     * @return Mage_Core_Model_Store
     */
    public function getStoreById($id)
    {
        return $storeData = Mage::getModel('core/store')->load($id);
    }

}
