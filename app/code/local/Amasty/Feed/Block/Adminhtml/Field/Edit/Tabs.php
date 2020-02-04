<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */
class Amasty_Feed_Block_Adminhtml_Field_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
   
    
    public function __construct()
    {
        parent::__construct();
        $this->setId('fieldTabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('amfeed')->__('Field Options'));
    }

    protected function _getAdvancedBlock(){
        $feed = Mage::registry('amfeed_field');
        
        $layout = $this->getLayout();
        $customBlocks = Mage::helper("amfeed/field")->getCustomBlocks();
        
        $advancedBlock = $layout
            ->createBlock('amfeed/adminhtml_field_edit_tab_advanced')
            ->setModel($feed);
        
        foreach($customBlocks as $key => $path){
            $block = $layout
                ->createBlock('amfeed/adminhtml_control')
                ->setModel($feed)
                ->setTemplate($path);
            
            $advancedBlock->setChild($key, $block);
            
        }        
        return $advancedBlock;
    }

    protected function _beforeToHtml()
    {
        $this->addTab('general', array(
            'label'     => Mage::helper('amfeed')->__('General'),
            'content'   => $this->getLayout()->createBlock('amfeed/adminhtml_field_edit_tab_general')->toHtml(),
        ));
        
        $this->addTab('advanced', array(
            'label'     => Mage::helper('amfeed')->__('Transform'),
            'content'   => $this->_getAdvancedBlock()->toHtml(),
        ));
        
        $this->addTab('mapping', array(
            'label'     => Mage::helper('amfeed')->__('Replace'),
            'content'   => 
                $this->getLayout()
                    ->createBlock('amfeed/adminhtml_field_edit_tab_mapping')
                    ->toHtml(),
        ));
        
        $this->addTab('default', array(
            'label'     => Mage::helper('amfeed')->__('Default'),
            'content'   => 
                $this->getLayout()
                    ->createBlock('amfeed/adminhtml_field_edit_tab_default')
                    ->toHtml(),
        ));
        
        return parent::_beforeToHtml();
    }
}