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
 * Style admin controller
 *
 * @category    Xcentia
 * @package     Xcentia_Coster
 * @author      Ultimate Module Creator
 */
class Xcentia_Coster_Adminhtml_Coster_StyleController extends Xcentia_Coster_Controller_Adminhtml_Coster
{
    /**
     * init the style
     *
     * @access protected
     * @return Xcentia_Coster_Model_Style
     */
    protected function _initStyle()
    {
        $styleId  = (int) $this->getRequest()->getParam('id');
        $style    = Mage::getModel('xcentia_coster/style');
        if ($styleId) {
            $style->load($styleId);
        }
        Mage::register('current_style', $style);
        return $style;
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
             ->_title(Mage::helper('xcentia_coster')->__('Styles'));
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
     * edit style - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function editAction()
    {
        $styleId    = $this->getRequest()->getParam('id');
        $style      = $this->_initStyle();
        if ($styleId && !$style->getId()) {
            $this->_getSession()->addError(
                Mage::helper('xcentia_coster')->__('This style no longer exists.')
            );
            $this->_redirect('*/*/');
            return;
        }
        $data = Mage::getSingleton('adminhtml/session')->getStyleData(true);
        if (!empty($data)) {
            $style->setData($data);
        }
        Mage::register('style_data', $style);
        $this->loadLayout();
        $this->_title(Mage::helper('xcentia_coster')->__('Coster'))
             ->_title(Mage::helper('xcentia_coster')->__('Styles'));
        if ($style->getId()) {
            $this->_title($style->getStyleCode());
        } else {
            $this->_title(Mage::helper('xcentia_coster')->__('Add style'));
        }
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        $this->renderLayout();
    }

    /**
     * new style action
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
     * save style - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost('style')) {
            try {
                $style = $this->_initStyle();
                $style->addData($data);
                $style->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('xcentia_coster')->__('Style was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $style->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setStyleData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('xcentia_coster')->__('There was a problem saving the style.')
                );
                Mage::getSingleton('adminhtml/session')->setStyleData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('xcentia_coster')->__('Unable to find style to save.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * delete style - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function deleteAction()
    {
        if ( $this->getRequest()->getParam('id') > 0) {
            try {
                $style = Mage::getModel('xcentia_coster/style');
                $style->setId($this->getRequest()->getParam('id'))->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('xcentia_coster')->__('Style was successfully deleted.')
                );
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('xcentia_coster')->__('There was an error deleting style.')
                );
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                Mage::logException($e);
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('xcentia_coster')->__('Could not find style to delete.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * mass delete style - action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function massDeleteAction()
    {
        $styleIds = $this->getRequest()->getParam('style');
        if (!is_array($styleIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('xcentia_coster')->__('Please select styles to delete.')
            );
        } else {
            try {
                foreach ($styleIds as $styleId) {
                    $style = Mage::getModel('xcentia_coster/style');
                    $style->setId($styleId)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('xcentia_coster')->__('Total of %d styles were successfully deleted.', count($styleIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('xcentia_coster')->__('There was an error deleting styles.')
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
        $styleIds = $this->getRequest()->getParam('style');
        if (!is_array($styleIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('xcentia_coster')->__('Please select styles.')
            );
        } else {
            try {
                foreach ($styleIds as $styleId) {
                $style = Mage::getSingleton('xcentia_coster/style')->load($styleId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d styles were successfully updated.', count($styleIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('xcentia_coster')->__('There was an error updating styles.')
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
        $fileName   = 'style.csv';
        $content    = $this->getLayout()->createBlock('xcentia_coster/adminhtml_style_grid')
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
        $fileName   = 'style.xls';
        $content    = $this->getLayout()->createBlock('xcentia_coster/adminhtml_style_grid')
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
        $fileName   = 'style.xml';
        $content    = $this->getLayout()->createBlock('xcentia_coster/adminhtml_style_grid')
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
        return Mage::getSingleton('admin/session')->isAllowed('xcentia_coster/style');
    }
}
