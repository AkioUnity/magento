<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */ 
class Amasty_Feed_Block_Adminhtml_Category_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        $hlp   = Mage::helper('amfeed');
        $model = Mage::registry('amfeed_category');

        $fldInfo = $form->addFieldset('amfeed_general', array('legend'=> $hlp->__('General')));

        $fldInfo->addField(
            'code',
            'text',
            array(
                'name' => 'code',
                'label' => $hlp->__('Code'),
                'title' => $hlp->__('Code'),
                'required' => true
            )
        );

        $fldInfo->addField(
            'name',
            'text',
            array(
                'name' => 'name',
                'label' => $hlp->__('Name'),
                'title' => $hlp->__('Name'),
                'required' => true
            )
        );

        $fldInfo->addField(
            'mapping',
            'text',
            array(
                'name' => 'mapping',
                'label' => $hlp->__('Categories'),
                'title' => $hlp->__('Categories'),
            )
        );

        $form->getElement(
            'mapping'
        )->setRenderer(
            $this->getLayout()->createBlock('amfeed/adminhtml_category_edit_tab_mapping')
        );

        $form->setValues($model->getData());
        
        return parent::_prepareForm();
    }
    
}