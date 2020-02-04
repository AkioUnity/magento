<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */
class Amasty_Feed_Block_Adminhtml_Category_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('categoryTabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('amfeed')->__('Categories'));
    }


    protected function _beforeToHtml()
    {
        $tabs = array(
            'general'    => 'General'
        );

        foreach ($tabs as $code => $label){

            $block = $this->getLayout()->createBlock('amfeed/adminhtml_category_edit_tab_' . $code);

            $content = $block
                ->setTitle($label)
                ->toHtml();

            $this->addTab($code, array(
                'label'     => $label,
                'content'   => $content,
            ));
        }

        return parent::_beforeToHtml();
    }
}