<?php
class Magebird_Popup_Model_System_Requesttype
{
    public function toOptionArray()
    {
        if (version_compare(Mage::getVersion(), '1.5', '<')){
                return array(
                    array('value'=>3, 'label'=>Mage::helper('magebird_popup')->__("GET2"))            
                );
        }
    
        return array(
            array('value'=>1, 'label'=>Mage::helper('magebird_popup')->__("GET")),
            array('value'=>2, 'label'=>Mage::helper('magebird_popup')->__('POST')),
            array('value'=>3, 'label'=>Mage::helper('magebird_popup')->__("GET2 (don't choose unless advised otherwise)")),            
        );                           
    }

}