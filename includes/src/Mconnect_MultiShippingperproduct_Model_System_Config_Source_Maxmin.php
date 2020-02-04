<?php

class Mconnect_MultiShippingperproduct_Model_System_Config_Source_Maxmin
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => "max", 'label'=>Mage::helper('adminhtml')->__('Max')),
            array('value' => "min", 'label'=>Mage::helper('adminhtml')->__('Min')),            
        );
    }
}