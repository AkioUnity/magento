<?php
class Magebird_Popup_Model_System_Asyncoption
{
    public function toOptionArray()
    {
        return array(
            array('value'=>1, 'label'=>Mage::helper('magebird_popup')->__("Asynchronously (default)")),
            array('value'=>2, 'label'=>Mage::helper('magebird_popup')->__('Synchronous')),            
        );                           
    }

}