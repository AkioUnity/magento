<?php
/**
 * Magebird.com
 *
 * @category   Magebird
 * @package    Magebird_Popup
 * @copyright  Copyright (c) 2018 Magebird (http://www.Magebird.com)
 * @license    http://www.magebird.com/licence
 * Any form of ditribution, sell, transfer forbidden see licence above 
 */
class Magebird_Popup_Block_Adminhtml_Popup_Edit_Tab_Customfields extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('customfields_form', array('legend' => Mage::helper('magebird_popup')->__('Custom fields')));        
        
        $fieldset->addField('your_custom_field1','text',array(
                    'label' => Mage::helper('magebird_popup')->__('Your custom field1 label'),
                    'name' =>  'your_custom_field1',                                                                                                      
        ));
        
        
        $fieldset->addField('your_custom_field2','select',array(
                    'label' => Mage::helper('magebird_popup')->__('Your custom field2 label'),
                    'name' =>  'your_custom_field2',
                    'required'=>true, 
              		  'values'    => array(
              			  array(
              				  'value'     => 1,
              				  'label'     => Mage::helper('magebird_popup')->__('Select value1'),
              			  ),            
              			  array(
              				  'value'     => 2,
              				  'label'     => Mage::helper('magebird_popup')->__('Select value2'),
              			  ),                                                                                                 
              		  ),            
        ));                   
    
    		if(Mage::getSingleton('adminhtml/session')->getPopupData()){
          $form->setValues(Mage::getSingleton('adminhtml/session')->getPopupData());
          Mage::getSingleton('adminhtml/session')->setPopupData(null);
        }elseif(Mage::registry('popup_data')){   
          $form->setValues(Mage::registry('popup_data')->getData());
        }
        return parent::_prepareForm();
  }
    
}
