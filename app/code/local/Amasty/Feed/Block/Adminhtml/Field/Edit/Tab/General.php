<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */ 
class Amasty_Feed_Block_Adminhtml_Field_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $feed = Mage::registry('amfeed_field');
        
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        /* @var $hlp Amasty_Feed_Helper_Data */
        $hlp = Mage::helper('amfeed');
    
        $fldInfo = $form->addFieldset('general', array('legend'=> $hlp->__('General')));
        $fldInfo->addField('title', 'text', array(
            'label'     => $hlp->__('Name'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'title',
        ));
        $fldInfo->addField('code', 'text', array(
            'label'     => $hlp->__('Code'),
            'required'  => true,
            'name'      => 'code',
            'class'		=> 'validate-code',
            'note'		=> $hlp->__('For internal use. Must be unique with no spaces'),
        ));
        
        //set form values
        $form->setValues($feed->getData()); 
        
        return parent::_prepareForm();
    }
}