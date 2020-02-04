<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Table
 */


class Amasty_Table_Model_Config_Source_Volumetrictype extends Varien_Object
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $helper = Mage::helper('amtable');
        $options = array();
        $values = array(
            Amasty_Table_Model_Weight::VOLUMETRIC_WEIGHT_ATTRIBUTE_TYPE
                => $helper->__('Volumetric weight attribute'),
            Amasty_Table_Model_Weight::VOLUME_ATTRIBUTE_TYPE
                => $helper->__('Volume attribute'),
            Amasty_Table_Model_Weight::VOLUMETRIC_DIMENSIONS_ATTRIBUTE_TYPE
                => $helper->__('Dimensions attribute'),
            Amasty_Table_Model_Weight::VOLUMETRIC_SEPARATE_DIMENSION_ATTRIBUTE_TYPE
                => $helper->__('Separate Dimension attributes')
        );

        foreach ($values as $key => $value) {
            $options[] = array(
                'value' => $key,
                'label' => $value
            );
        }

        return $options;
    }
}
