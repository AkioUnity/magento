<?php
 
class Srdt_Slider_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
                  $this->loadLayout();
       $this->_setActiveMenu('srdt');
       $this->_addContent($this->getLayout()->createBlock('srdt_slider/adminhtml_slider'));
        $this->renderLayout();
      
    }
 
    public function gridAction()
    {

        $this->loadLayout(); 
        $this->getResponse()->setBody($this->getLayout()->createBlock('srdt_slider/adminhtml_enquiry_grid')->toHtml()
        );
    }
 
    public function exportInchooCsvAction()
    {
        $fileName = 'srdt_slider.csv';
        $grid = $this->getLayout()->createBlock('srdt_slider/adminhtml_enquiry_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }
 
    public function exportInchooExcelAction()
    {
        $fileName = 'srdt_slider.xml';
        $grid = $this->getLayout()->createBlock('srdt_slider/adminhtml_enquiry_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }


  public function massDeleteAction() {
    $enquiry_ids = $this->getRequest()->getParam('enquiry');
    if (!is_array($enquiry_ids)) {
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
    } else {
        try {
            foreach ($enquiry_ids as $enquiry_id) {
                $enquiry_del = Mage::getModel('slider/slider')->load($enquiry_id);
                $enquiry_del->delete();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted', count($enquiry_ids)));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }
    $this->_redirect('*/*/index', array('store' => $this->getRequest()->getParam("store")));
}
  public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('slider/slider')->load($id);
        if ($model->getBannerId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data))
                $model->setData($data);

            Mage::register('banner_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('srdt');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Enquiry Manager'), Mage::helper('adminhtml')->__('Banner Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('srdt_slider/adminhtml_slider_edit'))
                 ->_addLeft($this->getLayout()->createBlock('srdt_slider/adminhtml_slider_edit_tabs'));

            $this->renderLayout();

        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('srdt_slider')->__('Enquiry does not exist'));
            $this->_redirect('*/*/');
        }
    }
 /**
     * save item action
     */
  /**
     * save item action
     */
    public function saveAction() {        
    if ($data = $this->getRequest ()->getPost ()) {       
            if ($data ['banner_type'] == 2 && isset ( $_FILES ['banner_image'] ['name'] ) and (file_exists ( $_FILES ['banner_image'] ['tmp_name'] ))) {
                try {
                    $uploader = new Varien_File_Uploader ( 'banner_image' );
                    $uploader->setAllowedExtensions ( array (
                            'jpg',
                            'jpeg',
                            'gif',
                            'png' 
                    ) ); // or pdf or anything
                    
                    $uploader->setAllowRenameFiles ( true );
                    
                    // setAllowRenameFiles(true) -> move your file in a folder the magento way
                    // setAllowRenameFiles(true) -> move your file directly in the $path folder
                    $uploader->setFilesDispersion ( false );
                    
                    $path = Mage::getBaseDir ( 'media' ) . DS."bannerslider";
                    
                    $uploader->save ( $path, $_FILES ['banner_image'] ['name'] );
                    
                    $data ['banner_image'] = $_FILES ['banner_image'] ['name'];
                } catch ( Exception $e ) {
                }
            } else {
                
                if (isset ( $data ['banner_image'] ['delete'] ) && $data ['banner_image'] ['delete'] == 1)
                    $data ['image_main'] = '';
                else
                    unset ( $data ['banner_image'] );
            }
            if(isset ( $data ['banner_type']) && $data ['banner_type'] == 1)
            {
               $data ['banner_caption'] ='';
               $data ['banner_image'] ='';
               $data ['banner_url'] ='';

            }
            else
            {
                $data ['youtube_url'] ='';
            }


            $model = Mage::getModel ( 'slider/slider' );
            $model->setData ( $data )->setId ( $this->getRequest ()->getParam ( 'id' ) );
            // Zend_debug::dump($model->getData());die();
            try {
                
                $model->save ();
                
                // Zend_debug::dump($selectBanner->getData());die();
                
                Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'srdt_slider' )->__ ( 'Banner was successfully Saved' ) );
                Mage::getSingleton ( 'adminhtml/session' )->setFormData ( false );
                
                if ($this->getRequest ()->getParam ( 'back' )) {
                    $this->_redirect ( '*/*/edit', array (
                            'id' => $model->getId () 
                    ) );
                    return;
                }
                $this->_redirect ( '*/*/' );
                return;
            } catch ( Exception $e ) {
                Mage::getSingleton ( 'adminhtml/session' )->addError ( $e->getMessage () );
                Mage::getSingleton ( 'adminhtml/session' )->setFormData ( $data );
                $this->_redirect ( '*/*/edit', array (
                        'id' => $this->getRequest ()->getParam ( 'id' ) 
                ) );
                return;
            }
        }
        Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'srdt_slider' )->__ ( 'Unable to find Banner to save' ) );
        $this->_redirect ( '*/*/' );
    }


    /**
     * delete item action
    */
    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('slider/slider');
                $model->setId($this->getRequest()->getParam('id'))
                        ->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Enquiry was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function newAction() {
        $this->_forward('edit');
    }
}