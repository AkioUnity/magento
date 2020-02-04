<?php

class Magebird_Popup_Block_PopupCustomize extends Mage_Core_Block_Template
{
	public function getPopupCustomize($collection) {       
          
    /* 
    //Example how you can limit collection by field value
    $collection->addFieldToFilter('your_custom_field1',array('in' => array('value one','value two','...')));
    */
    
    /*
    //example how to prevent popups from showing for multiple url-s
    if(Mage::app()->getRequest()->getParam('url')){
      $url = Mage::app()->getRequest()->getParam('url');
    }else{
      $requestUri = Mage::app()->getRequest()->getOriginalRequest()->getRequestUri();
      $url = $_SERVER['HTTP_HOST'].$requestUri;        
    }
    
    if(strpos($url, "gclid")!==false || strpos($url, "remarkety")!==false){
      return array();
    }
    */
    
    /*
    //example how to remove from collection only specific popup based on url 
    foreach($collection as $key => $_popup){ 
      if(Mage::app()->getRequest()->getParam('url')){
        $url = Mage::app()->getRequest()->getParam('url');
      }else{
        $requestUri = Mage::app()->getRequest()->getOriginalRequest()->getRequestUri();
        $url = $_SERVER['HTTP_HOST'].$requestUri;        
      }
      
      if(strpos($url, "gclid")!==false || strpos($url, "remarkety")!==false){
        return array();
      }
      if($_popup->getData('popup_id')==4 || $_popup->getData('popup_id')==20){
          $collection->removeItemByKey($key);
          continue;               
      }                                      
    } 
    */ 
    
    /*
    $productId = null;
    if(Mage::registry('current_product')){
      $productId = Mage::registry('current_product')->getId();
    }elseif(Mage::app()->getRequest()->getParam('url')){ //if popup loaded with ajax
      $url = urldecode(Mage::app()->getRequest()->getParam('url'));*/
      //if(strpos($url,'catalog/product')!==false && preg_match('/(\d+)[^\d]*/', $url, $match)){
     /*
        $productId = $match[1];
      }else{
        $tokens = explode('/', $url);
        $vPath = $tokens[sizeof($tokens)-1];
        $oRewrite = Mage::getModel('core/url_rewrite')
                        ->setStoreId(Mage::app()->getStore()->getId())
                        ->loadByRequestPath($vPath);              
        $productId = $oRewrite->getProductId();    
      }
    }
    if($productId){
      $_product = Mage::getModel('catalog/product')->load($productId);
      $popupId = 1; //change with your popup id
      $attributeCode = 'your_product_attribute_code';
      $attributeValue = 'required_attribute_value';
      if($_popup->getData('popup_id')==$popupId && $_product->getData($attributeCode)!=$attributeValue){
          $collection->removeItemByKey($key);
          continue;               
      }       
    }     
    */
    return $collection;
          
	}    
}