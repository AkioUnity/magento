<?php

class MagicToolbox_MagicZoom_Block_Adminhtml_Settings_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {

        parent::__construct();

        $this->setId('magiczoom_config_tabs');
        $this->setDestElementId('edit_form');//this should be same as the form id
        $this->setTitle('<span style="visibility: hidden">'.Mage::helper('magiczoom')->__('Supported blocks:').'</span>');

    }

    protected function _beforeToHtml()
    {

        $blocks = Mage::helper('magiczoom/params')->getProfiles();
        $activeTab = $this->getRequest()->getParam('tab', 'product');

        foreach ($blocks as $id => $label) {
            $this->addTab($id, array(
                'label'     => Mage::helper('magiczoom')->__($label),
                'title'     => Mage::helper('magiczoom')->__($label.' settings'),
                'content'   => $this->getLayout()->createBlock('magiczoom/adminhtml_settings_edit_tab_form', 'magiczoom_'.$id.'_settings_block')->toHtml(),
                'active'    => ($id == $activeTab) ? true : false
            ));
        }

        //NOTE: promo section for Sirv extension
        $this->addTab('promo', array(
            'label'     => Mage::helper('magiczoom')->__('CDN and Image Processing'),
            'title'     => Mage::helper('magiczoom')->__('CDN and Image Processing'),
            'content'   => $this->getLayout()->createBlock(
                'magiczoom/adminhtml_settings_edit_tab_promo',
                'magiczoom_promo_block'
            )->toHtml(),
            'active'    => ('promo' == $activeTab)
        ));

        return parent::_beforeToHtml();
    }
}
