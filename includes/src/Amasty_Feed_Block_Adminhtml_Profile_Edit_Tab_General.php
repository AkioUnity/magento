<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */ 
class Amasty_Feed_Block_Adminhtml_Profile_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        $hlp   = Mage::helper('amfeed');
        $model = Mage::registry('amfeed_profile');
        
        // rule info fieldset
        $fldInfo = $form->addFieldset('amfeed_info', array('legend'=> $hlp->__('Info')));
        
        if (!Mage::app()->isSingleStoreMode()){
            $fldInfo->addField('store_id', 'select', array(
                'label'    => $hlp->__('Store View'),
                'class'    => 'required-entry',
                'required' => true,
                'name'     => 'store_id',
                'values'   => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm()
            ));            
        } 
        else {
            $fldInfo->addField('store_id', 'hidden', array(
                'name'  => 'store_id',
                'value' => Mage::app()->getStore(true)->getId(),
            )); 
            $model->setStoreId(Mage::app()->getStore(true)->getId());            
        }
        
        $fldInfo->addField('title', 'text', array(
            'label'    => $hlp->__('Name'),
            'required' => true,
            'name'     => 'title',
        )); 
        
        $fldInfo->addField('type', 'select', array(
            'label'    => $hlp->__('Type'),
            'class'    => 'required-entry',
            'required' => true,
            'name'     => 'type',
            'values'   => array(
                Amasty_Feed_Model_Profile::TYPE_CSV => $hlp->__('CSV'),
                Amasty_Feed_Model_Profile::TYPE_XML => $hlp->__('XML'),
                Amasty_Feed_Model_Profile::TYPE_TXT => $hlp->__('TXT'),
             ),
            'onchange' => 'amfeed_toggleContentType()', 
        ));        
        
        $fldInfo->addField('filename', 'text', array(
            'label'    => $hlp->__('Filename'),
            'class'    => 'required-entry',
            'required' => true,
            'name'     => 'filename',
            'note'     => $hlp->__('Do not specify filename extension.'),
        ));
        
        $fldInfo->addField('mode', 'select', array(
            'label'    => $hlp->__('Mode'),
            'class'    => 'required-entry',
            'required' => true,
            'name'     => 'mode',
            'values'   => Mage::getModel('amfeed/source_mode')->toOptionArray()
        ));

        if (!is_array($model->getCronTime())) {
            $cronTimes = explode(",", $model->getCronTime());
        } else {
            $cronTimes = $model->getCronTime();
        }
        
        $fldInfo->addField('cron_time', 'multiselect', array(
            'label'    => $hlp->__('Cron Execution Time'),
            'name'     => 'cron_time[]',
            'values'   => Mage::getModel('amfeed/source_time')->toOptionArray(),
            'value'    => $cronTimes,
            'note'     => $hlp->__('Working only for Hourly/Daily/Weekly/Monthly modes'),
        ));
        
        $fldInfo->addField('send_to', 'text', array(
            'label'    => $hlp->__('Send to'),
//            'class'    => 'required-entry',
//            'required' => true,
            'name'     => 'send_to',
            'note'     => $hlp->__('Send download link to email(s)'),
        ));
        
        $fldInfo->addField('delivery_type', 'select', array(
            'label'    => $hlp->__('Delivery Type'),
            'class'    => 'required-entry',
            'required' => true,
            'name'     => 'delivery_type',
            'values'   => $hlp->getDeliveryTypes(true),
            'onchange' => 'amfeed_toggleDeliveryType()', 
        ));
        
        $fldInfo->addField('max_images', 'text', array(
            'label' => $hlp->__('Default number of additional images'),
            'name' => 'max_images',
        ));

        $fldInfo->addField(
            'compress',
            'select',
            array(
                'label' => __('Compress'),
                'name' => 'compress',
                'options' =>  Mage::getSingleton('amfeed/source_compress')->toArray()
            )
        );
        
        $form->setValues($model->getData());
        
        return parent::_prepareForm();
    }
    
}