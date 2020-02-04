<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Table
 */ 
class Amasty_Table_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_VOLUMETRIC_WEIGHT_TYPE = 'carriers/amtable/volumetric_weight';
    const XML_PATH_VOLUMETRIC_WEIGHT_ATTRIBUTE = 'carriers/amtable/volumetric_attribute';
    const XML_PATH_VOLUME_ATTRIBUTE = 'carriers/amtable/volume_attribute';
    const XML_PATH_SHIPPING_FACTOR = 'carriers/amtable/shipping_factor';
    const XML_PATH_DIMENSIONS_ATTRIBUTE = 'carriers/amtable/dimensions_attribute';
    const XML_PATH_FIRST_DIMENSION_ATTRIBUTE = 'carriers/amtable/first_sep_dimension_attribute';
    const XML_PATH_SECOND_DIMENSION_ATTRIBUTE = 'carriers/amtable/second_sep_dimension_attribute';
    const XML_PATH_THIRD_DIMENSION_ATTRIBUTE = 'carriers/amtable/third_sep_dimension_attribute';

    public function getAllGroups()
    {
        $customerGroups = Mage::getResourceModel('customer/group_collection')
            ->load()->toOptionArray();

        $found = false;
        foreach ($customerGroups as $group) {
            if ($group['value']==0) {
                $found = true;
            }
        }
        if (!$found) {
            array_unshift($customerGroups, array('value'=>0, 'label'=>Mage::helper('salesrule')->__('NOT LOGGED IN')));
        } 
        
        return $customerGroups;
    }
    
    public function getStatuses()
    {
        return array(
            '0' => $this->__('Inactive'),
            '1' => $this->__('Active'),
        );       
    }
      
    public function getStates()
    {
        $hash = array();
        $hashCountry = $this->getCountries();
        
        $collection = Mage::getResourceModel('directory/region_collection')->getData();

        foreach ($collection as $state){
            $hash[$state['region_id']] = $hashCountry[$state['country_id']] ."/".$state['default_name'];
        }
        asort($hash);
        $hashAll['0'] = 'All';
        $hash = $hashAll + $hash;        
        return $hash;    
    }
        
    public function getCountries()
    {
        $hash = array();
        $countries = Mage::getModel('directory/country')->getCollection()->toOptionArray();

        foreach ($countries as $country){
            if($country['value']){
                $hash[$country['value']] = $country['label'];                
            }
        }
        asort($hash);
        $hashAll['0'] = 'All';
        $hash = $hashAll + $hash; 
        return $hash;    
    } 
   
    public function getTypes($includeAll = true)
    {
        $hash = array();
        $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'am_shipping_type');
        if ($attribute->usesSource()) {
            $options = $attribute->getSource()->getAllOptions(false);
        }
        foreach ($options as $option){
            $hash[$option['value']] = $option['label'];    
        }
        asort($hash);
        if ($includeAll) {
            $hashAll['0'] = 'All';
            $hash = $hashAll + $hash;
        }
        return $hash;
    }

    /**
     * @param $zip
     * @return array('area', 'district')
     */
    public function getDataFromZip($zip)
    {
        $dataZip = array('area' => '', 'district' => '');

        if (!empty($zip)) {
            $zipSpell = str_split($zip);
            foreach ($zipSpell as $element) {
                if ($element === ' ') {
                    break;
                }
                if (is_numeric($element) || (!is_numeric($element) && !empty($dataZip['district']))) {
                    $dataZip['district'] = $dataZip['district'] . $element;
                } elseif (empty($dataZip['district'])) {
                    $dataZip['area'] = $dataZip['area'] . $element;
                }
            }
        }

        return $dataZip;
    }

    public function _isRateResultRewritten()
    {
        $isRewritten = false;

        $modelName = Mage::getConfig()->getModelClassName('shipping/rate_result');
        if ($modelName == 'Amasty_Table_Model_Shipping_Rate_Result') {
            $isRewritten = true;
        }

        return $isRewritten;
    }

    public function getAllShippingTypes()
    {
        $result = array();
        foreach ($this->getTypes(false) as $value => $label) {
            $result[] = array('value' => $value, 'label' => $label);
        }
        return $result;
    }

    /**
     * @param $productModel
     * @param $productId
     * @param $attributeCode
     * @param $store
     * @return mixed
     */
    public function getProductAttributeValue($productModel, $productId, $attributeCode, $store)
    {
        return $productModel->getAttributeRawValue($productId, $attributeCode, $store);
    }

    /**
     * @return mixed
     */
    public function getVolumetricWeightAttribute()
    {
        return Mage::getStoreConfig(self::XML_PATH_VOLUMETRIC_WEIGHT_ATTRIBUTE);
    }

    /**
     * @return mixed
     */
    public function getVolumetricWeighType()
    {
        return Mage::getStoreConfig(self::XML_PATH_VOLUMETRIC_WEIGHT_TYPE);
    }

    /**
     * @return mixed
     */
    public function getVolumeAttribute()
    {
        return Mage::getStoreConfig(self::XML_PATH_VOLUME_ATTRIBUTE);
    }

    /**
     * @return int
     */
    public function getShippingFactor()
    {
        return (int) Mage::getStoreConfig(self::XML_PATH_SHIPPING_FACTOR);
    }

    /**
     * @return mixed
     */
    public function getDimensionsAttribute()
    {
        return Mage::getStoreConfig(self::XML_PATH_DIMENSIONS_ATTRIBUTE);
    }

    /**
     * @return mixed
     */
    public function getFirstDimensionsAttribute()
    {
        return Mage::getStoreConfig(self::XML_PATH_FIRST_DIMENSION_ATTRIBUTE);
    }

    /**
     * @return mixed
     */
    public function getSecondDimensionsAttribute()
    {
        return Mage::getStoreConfig(self::XML_PATH_SECOND_DIMENSION_ATTRIBUTE);
    }

    /**
     * @return mixed
     */
    public function getThirdDimensionsAttribute()
    {
        return Mage::getStoreConfig(self::XML_PATH_THIRD_DIMENSION_ATTRIBUTE);
    }
}
