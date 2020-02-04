<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */ 
class Amasty_Feed_Block_Adminhtml_Google_Edit_Tab_Category extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $categoryMapper = Mage::registry(Amasty_Feed_Model_Google::VAR_CATEGORY_MAPPER);
        $identifierExists = Mage::registry(Amasty_Feed_Model_Google::VAR_IDENTIFIER_EXISTS);
        $feed = Mage::registry(Amasty_Feed_Model_Google::VAR_FEED_ID);
        $step = Mage::registry(Amasty_Feed_Model_Google::VAR_STEP);

        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        $hlp   = Mage::helper('amfeed');

        $fldInfo = $form->addFieldset('amfeed_general', array('legend'=> $hlp->__('Step 1: Categories')));

        if ($categoryMapper->getId()) {
            $fldInfo->addField('feed_category_id', 'hidden', array(
                'name' => 'feed_category_id',
            ));
        }

        $fldInfo->addField(
            'mapping',
            'text',
            array(
                'name' => 'mapping',
                'label' => $hlp->__('Categories'),
                'title' => $hlp->__('Categories'),
                'hideLabel' => true,
                'note' => 'Please check <a target="_blank" href="https://support.google.com/merchants/answer/1705911?hl=en">Google Taxonomy</a> and associate your categories to Google\'s according to requirements.<br/><b>Notice:</b> you should define full path when associating category, just like in taxonomy.<br/>For example if you want to associate category where you have Shorts, you should rename it to "Apparel & Accessories > Clothing > Shorts"'
            )
        );

        $form->getElement(
            'mapping'
        )->setRenderer(
            $this->getLayout()->createBlock('amfeed/adminhtml_category_edit_tab_mapping')
        );

        $form->setValues($categoryMapper->getData());

        $fldInfo->addField('step', 'hidden', array(
            'name'  => 'step',
            'value' => $step,
        ));

        if ($identifierExists->getId()){
            $fldInfo->addField('identifier_exists_id', 'hidden', array(
                'name'  => 'identifier_exists_id',
                'value' => $identifierExists->getId(),
            ));
        }

        if ($feed->getId()){
            $fldInfo->addField('feed_id', 'hidden', array(
                'name'  => 'feed_id',
                'value' => $feed->getId(),
            ));
        }

        return parent::_prepareForm();
    }
    
}