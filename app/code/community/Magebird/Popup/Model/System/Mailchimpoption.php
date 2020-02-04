<?php
class Magebird_Popup_Model_System_Mailchimpoption
{
    public function toOptionArray()
    {
        return array(
            array('value'=>1, 'label'=>Mage::helper('magebird_popup')->__("Don't use Mailchimp integration")),
            array('value'=>2, 'label'=>Mage::helper('magebird_popup')->__('Add subscribers email to Mailchimp list only')),
            array('value'=>3, 'label'=>Mage::helper('magebird_popup')->__('Add subscribers email to Mailchimp list and Magento list'))            
        );                           
    }

}