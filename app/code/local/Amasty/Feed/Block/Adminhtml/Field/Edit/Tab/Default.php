<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */ 
class Amasty_Feed_Block_Adminhtml_Field_Edit_Tab_Default extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $feed = Mage::registry('amfeed_field');
        
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        /* @var $hlp Amasty_Feed_Helper_Data */
        $hlp = Mage::helper('amfeed');
    
        $fldInfo = $form->addFieldset('general', array('legend'=> $hlp->__('General')));
        
        $fldInfo->addField('default_value', 'textarea', array(
            'label'     => $hlp->__('Default value'),
            'rows'      => 1,
            'cols'      => 1,
            'name'      => 'default_value',
        ));
        
        //set form values
        $form->setValues($feed->getData()); 
        
        return parent::_prepareForm();
    }
}