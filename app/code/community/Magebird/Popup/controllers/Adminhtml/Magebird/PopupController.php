<?php

/**
 * Magebird.com
 *
 * @category   Magebird
 * @package    Magebird_Popup
 * @copyright  Copyright (c) 2018 Magebird (http://www.Magebird.com)
 * @license    http://www.magebird.com/licence
 * Any form of ditribution, sell, transfer forbidden see licence above 
 */
 
class Magebird_Popup_Adminhtml_Magebird_PopupController extends Mage_Adminhtml_Controller_Action
{  

  protected function _isAllowed()
  {
      return Mage::getSingleton('admin/session')->isAllowed('cms/magebird_popup');
  }      

	protected function _initAction(){
		$this->loadLayout()
			->_setActiveMenu('popup')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Popups Manager'), Mage::helper('adminhtml')->__('Popups Manager'));
		
		return $this;
	}   
  
  public function indexAction()
  {
		$this->_initAction();       
		$this->_addContent($this->getLayout()->createBlock('magebird_popup/adminhtml_popup'));
		$this->getLayout()->getBlock('head')->setTitle($this->__('Manage Popups'));
		$this->renderLayout();                                                     
  }
    
	public function editAction() {  
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('magebird_popup/popup')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}
      
      if($id==0){
        $randString = substr(md5(time()),0,6);
        $model->setCookieId($randString);
        $model->setWidth(400);
        $model->setMaxWidth(400);
        $model->setCookieTime(10);
        $model->setMaxCountTime(10);
        $model->setDelaytime(0);
        $model->setBorderRadius(6);
        $model->setPopupBackground('#FFFFFF');
        $model->setPadding(10);
        $model->setScrollPx(50);
        $model->setVerticalPositionPx(100);
        $model->setHorizontalPositionPx(100);
        $model->setCloseOnOverlayclick(1);
        $model->setPriority(1);      
      }

			Mage::register('popup_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('popup/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
			
			if($model->getTitle()){
				$this->getLayout()->getBlock('head')->setTitle($this->__('%s / Popup Item', $model->getTitle()));
			}
			else{
				$this->getLayout()->getBlock('head')->setTitle($this->__('New Popup Item'));
			}

			$this->_addContent($this->getLayout()->createBlock('magebird_popup/adminhtml_popup_edit'))
				->_addLeft($this->getLayout()->createBlock('magebird_popup/adminhtml_popup_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('magebird_popup')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
  
	public function duplicateAction() {
    function renameWidgetId($matches){
      $rand = substr( md5(rand()), 0, 2); 
      return $matches[0].$rand;
    }
      
		$id     = $this->getRequest()->getParam('copyid');
		$origin  = Mage::getModel('magebird_popup/popup')->load($id);
    $duplicate  = $origin;
    $duplicate->setData('title',$duplicate->getData('title').' copy');
    $content = $duplicate->getData('popup_content');   
    //because widget_id must be unique
    $content = preg_replace_callback('/widget_id="_/',"renameWidgetId",$content);
    $duplicate->setData('popup_content',$content);
    $randString = substr(md5(time()),0,6);
    $duplicate->setData('cookie_id',$randString);    
    
    $duplicate->setData('popup_id',0);
		Mage::register('popup_data', $duplicate);

		$this->loadLayout();
		$this->_setActiveMenu('popup/items');

		$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

		$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
		$this->getLayout()->getBlock('head')->setTitle($this->__('New Popup Item'));

		$this->_addContent($this->getLayout()->createBlock('magebird_popup/adminhtml_popup_edit'))
			->_addLeft($this->getLayout()->createBlock('magebird_popup/adminhtml_popup_edit_tabs'));

		$this->renderLayout();

	} 
  
	public function copyAction() {
		$id     = $this->getRequest()->getParam('copyid');
		$origin  = Mage::getModel('magebird_popup/template')->load($id);
    if($origin->getTemplateType()==2 && !Mage::helper('magebird_popup')->addOnActivated('premium_templates')){
      $this->_redirect('*/*/');
    }else{
      $duplicate  = $origin;
      $duplicate->setData('title',$duplicate->getData('title'));
      $duplicate->setData('popup_id',0);
      $randString = substr(md5(time()),0,6);
      $duplicate->setCookieId($randString);
      $duplicate->setCookieTime(10);
      $duplicate->setMaxCountTime(10);
      $duplicate->setDelaytime(0);
      $duplicate->setPriority(1);    
  		Mage::register('popup_data', $duplicate);    
    }


		$this->loadLayout();
		$this->_setActiveMenu('popup/items');

		$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

		$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
		$this->getLayout()->getBlock('head')->setTitle($this->__('New Popup Item'));

		$this->_addContent($this->getLayout()->createBlock('magebird_popup/adminhtml_popup_edit'))
			->_addLeft($this->getLayout()->createBlock('magebird_popup/adminhtml_popup_edit_tabs'));

		$this->renderLayout();

	}    
 
	public function newAction() {  
		$this->_forward('edit');
	}
  
	public function templateAction() {  
			$this->loadLayout();
			$this->_setActiveMenu('popup/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
			$this->getLayout()->getBlock('head')->setTitle($this->__('New Popup Item'));

			$this->_addContent($this->getLayout()->createBlock('magebird_popup/adminhtml_popup_template'));

			$this->renderLayout();
	}  
 
	public function saveAction() {
        $this->loadLayout(); // do this first
        $this->getLayout()->getBlock('head')->setTitle($this->__('Popup')); // then this works   
        if ($data = $this->getRequest()->getPost()){        
            $data = $this->_filterDateTime($data, array('from_date', 'to_date'));
            $model = Mage::getModel('magebird_popup/popup');
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
            }                              

            if(isset($_FILES['image']['name']) && $_FILES['image']['name'] != '') {      				
              try {	
      					/* Starting upload */	
      					$uploader = new Varien_File_Uploader('image');
      					// Any extention would work
      	        $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
      					$uploader->setAllowRenameFiles(false);
      					$uploader->setFilesDispersion(false);
      					// We set media as the upload dir
      					$path = Mage::getBaseDir('media') . DS. 'popup' . DS; 
                $uploader->setAllowCreateFolders(true);
      					$uploader->save($path, $_FILES['image']['name'] );
                $uploadedFile = $uploader->getUploadedFileName();
      				} catch (Exception $e) {
                $uploadedFile = null;
      	        $this->_getSession()->addException($e, Mage::helper('magebird_popup')->__('Error uploading image. Please try again later.'));
  		        }
      	  		$data['image'] = "popup/".$uploadedFile;     	
      			}else{
      				if(isset($data['image']['delete']) && $data['image']['delete'] == 1){
                $data["image"]="";
              //if id then image is already stored inside $data['image']
              }elseif(!$id && $data["popup_type"]==1){
                if(!$data['image']['value']){
                  Mage::getSingleton('adminhtml/session')->addError(Mage::helper('magebird_popup')->__('Please choose your image or select "Custom Content" in "Popup Content Type" field.'));
                }              
                $data['image'] = $data['image']['value'];
              }else{
                unset($data["image"]);
              }      					
      			}  
                         		                
      
            $model->setData($data);
     
            Mage::getModel('magebird_popup/popup')->setFormData($data);
            try {
                if ($id) {
                    $model->setId($id);
                }
                $model->save();

                if (!$model->getId()) {
                    Mage::throwException(Mage::helper('magebird_popup')->__('Error saving popup'));
                }
                
                Mage::getModel('magebird_popup/popup')->parsePopupContent($model->getId());

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('magebird_popup')->__('Popup was successfully saved.'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);
 
                // The following line decides if it is a "save" or "save and continue"
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
 
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                if ($model && $model->getId()) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            }
 
            return;
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('magebird_popup')->__('No data found to save'));
        $this->_redirect('*/*/');
	}
  
  public function clearcacheAction(){
        $this->loadLayout(); // do this first
        $this->getLayout()->getBlock('head')->setTitle($this->__('Popup')); // then this works   
        Mage::getModel('magebird_popup/popup')->parsePopupContent(); 
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('magebird_popup')->__('Popup template cache has been successfully cleared.'));
        $this->_redirect('*/*/');  
  }
 
	public function deleteAction() {    
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('magebird_popup/popup');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $popupIds = $this->getRequest()->getParam('popup');
        if(!is_array($popupIds)) {
			   Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($popupIds as $popupId) {
                    $popup = Mage::getModel('magebird_popup/popup')->load($popupId);
                    $popup->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($popupIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    
    public function massResetAction() {
        $popupIds = $this->getRequest()->getParam('popup');
        if(!is_array($popupIds)) {
			   Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                $write = Mage::getSingleton('core/resource')->getConnection('core_write');
                foreach ($popupIds as $popupId) {
                    $popup = Mage::getModel('magebird_popup/popup')->load($popupId);
                    $popup->setViews(0);
                    $popup->setPopupClosed(0);
                    $popup->setClickInside(0);
                    $popup->setWindowClosed(0);
                    $popup->setTotalTime(0);
                    $popup->setPageReloaded(0);
                    $popup->setGoalComplition(0);
                    $popup->save();                                             
                    $query = "UPDATE ".Mage::getSingleton('core/resource')->getTableName('magebird_popup_stats')." 
                            SET visitors=0,total_carts=0,popup_carts=0,purchases=0,popup_visitors=0,popup_purchases=0 
                            WHERE popup_id=".intval($popupId); 
                    $write->query($query); 
                    $query = "DELETE FROM ".Mage::getSingleton('core/resource')->getTableName('magebird_popup_orders')." 
                            WHERE popup_id=".intval($popupId); 
                    $write->query($query);                      
                            
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully reset', count($popupIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }    
	
    public function massStatusAction()
    {
        $popupIds = $this->getRequest()->getParam('popup');
        if(!is_array($popupIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($popupIds as $popupId) {
                    $popup = Mage::getSingleton('magebird_popup/popup')
                        ->load($popupId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($popupIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        return;
    }   
    
    protected function buttonTemplateAction()
    {
  		$this->_initAction();   			
      $block = $this->getLayout()->createBlock('magebird_popup/adminhtml_template')->setTemplate('magebird/popup/buttons.phtml');          
      $this->getResponse()->setBody($block->toHtml());
    } 
    
    protected function reviewTemplateAction()
    {
  		$this->_initAction();   			
      $block = $this->getLayout()->createBlock('core/template')->setTemplate('magebird/popup/review.phtml');          
      $this->getResponse()->setBody($block->toHtml());
    }      
    
    protected function timerTemplateAction()
    {
  		$this->_initAction();   			
      $block = $this->getLayout()->createBlock('core/template')->setTemplate('magebird/popup/timer.phtml');          
      $this->getResponse()->setBody($block->toHtml());
    }      
    
    protected function newsletterTemplateAction()
    {
  		$this->_initAction();   			
      $block = $this->getLayout()->createBlock('core/template')->setTemplate('magebird/popup/newsletter.phtml');          
      $this->getResponse()->setBody($block->toHtml());
    }      
    
    protected function contactTemplateAction()
    {
  		$this->_initAction();   			
      $block = $this->getLayout()->createBlock('core/template')->setTemplate('magebird/popup/contact.phtml');          
      $this->getResponse()->setBody($block->toHtml());
    }          
    
    protected function registerTemplateAction()
    {
  		$this->_initAction();   			
      $block = $this->getLayout()->createBlock('core/template')->setTemplate('magebird/popup/register.phtml');          
      $this->getResponse()->setBody($block->toHtml());
    }
    
    protected function followTemplateAction()
    {
  		$this->_initAction();   			
      $block = $this->getLayout()->createBlock('core/template')->setTemplate('magebird/popup/follow.phtml');          
      $this->getResponse()->setBody($block->toHtml());
    }   
    
    public function dismissNotificationAction() {
        $id = intval($this->getRequest()->getParam('id'));
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $query = "UPDATE " . Mage::getSingleton('core/resource')->getTableName('magebird_notifications') . " 
                SET dismissed=1 WHERE id=" . intval($id);
        $write->query($query);
    } 
                           
} 