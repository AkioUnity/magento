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
 * Category admin controller
 *
 * @category    Xcentia
 * @package     Xcentia_Coster
 * @author      Ultimate Module Creator
 */
class Xcentia_Coster_Adminhtml_Coster_CategoryController extends Xcentia_Coster_Controller_Adminhtml_Coster
{
    /**
     * init the category
     *
     * @access protected
     * @return Xcentia_Coster_Model_Category
     */
    protected function _initCategory()
    {
        $categoryId  = (int) $this->getRequest()->getParam('id');
        $category    = Mage::getModel('xcentia_coster/category');
        if ($categoryId) {
            $category->load($categoryId);
        }
        Mage::register('current_category', $category);
        return $category;
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
             ->_title(Mage::helper('xcentia_coster')->__('Categories'));
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
     * edit category - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function editAction()
    {
        $categoryId    = $this->getRequest()->getParam('id');
        $category      = $this->_initCategory();
        if ($categoryId && !$category->getId()) {
            $this->_getSession()->addError(
                Mage::helper('xcentia_coster')->__('This category no longer exists.')
            );
            $this->_redirect('*/*/');
            return;
        }
        $data = Mage::getSingleton('adminhtml/session')->getCategoryData(true);
        if (!empty($data)) {
            $category->setData($data);
        }
        Mage::register('category_data', $category);
        $this->loadLayout();
        $this->_title(Mage::helper('xcentia_coster')->__('Coster'))
             ->_title(Mage::helper('xcentia_coster')->__('Categories'));
        if ($category->getId()) {
            $this->_title($category->getPiececode());
        } else {
            $this->_title(Mage::helper('xcentia_coster')->__('Add category'));
        }
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        $this->renderLayout();
    }

    /**
     * new category action
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
     * save category - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost('category')) {
            try {
                $category = $this->_initCategory();
                $category->addData($data);
                $category->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('xcentia_coster')->__('Category was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $category->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setCategoryData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('xcentia_coster')->__('There was a problem saving the category.')
                );
                Mage::getSingleton('adminhtml/session')->setCategoryData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('xcentia_coster')->__('Unable to find category to save.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * delete category - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function deleteAction()
    {
        if ( $this->getRequest()->getParam('id') > 0) {
            try {
                $category = Mage::getModel('xcentia_coster/category');
                $category->setId($this->getRequest()->getParam('id'))->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('xcentia_coster')->__('Category was successfully deleted.')
                );
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('xcentia_coster')->__('There was an error deleting category.')
                );
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                Mage::logException($e);
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('xcentia_coster')->__('Could not find category to delete.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * mass delete category - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function massDeleteAction()
    {
        $categoryIds = $this->getRequest()->getParam('category');
        if (!is_array($categoryIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('xcentia_coster')->__('Please select categories to delete.')
            );
        } else {
            try {
                foreach ($categoryIds as $categoryId) {
                    $category = Mage::getModel('xcentia_coster/category');
                    $category->setId($categoryId)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('xcentia_coster')->__('Total of %d categories were successfully deleted.', count($categoryIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('xcentia_coster')->__('There was an error deleting categories.')
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
        $categoryIds = $this->getRequest()->getParam('category');
        if (!is_array($categoryIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('xcentia_coster')->__('Please select categories.')
            );
        } else {
            try {
                foreach ($categoryIds as $categoryId) {
                $category = Mage::getSingleton('xcentia_coster/category')->load($categoryId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d categories were successfully updated.', count($categoryIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('xcentia_coster')->__('There was an error updating categories.')
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
        $fileName   = 'category.csv';
        $content    = $this->getLayout()->createBlock('xcentia_coster/adminhtml_category_grid')
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
        $fileName   = 'category.xls';
        $content    = $this->getLayout()->createBlock('xcentia_coster/adminhtml_category_grid')
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
        $fileName   = 'category.xml';
        $content    = $this->getLayout()->createBlock('xcentia_coster/adminhtml_category_grid')
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
        return Mage::getSingleton('admin/session')->isAllowed('xcentia_coster/category');
    }
}
