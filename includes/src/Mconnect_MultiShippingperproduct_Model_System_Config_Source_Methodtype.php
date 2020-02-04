<?php

class Mconnect_MultiShippingperproduct_Model_System_Config_Source_Methodtype
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => "per product", 'label'=>Mage::helper('adminhtml')->__('Per Product')),
            array('value' => "per order", 'label'=>Mage::helper('adminhtml')->__('Per Order')),            
        );
    }
}