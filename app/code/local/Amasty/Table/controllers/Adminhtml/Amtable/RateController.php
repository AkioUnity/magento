<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Table
 */
class Amasty_Table_Adminhtml_Amtable_RateController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction() 
	{
	    $html = $this->getLayout()->createBlock('amtable/adminhtml_rates')->toHtml();
        $this->getResponse()->setBody($html);
	}

    public function exportCsvAction()
    {
        $content    = $this->getLayout()->createBlock('amtable/adminhtml_rates')
            ->getCsvFile();
        $this->_prepareDownloadResponse('rates.csv', $content);  
    }

    public function editAction() 
    {
        $id = (int)$this->getRequest()->getParam('id');
        /** @var Amasty_Table_Model_Rate $model */
        $model = Mage::getModel('amtable/rate')->load($id);
        $mid = (int)$this->getRequest()->getParam('mid');

		if (!$mid && !$model->getId()) {
    		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amtable')->__('Record #%d does not exist', $id));
		    $this->_redirect('adminhtml/amtable_method/index');
			return;
		}   
		
		$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}
        
        if ($mid && !$model->getId()){
            $model->setMethodId($mid);
            $model->setWeightFrom('0');
            $model->setQtyFrom('0');
            $model->setPriceFrom('0');
            $model->setWeightTo($model::MAX_VALUE);
            $model->setQtyTo($model::MAX_VALUE);
            $model->setPriceTo($model::MAX_VALUE);
        }
		
		Mage::register('amtable_rate', $model);

		$this->loadLayout();
		
		$this->_setActiveMenu('sales/amtable');
        $this->_addContent($this->getLayout()->createBlock('amtable/adminhtml_rate_edit'));
        
		$this->renderLayout();
	}  

	public function saveAction() 
	{
	    $id     = $this->getRequest()->getParam('id');
        $mid    = $this->getRequest()->getParam('mid');
	    $model  = Mage::getModel('amtable/rate')->load($id);
	               
	    $data = $this->getRequest()->getPost();
		if (!$data) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('amtable')->__('Unable to find a rate to save'));
            $this->_redirect('adminhtml/amtable_method/index');
            return;
		}
        try {
            $methodId = $model->getMethodId();
            if (!$methodId)
            {
                $methodId = $data['method_id'];    
            }
            $fullZipFrom = Mage::helper('amtable')->getDataFromZip($data['zip_from']);
            $fullZipTo = Mage::helper('amtable')->getDataFromZip($data['zip_to']);
            $data['num_zip_from'] = $fullZipFrom['district'];
            $data['num_zip_to'] = $fullZipTo['district'];
		    $model->setData($data)->setId($id);
			$model->save();
			
			Mage::getSingleton('adminhtml/session')->setFormData(false);
			
			$msg = Mage::helper('amtable')->__('Rate has been successfully saved');
            Mage::getSingleton('adminhtml/session')->addSuccess($msg);

            $this->_redirect('adminhtml/amtable_method/edit', array('id'=> $methodId, 'tab'=>'rates'));
			
        } 
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError('This rate already exist!');
            Mage::getSingleton('adminhtml/session')->setFormData($data);
            $this->_redirect('*/*/edit', array('id' => $id, 'mid'=> $methodId));
        }	
	}

    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        if (!$id) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amtable')->__('Unable to find a rate to delete'));
            $this->_redirect('adminhtml/amtable_method/index');
            return;
        }
        
        try {
            $rate = Mage::getModel('amtable/rate')->load($id);
            $methodId = $rate->getMethodId();
            
            $rate->delete();
            
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('amtable')->__('Rate has been deleted'));
            $this->_redirect('adminhtml/amtable_method/edit', array('id'=>$methodId, 'tab'=>'rates'));
        }
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('adminhtml/amtable_method/index');
        }
    }


    protected function _isAllowed(){
        return Mage::getSingleton('admin/session')->isAllowed('sales/amtable');
    }
	
}
