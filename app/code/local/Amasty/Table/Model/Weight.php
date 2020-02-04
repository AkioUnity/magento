<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Table
 */


class Amasty_Table_Model_Weight extends Mage_Core_Model_Abstract
{
    const PATTERN_VALID_VOLUME_DIMENSION = '/^((?:\d+?)(?:[.,](?:\d+?)(?=[^\d.,\s]))?)(?:[^\d.,\s])((?:\d+?)(?:[.,](?:\d+?)(?=[^\d.,\s]))?)(?:[^\d.,\s])((?:\d+?)(?:[.,](?:\d+?))?)$/';
    const VOLUMETRIC_WEIGHT_ATTRIBUTE_TYPE = 'volumetric_weight_attribute';
    const VOLUME_ATTRIBUTE_TYPE = 'volume_attribute';
    const VOLUMETRIC_DIMENSIONS_ATTRIBUTE_TYPE = 'dimmensions_attribute';
    const VOLUMETRIC_SEPARATE_DIMENSION_ATTRIBUTE_TYPE= 'separate_dimmension_attribute';

    /**
     * @param $item
     * @return float|int
     */
    public function getItemWeight($item)
    {
        $volumetricWeightValue = $this->calculateVolumetricWeight($item);

        return $volumetricWeightValue && ($volumetricWeightValue > $item->getWeight()) ?
            $volumetricWeightValue :
            $item->getWeight();
    }

    /**
     * @param $item
     * @return float|int
     */
    public function calculateVolumetricWeight($item)
    {
        $helper = Mage::helper('amtable');
        $productId = $item->getProductId();
        $store = Mage::app()->getStore();
        $productModel = Mage::getResourceModel('catalog/product');
        $volumetricWeight = 0;
        $attributeCodes = array();
        $helper->getShippingFactor();

        switch ($helper->getVolumetricWeighType()) {
            case self::VOLUMETRIC_WEIGHT_ATTRIBUTE_TYPE:
                $attributeCode = $helper->getVolumetricWeightAttribute();
                $volumetricWeight =
                    (float) $helper->getProductAttributeValue($productModel, $productId, $attributeCode, $store);
                break;

            case self::VOLUME_ATTRIBUTE_TYPE:
                $attributeCode = $helper->getVolumeAttribute();
                $volumetricWeight =
                    (float) $helper->getProductAttributeValue($productModel, $productId, $attributeCode, $store) /
                    $helper->getShippingFactor();
                break;

            case self::VOLUMETRIC_DIMENSIONS_ATTRIBUTE_TYPE:
                $attributeCode = $helper->getDimensionsAttribute();
                $dimensions = $helper->getProductAttributeValue($productModel, $productId, $attributeCode, $store);
                $volumetricWeight = $this->calculateVolumeByDimensionsAttribute($dimensions) /
                    $helper->getShippingFactor();
                break;

            case self::VOLUMETRIC_SEPARATE_DIMENSION_ATTRIBUTE_TYPE:
                $attributeCodes[] = $helper->getFirstDimensionsAttribute();
                $attributeCodes[] = $helper->getSecondDimensionsAttribute();
                $attributeCodes[] = $helper->getThirdDimensionsAttribute();
                $volumetricWeight = $this->getVolume($productModel, $productId, $attributeCodes, $store) /
                    $helper->getShippingFactor() ;
                break;
        }

        return $volumetricWeight;
    }

    /**
     * @param $productModel
     * @param $productId
     * @param $attributeCodes
     * @param $store
     * @return float|int
     */
    public function getVolume($productModel, $productId, $attributeCodes, $store)
    {
        $volume = 1;
        $helper = Mage::helper('amtable');

        foreach ($attributeCodes as $attributeCode) {
            $volume *= (float) $helper->getProductAttributeValue($productModel, $productId, $attributeCode, $store);
        }

        return $volume;
    }

    /**
     * @param string $dimensions
     * @return float
     */
    public function calculateVolumeByDimensionsAttribute($dimensions = '')
    {
        $volume = 0;
        if ($this->isVolumeDimensions($dimensions)) {
            $dimensionNumbers = array();
            preg_match(self::PATTERN_VALID_VOLUME_DIMENSION, $dimensions, $dimensionNumbers);
            array_shift($dimensionNumbers);

            if (!empty($dimensionNumbers)) {
                $volume = 1;
                foreach ($dimensionNumbers as $number) {
                    $number = str_replace(',', '.', $number);
                    $volume *= (float)$number;
                }
            }
        }

        return (float) $volume;
    }

    /**
     * @param string $dimensions
     * @return bool
     */
    protected function isVolumeDimensions($dimensions = '')
    {
        return Zend_Validate::is($dimensions, 'Regex', array(self::PATTERN_VALID_VOLUME_DIMENSION));
    }
}
