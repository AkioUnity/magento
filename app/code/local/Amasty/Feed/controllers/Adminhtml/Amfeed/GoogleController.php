<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */
class Amasty_Feed_Adminhtml_Amfeed_GoogleController extends Amasty_Feed_Controller_Abstract
{
    public function indexAction()
    {
        try{
            $categoryMapperId = $this->getRequest()->getParam(Amasty_Feed_Model_Google::VAR_CATEGORY_MAPPER);
            $identifierExistsId = $this->getRequest()->getParam(Amasty_Feed_Model_Google::VAR_IDENTIFIER_EXISTS);
            $feedId = $this->getRequest()->getParam(Amasty_Feed_Model_Google::VAR_FEED_ID);
            $step = $this->getRequest()->getParam(Amasty_Feed_Model_Google::VAR_STEP, 1);

            $categoryMapper = Mage::getModel('amfeed/category');
            $identifierExists = Mage::getModel('amfeed/field');
            $feed = Mage::getModel('amfeed/profile');

            if ($categoryMapperId){
                $categoryMapper = Mage::getModel('amfeed/category')->load($categoryMapperId);
            }

            if ($identifierExistsId){
                $identifierExists = Mage::getModel('amfeed/field')->load($identifierExistsId);
            }

            if ($feedId){
                $feed = Mage::getModel('amfeed/profile')->load($feedId);
            }

            Mage::register(Amasty_Feed_Model_Google::VAR_CATEGORY_MAPPER, $categoryMapper);
            Mage::register(Amasty_Feed_Model_Google::VAR_IDENTIFIER_EXISTS, $identifierExists);
            Mage::register(Amasty_Feed_Model_Google::VAR_FEED_ID, $feed);
            Mage::register(Amasty_Feed_Model_Google::VAR_STEP, $step);

            $this->loadLayout();

            $this->getLayout()->getBlock('menu')->setActive('catalog/amfeed/profiles');
            $this->_title($this->__('Catalog'))
                 ->_title($this->__('Product Feeds'))
                 ->_title($this->__('Setup Google Feed'));
            $this->_addContent($this->getLayout()->createBlock('amfeed/adminhtml_google'));
            $this->_addLeft($this->getLayout()->createBlock('amfeed/adminhtml_google_edit_tabs'));
            $this->renderLayout();

            return ;

        } catch (Exception $e){
            $this->_getSession()->addError($e->getMessage());

        }

        $this->_redirect('*/*/');
    }

    public function saveAction()
    {
        $args = array();

        try{
            $config = Mage::getModel('amfeed/google')->setup($this->getRequest()->getParams());

            if (array_key_exists(Amasty_Feed_Model_Google::VAR_CATEGORY_MAPPER, $config)){
                $args[Amasty_Feed_Model_Google::VAR_CATEGORY_MAPPER] = $config[Amasty_Feed_Model_Google::VAR_CATEGORY_MAPPER];
            }

            if (array_key_exists(Amasty_Feed_Model_Google::VAR_IDENTIFIER_EXISTS, $config)){
                $args[Amasty_Feed_Model_Google::VAR_IDENTIFIER_EXISTS] = $config[Amasty_Feed_Model_Google::VAR_IDENTIFIER_EXISTS];
            }

            if (array_key_exists(Amasty_Feed_Model_Google::VAR_FEED_ID, $config)){
                $args[Amasty_Feed_Model_Google::VAR_FEED_ID] = $config[Amasty_Feed_Model_Google::VAR_FEED_ID];
            }

            if (array_key_exists(Amasty_Feed_Model_Google::VAR_STEP, $config)){
                $args[Amasty_Feed_Model_Google::VAR_STEP] = $config[Amasty_Feed_Model_Google::VAR_STEP];
            }
        } catch (Exception $e){
            $this->_getSession()->addError($e->getMessage());
            
        }

        if ($this->getRequest()->getParam('setup_complete')){
            $this->_redirect('*/amfeed_profile/edit', array(
                'id' => $args[Amasty_Feed_Model_Google::VAR_FEED_ID],
                'force_generate' => 1
            ));
        } else {
            $this->_redirect('*/*/', $args);
        }
    }
}