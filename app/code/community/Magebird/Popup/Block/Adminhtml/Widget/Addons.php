<?php 
class Magebird_Popup_Block_Adminhtml_Widget_Addons extends Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element
{
    public function render(Varien_Data_Form_Element_Abstract $element){   
      $msg = Mage::helper('magebird_popup')->__("You can use this widget only inside Magebird Popup extension."); 
      $html =  "<script>if(window.location.href.indexOf('popup')<=0){alert('$msg');}</script>";           
      return $html;
    }     
}