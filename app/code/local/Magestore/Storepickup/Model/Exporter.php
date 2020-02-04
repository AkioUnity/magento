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
 * Class Magestore_Storepickup_Model_Exporter
 */
class Magestore_Storepickup_Model_Exporter extends Varien_Object
{
    /**
     * @var string
     */
    var $_fieldstr = 'store_name,store_manager,store_email,store_phone,store_fax,description,address,address_2,region,state,city,suburb,zipcode,country,store_latitude,store_longitude,monday_status,monday_time_interval,monday_open,monday_close,tuesday_status,tuesday_time_interval,tuesday_open,tuesday_close,wednesday_status,wednesday_time_interval,wednesday_open,wednesday_close,thursday_status,thursday_time_interval,thursday_open,thursday_close,friday_status,friday_time_interval,friday_open,friday_close,saturday_status,saturday_time_interval,saturday_open,saturday_close,sunday_status,sunday_time_interval,sunday_open,sunday_close,minimum_gap';

    /**
     * @return bool|string
     */
    public function exportStore()
	{
		$stores = Mage::getResourceModel('storepickup/store_collection');
		
		if(!count($stores))
			return false;
			
		foreach($stores as $store)
		{
			$data[] = $this->getStandData($store);
		}
		
		$csv = '';
		$csv .= $this->_fieldstr ."\n";

		foreach($data as $row)
		{
			$rowstr = implode('","',$row);
			$rowstr = '"'.$rowstr.'"';
			$csv .= $rowstr."\n";
		}
		
		return $csv;
	}

    /**
     * @return bool|string
     */
    public function getXmlStore()
	{
		$stores = Mage::getResourceModel('storepickup/store_collection');
		$storecollection = array();
		if(!count($stores))
			return false;
		
		foreach($stores as $store)
		{
			$data = $this->getStandData($store);
			$store->setData($data);
			$storecollection[] = $store;
		}
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml.= '<items>';
        foreach ($storecollection as $item) {
            $xml.= $item->toXml();
        }
        $xml.= '</items>';	
		
		return $xml;
	}

    /**
     * @param $store
     * @return array
     */
    public function getStandData($store)
	{
		$data = $store->getData();
		//prepare location
		$data['suburb'] = $store->getSuburb();
		$data['city'] = $store->getCity();
		$data['region'] = $store->getRegion();
		$data['state'] = $store->getState();
		$fields = $this->_getFields();
		
		$export_data = array();
		foreach($fields as $field)
		{
			$value = isset($data[$field]) ? $data[$field] : '';
			$export_data[$field] = $value;
		}
		
		return $export_data;
	}

    /**
     * @return mixed
     */
    protected function _getFields()
	{
		if(! $this->getData('fields'))
		{
			$fields = explode(',',$this->_fieldstr);
			$this->setData('fields',$fields);
		}
		
		return $this->getData('fields');
	}
}