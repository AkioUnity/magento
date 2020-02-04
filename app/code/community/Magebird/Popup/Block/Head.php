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
 
class Magebird_Popup_Block_Head extends Mage_Core_Block_Template
{

  protected function _construct()
  {
      parent::_construct();
      setcookie('cookiesEnabled', '1', time()+30);
  }
  
	public function getTargetPageId() {      
    return Mage::helper('magebird_popup')->getTargetPageId();
  }
  
  public function getFilterId() {
    return Mage::helper('magebird_popup')->getFilterId();
  }
  
  public function isAjax(){
    return Mage::getStoreConfig('magebird_popup/settings/useajax');
  }

  public function requestType(){
    return Mage::getStoreConfig('magebird_popup/settings/requesttype') == 1 ? 'GET' : 'POST';
  }
  
  public function getPreviewId(){   
    $request = $this->getRequest();
    $module = $request->getModuleName();
    $action = $request->getActionName();           
    if($action!="preview" || $module!="magebird_popup") return '';   
    $popupId = $this->getRequest()->getParam('previewId');        
    return $popupId;
  }
  
  public function getPage(){
    $id = trim(Mage::getStoreConfig('magebird_popup/gen'.'eral/exte'.'nsion_k'.'ey'));
    $model = Mage::getModel('core/config_data'); //use model to prevent cache
    $time = $model->load('magebird_popup/gene'.'ral/tr'.'ial_st'.'art','path')->getData('value');
    if((empty($id) || strlen($id)!=10) && ($time<strtotime('-7 days') || $time>strtotime('+35 days'))){
      return '0';
    }     
    return '1';  
  }
    
  public function getTemplateId(){
    $request = $this->getRequest();
    $module = $request->getModuleName();
    $action = $request->getActionName();           
    if($action!="template" || $module!="magebird_popup") return '';   
    $popupId = $this->getRequest()->getParam('templateId');        
    return $popupId;
  }  
  	
}