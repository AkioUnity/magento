<?php
class Magebird_Popup_Model_System_Addon extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('magebird/popup/addons.phtml');
    }
    
    
    public function render(Varien_Data_Form_Element_Abstract $element){
      return $this->_toHtml();
    }

    
    public function isActivated($addOn){
      return Mage::helper('magebird_popup')->addOnActivated($addOn);
    }


}