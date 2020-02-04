<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Storepickup
 * @module      Storepickup
 * @author      Magestore Developer
 *
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */

/**
 * Class Magestore_Storepickup_Block_Adminhtml_Store_Edit_Tabs
 */
class Magestore_Storepickup_Block_Adminhtml_Store_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    /**
     * Magestore_Storepickup_Block_Adminhtml_Store_Edit_Tabs constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->setId('storepickup_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('storepickup')->__('Store Information'));
    }

    /**
     * @return mixed
     */
    protected function _beforeToHtml() {
        
        $generalTab = new Varien_Object();
        $generalTab->setContent($this->getLayout()->createBlock('storepickup/adminhtml_store_edit_tab_form')->toHtml());
        Mage::dispatchEvent('storepickup_general_information_tab_before', 
                array('tab' => $generalTab,
                    'store_id' => $this->getRequest()->getParam('id')));
        
        $this->addTab('form_section', array(
            'label' => Mage::helper('storepickup')->__('General Information'),
            'title' => Mage::helper('storepickup')->__('General Information'),
            'content' => $generalTab->getContent(),
        ));
        $this->addTab('timeschedule_section', array(
            'label' => Mage::helper('storepickup')->__('Time Schedule'),
            'title' => Mage::helper('storepickup')->__('Time Schedule'),
            'content' => $this->getLayout()->createBlock('storepickup/adminhtml_store_edit_tab_timeschedule')->toHtml(),
        ));
        $this->addTab('message_section', array(
            'label' => Mage::helper('storepickup')->__('Customer Messages'),
            'title' => Mage::helper('storepickup')->__('Customer Messages'),
            'url' => $this->getUrl('*/*/message', array('_current' => true)),
            'class' => 'ajax',
        ));
        if ($this->getRequest()->getParam('id')) {
            $this->addTab('relatedorders_section', array(
                'label' => Mage::helper('storepickup')->__('Related Orders'),
                'url' => $this->getUrl('*/*/relatedorders', array('_current' => true, 'id' => $this->getRequest()->getParam('id'))),
                'class' => 'ajax',
            ));
        }
        return parent::_beforeToHtml();
    }

}