<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Table
 */


class Amasty_Table_Model_Config_Source_Volumetric extends Varien_Object
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')->getItems();
        $options[] = array(
                'value' => '',
                'label' => Mage::helper('amtable')->__('None')
        );

        foreach ($attributes as $attribute) {
            if (!$attribute->getFrontendLabel()) {
                continue;
            }

            $options[] = array(
                'value' => $attribute->getAttributeCode(),
                'label' => $attribute->getFrontendLabel()
            );
        }

        return $options;
    }
}
