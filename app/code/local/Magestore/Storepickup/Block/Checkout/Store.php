<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Storepickup
 * @module      Storepickup
 * @author      Magestore Developer
 *
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */

/**
 * Class Magestore_Storepickup_Block_Checkout_Store
 */
class Magestore_Storepickup_Block_Checkout_Store extends Mage_Core_Block_Template {

    /**
     * Magestore_Storepickup_Block_Checkout_Store constructor.
     */
    public function __construct() {
        parent::__construct();

        $this->setData('shipping_model', Mage::getModel('storepickup/shipping_storepickup'));
    }

    /**
     * @return mixed
     */
    public function _prepareLayout() {
        $return = parent::_prepareLayout();

        $listStore = $this->getStoreByLocation();

        $this->setListStoreLocation($listStore);

        //$storeId = Mage::app()->getStore()->getId();
        //$storemap = Mage::getStoreConfig("carriers/storepickup/store_map", $storeId);
        $modulecheckout = Mage::app()->getRequest()->getModuleName();

        if ($modulecheckout == 'onestepcheckout') {
                $this->setTemplate('storepickup/onestepcheckout_storepickupmap_idev.phtml');
        } elseif ($modulecheckout == 'gomage_checkout') {
            $this->setTemplate('storepickup/gomage_checkout_storepickupmap.phtml');
        } else {
            $this->setTemplate('storepickup/storepickupmap.phtml');
        }
        return $return;
    }

    /**
     * @return mixed
     */
    public function getListTime() {
        return Mage::helper('storepickup')->getListTime();
    }

    /**
     * @return bool
     */
    public function has_stores() {
        return true;
    }

    /**
     * @return mixed
     */
    public function getStoreByLocation() {
        if (!$this->hasData('storecollection')) {
           
                $stores = Mage::getSingleton('storepickup/store')->filterStoresUseGAPI();
            
            $this->setData('storecollection', $stores);
        }
        return $this->getData('storecollection');
       
    }
   

    //add for Store map
    /**
     * @return mixed
     */
    public function getStore() {
        if (!$this->hasData('store_data')) {
            $stores = Mage::getModel('storepickup/store')->getCollection()
                    ->addFieldToFilter('status', 1)
                    ->getFirstItem();
            ;
            $this->setData('store_data', $stores);
        }

        return $this->getData('store_data');
    }

    /**
     * @return mixed
     */
    public function getCoordinates() {
        $store = $this->getStore();
        $address['street'] = $store->getSuburb();
        $address['street'] = '';
        $address['city'] = $store->getCity();
        $address['region'] = $store->getRegion();
        $address['zipcode'] = $store->getZipcode();
        $address['country'] = $store->getCountryName();

        $coordinates = Mage::getModel('storepickup/gmap')
                ->getCoordinates($address);
        if (!$coordinates) {
            $coordinates['lat'] = '0.000';
            $coordinates['lng'] = '0.000';
        }

        return $coordinates;
    }

    /**
     * @return array
     */
    public function getMapSize() {
        $storeId = Mage::app()->getStore()->getId();
        $mapsize = trim(Mage::getStoreConfig("carriers/storepickup/map_size", $storeId));
        $size = explode('*', $mapsize);
        return $size;
    }

    /**
     * @return mixed
     */
    public function getStoreByDefault() {

        if (!$this->hasData('storedefault')) {
            if ($this->getShippingModel()->getConfigData('active_gapi')) {
                $stores = Mage::getSingleton('storepickup/store')->filterdefaultStoresUseGAPI();
            } else {
                $stores = Mage::getSingleton('storepickup/store')->convertToDefault();
            }
            $this->setData('storedefault', $stores);
        }
        return $this->getData('storedefault');
    }

    /**
     * @return mixed
     */
    public function getAllid() {
        return Mage::getModel('storepickup/store')->getCollection()->getAllIds();
    }

}
