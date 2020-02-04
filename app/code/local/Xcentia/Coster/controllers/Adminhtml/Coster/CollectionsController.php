<?php
/**
 * Xcentia_Coster extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       Xcentia
 * @package        Xcentia_Coster
 * @copyright      Copyright (c) 2017
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Collection admin controller
 *
 * @category    Xcentia
 * @package     Xcentia_Coster
 * @author      Ultimate Module Creator
 */
class Xcentia_Coster_Adminhtml_Coster_CollectionsController extends Xcentia_Coster_Controller_Adminhtml_Coster
{
    /**
     * init the collection
     *
     * @access protected
     * @return Xcentia_Coster_Model_Collections
     */
    protected function _initCollections()
    {
        $collectionsId  = (int) $this->getRequest()->getParam('id');
        $collections    = Mage::getModel('xcentia_coster/collections');
        if ($collectionsId) {
            $collections->load($collectionsId);
        }
        Mage::register('current_collections', $collections);
        return $collections;
    }

    /**
     * default action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_title(Mage::helper('xcentia_coster')->__('Coster'))
             ->_title(Mage::helper('xcentia_coster')->__('Collections'));
        $this->renderLayout();
    }

    /**
     * grid action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function gridAction()
    {
        $this->loadLayout()->renderLayout();
    }

    /**
     * edit collection - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function editAction()
    {
        $collectionsId    = $this->getRequest()->getParam('id');
        $collections      = $this->_initCollections();
        if ($collectionsId && !$collections->getId()) {
            $this->_getSession()->addError(
                Mage::helper('xcentia_coster')->__('This collection no longer exists.')
            );
            $this->_redirect('*/*/');
            return;
        }
        $data = Mage::getSingleton('adminhtml/session')->getCollectionsData(true);
        if (!empty($data)) {
            $collections->setData($data);
        }
        Mage::register('collections_data', $collections);
        $this->loadLayout();
        $this->_title(Mage::helper('xcentia_coster')->__('Coster'))
             ->_title(Mage::helper('xcentia_coster')->__('Collections'));
        if ($collections->getId()) {
            $this->_title($collections->getCollectionCode());
        } else {
            $this->_title(Mage::helper('xcentia_coster')->__('Add collection'));
        }
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        $this->renderLayout();
    }

    /**
     * new collection action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * save collection - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost('collections')) {
            try {
                $collections = $this->_initCollections();
                $collections->addData($data);
                $collections->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('xcentia_coster')->__('Collection was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $collections->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setCollectionsData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('xcentia_coster')->__('There was a problem saving the collection.')
                );
                Mage::getSingleton('adminhtml/session')->setCollectionsData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('xcentia_coster')->__('Unable to find collection to save.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * delete collection - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function deleteAction()
    {
        if ( $this->getRequest()->getParam('id') > 0) {
            try {
                $collections = Mage::getModel('xcentia_coster/collections');
                $collections->setId($this->getRequest()->getParam('id'))->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('xcentia_coster')->__('Collection was successfully deleted.')
                );
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('xcentia_coster')->__('There was an error deleting collection.')
                );
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                Mage::logException($e);
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('xcentia_coster')->__('Could not find collection to delete.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * mass delete collection - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function massDeleteAction()
    {
        $collectionsIds = $this->getRequest()->getParam('collections');
        if (!is_array($collectionsIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('xcentia_coster')->__('Please select collections to delete.')
            );
        } else {
            try {
                foreach ($collectionsIds as $collectionsId) {
                    $collections = Mage::getModel('xcentia_coster/collections');
                    $collections->setId($collectionsId)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('xcentia_coster')->__('Total of %d collections were successfully deleted.', count($collectionsIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('xcentia_coster')->__('There was an error deleting collections.')
                );
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * mass status change - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function massStatusAction()
    {
        $collectionsIds = $this->getRequest()->getParam('collections');
        if (!is_array($collectionsIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('xcentia_coster')->__('Please select collections.')
            );
        } else {
            try {
                foreach ($collectionsIds as $collectionsId) {
                $collections = Mage::getSingleton('xcentia_coster/collections')->load($collectionsId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d collections were successfully updated.', count($collectionsIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('xcentia_coster')->__('There was an error updating collections.')
                );
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * export as csv - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function exportCsvAction()
    {
        $fileName   = 'collections.csv';
        $content    = $this->getLayout()->createBlock('xcentia_coster/adminhtml_collections_grid')
            ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export as MsExcel - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function exportExcelAction()
    {
        $fileName   = 'collections.xls';
        $content    = $this->getLayout()->createBlock('xcentia_coster/adminhtml_collections_grid')
            ->getExcelFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export as xml - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function exportXmlAction()
    {
        $fileName   = 'collections.xml';
        $content    = $this->getLayout()->createBlock('xcentia_coster/adminhtml_collections_grid')
            ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Check if admin has permissions to visit related pages
     *
     * @access protected
     * @return boolean
     * @author Ultimate Module Creator
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('xcentia_coster/collections');
    }
}
