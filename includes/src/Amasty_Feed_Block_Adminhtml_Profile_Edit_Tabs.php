<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */
class Amasty_Feed_Block_Adminhtml_Profile_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('profileTabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('amfeed')->__('Feed Options'));
    }

    protected function _getConditionBlock(){
        $profile = Mage::registry('amfeed_profile');
        
        $layout = $this->getLayout();
        $customBlocks = Mage::helper("amfeed/profile")->getCustomBlocks();
        
        $conditionBlock = $layout
            ->createBlock('amfeed/adminhtml_profile_edit_tab_condition')
            ->setModel($profile);
        
        foreach($customBlocks as $key => $path){
            $block = $layout
                ->createBlock('amfeed/adminhtml_control_profile')
                ->setModel($profile)
                ->setTemplate($path);
            
            $conditionBlock->setChild($key, $block);
            
        }
        
        return $conditionBlock;
        
    }

    protected function _beforeToHtml()
    {
        $this->addTab('general', array(
            'label'     => Mage::helper('amfeed')->__('General'),
            'content'   => $this->getLayout()->createBlock('amfeed/adminhtml_profile_edit_tab_general')->toHtml(),
        ));

        $this->addTab('content', array(
            'label'     => Mage::helper('amfeed')->__('Content'),
            'content'   => $this->getLayout()->createBlock('amfeed/adminhtml_profile_edit_tab_content')->toHtml(),
        ));
        
        $this->addTab('condition', array(
            'label'     => Mage::helper('amfeed')->__('Conditions'),
            'content'   => $this->_getConditionBlock()->toHtml(),
        ));
        
        $this->addTab('delivery', array(
            'label'     => Mage::helper('amfeed')->__('Delivery'),
            'content'   => $this->getLayout()->createBlock('amfeed/adminhtml_profile_edit_tab_delivery')->toHtml(),
        ));
        
        return parent::_beforeToHtml();
    }
}