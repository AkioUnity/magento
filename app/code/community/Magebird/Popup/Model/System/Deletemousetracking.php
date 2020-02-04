<?php
class Magebird_Popup_Model_System_Deletemousetracking
{
    public function toOptionArray()
    {
        return array(
            array('value'=>1, 'label'=>Mage::helper('magebird_popup')->__("Keep data for 1 month")),
            array('value'=>2, 'label'=>Mage::helper('magebird_popup')->__("Keep data for 6 months")),
            array('value'=>3, 'label'=>Mage::helper('magebird_popup')->__('Keep data for 1 week')),
            array('value'=>4, 'label'=>Mage::helper('magebird_popup')->__("Don't delete old data (not recommended)"))            
        );                           
    }

}