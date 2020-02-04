<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */
class Amasty_Feed_Controller_Abstract extends Mage_Adminhtml_Controller_Action
{
    protected $_title     = 'Feed';
    protected $_modelName = 'profile';
    protected $_dynamic   = array();

    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('catalog/amfeed/' . $this->_modelName . 's');
        $this->_addContent($this->getLayout()->createBlock('amfeed/adminhtml_' . $this->_modelName));
        $this->renderLayout();
    }

    protected function _setActiveMenu($menuPath)
    {
        $this->getLayout()->getBlock('menu')->setActive($menuPath);
        $this->_title($this->__('Catalog'))
            ->_title($this->__('Product Feeds'))
            ->_title($this->__($this->_title));

        return $this;
    }

    public function newAction()
    {
        $this->editAction();
    }

    public function editAction()
    {
        $id    = (int)$this->getRequest()->getParam('id');
        $model = Mage::getModel('amfeed/' . $this->_modelName)->load($id);

        if ($id && !$model->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amfeed')->__('Record does not exist'));
            $this->_redirect('*/*/');

            return;
        }

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        } else {
            $this->prepareForEdit($model);
        }

        Mage::register('amfeed_' . $this->_modelName, $model);

        $this->loadLayout();

        $this->_setActiveMenu('catalog/amfeed/' . $this->_modelName . 's');
        $this->_title($this->__('Edit'));

        $this->_addContent($this->getLayout()->createBlock('amfeed/adminhtml_' . $this->_modelName . '_edit'));
        $this->_addLeft($this->getLayout()->createBlock('amfeed/adminhtml_' . $this->_modelName . '_edit_tabs'));

        $this->renderLayout();
    }

    protected function prepareForEdit($model)
    {
        foreach ($this->_dynamic as $field) {
            $model->setData($field, unserialize($model->getData($field)));
        }

        return true;
    }

    public function saveAction()
    {
        $id    = $this->getRequest()->getParam('id');
        $model = Mage::getModel('amfeed/' . $this->_modelName);
        $data  = $this->getRequest()->getPost();

        if ($data) {
            $model->setData($data)->setId($id);
            try {
                $this->prepareForSave($model);

                $model->save();

                $this->_afterSave($model);

                Mage::getSingleton('adminhtml/session')->setFormData(false);

                $msg = Mage::helper('amfeed')->__($this->_title . ' has been successfully saved');
                Mage::getSingleton('adminhtml/session')->addSuccess($msg);

                if ($this->getRequest()->getParam('continue')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId(), '_current' => true));

                    return;
                }

                $this->_redirect('*/*');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $id));
            }

            return;
        }

        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('amfeed')
                ->__('Unable to find a record to save')
        );
        $this->_redirect('*/*');
    }

    protected function prepareForSave($model)
    {
        foreach ($this->_dynamic as $field) {
            $map = $model->getData($field);
            if (!$map) {
                $map = array();
            }
            $model->setData($field, serialize($map));
        }

        return true;
    }

    protected function _afterSave($model)
    {

    }

    public function deleteAction()
    {
        $id    = (int)$this->getRequest()->getParam('id');
        $model = Mage::getModel('amfeed/' . $this->_modelName)->load($id);

        if ($id && !$model->getId()) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Record does not exist'));
            $this->_redirect('*/*/');

            return;
        }

        try {
            $model->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess(
                $this->__($this->_title . ' has been successfully deleted')
            );
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/*/');
    }

    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam($this->_modelName . 's');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amfeed')->__('Please select records'));
            $this->_redirect('*/*/');

            return;
        }

        try {
            foreach ($ids as $id) {
                $model = Mage::getModel('amfeed/' . $this->_modelName)->load($id);
                $model->delete();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('adminhtml')->__(
                    'Total of %d record(s) were successfully deleted', count($ids)
                )
            );
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/*/');

    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/amfeed');


    }
}
