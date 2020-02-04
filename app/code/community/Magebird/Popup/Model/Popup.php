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
class Magebird_Popup_Model_Popup extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('magebird_popup/popup');  
    }
    
    public function setPopupData($id,$field,$value){
      $id = intval($id);
      $tableName = Mage::getSingleton("core/resource")->getTableName('magebird_popup');
      $write = Mage::getSingleton('core/resource')->getConnection('core_write');
      $query = "UPDATE `{$tableName}` SET `$field`=:value WHERE popup_id=$id";       
      $binds = array('value' => $value);
      $write->query($query, $binds);    
    }
      

    public function getPictureResize($width, $height = null, $img = null) {
  		// actual path of image 
      if($img){
        $img = str_replace("popup/","",$img);
      }else{
        $img = str_replace("popup/","",$this->getData("image"));
      }
            
  		$imageUrl = Mage::getBaseDir('media'). DS. 'popup' .DS.$img;
  		
  		// path of the resized image to be saved
  		// here, the resized image is saved in media/resized folder
  		$imageResized = Mage::getBaseDir('media'). DS. 'popup' .DS."resized_".$width.$img; 
  		
  		// resize image only if the image file exists and the resized image file doesn't exist
  		// the image is resized proportionally with the width/height 135px
  		if (!file_exists($imageResized)&&file_exists($imageUrl)) {
  			$imageObj = new Varien_Image($imageUrl);
  			$imageObj->constrainOnly(TRUE);
  			$imageObj->keepAspectRatio(TRUE);
  			$imageObj->keepFrame(FALSE);
  			$imageObj->resize($width, $height);
  			$imageObj->save($imageResized);
  		}
  		return (Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . "media/popup/" . "resized_".$width.$img);
    }      
    
    public function checkIfPageRefreshed($lastPageviewId){
      $lastPageviewId = substr($lastPageviewId,0,10);
      $tableName = Mage::getSingleton("core/resource")->getTableName('magebird_popup');
      $write = Mage::getSingleton('core/resource')->getConnection('core_write');
      $query = "UPDATE `{$tableName}` SET `page_reloaded`=`page_reloaded`+1,`window_closed`=`window_closed`-1 WHERE last_rand_id=:lastPageviewId";
      $binds = array('lastPageviewId' => $lastPageviewId);
      $write->query($query, $binds);      
    }   
    
    /*
    Required because we query popups directly from sql, so we need to parse widgets 
    first with cms template processor
    */
    public function parsePopupContent($popupId=null){   
        if (version_compare(Mage::getVersion(), '1.5', '<')) return;    
        $write = Mage::getSingleton('core/resource')->getConnection('core_write'); 
        $_stores = Mage::getModel('core/store')->getCollection();
        $_popups = Mage::getModel('magebird_popup/popup')->getCollection();
        if($popupId){                    
          $_popups->addFieldToFilter('popup_id',$popupId);                     
        }         
                
        foreach($_stores as $_store){   
          $storeId = $_store->getData('store_id');
          $appEmulation = Mage::getSingleton('core/app_emulation');
          $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId); 

          foreach($_popups as $_popup){    
                  
            $content = $_popup->getData('popup_content');    
            $parsed = Mage::helper('cms')->getBlockTemplateProcessor()->filter($content); 
				
            $query = "INSERT INTO ".Mage::getSingleton('core/resource')->getTableName('magebird_popup_content')." (popup_id,store_id,content,is_template) 
                      VALUES (".$_popup->getData('popup_id').",$storeId,:value,0) ON DUPLICATE KEY UPDATE content = VALUES(content)";            
            $bind = array('value'=>$parsed);
                        
            $write->query($query, $bind);  
             
          }
        
          $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);   
        }
    }   
    
    public function parsePopupTemplateContent(){
        if (version_compare(Mage::getVersion(), '1.5', '<')) return;    
        $write = Mage::getSingleton('core/resource')->getConnection('core_write'); 
        $_stores = Mage::getModel('core/store')->getCollection();
        $collection = Mage::getModel('magebird_popup/template')->getCollection();        
        foreach($_stores as $_store){
          $storeId = $_store->getData('store_id');
          $appEmulation = Mage::getSingleton('core/app_emulation');
          $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);                   
          foreach($collection as $_popup){            
            $content = $_popup->getData('popup_content');          
            $parsed = Mage::helper('cms')->getBlockTemplateProcessor()->filter($content);
            
            $query = "INSERT INTO ".Mage::getSingleton('core/resource')->getTableName('magebird_popup_content')." (popup_id,store_id,content,is_template) 
                      VALUES (".$_popup->getData('template_id').",$storeId,:value,1) ON DUPLICATE KEY UPDATE content = VALUES(content)";          
  
            $bind = array('value'=>$parsed);
            $write->query($query, $bind);  
          }
          $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);   
        }
    } 
    
    public function addNewView(){    
      if(!Mage::helper('magebird_popup')->getPopupCookie('newVisit')){
        Mage::helper('magebird_popup')->setPopupCookie('newVisit',1,time()+(3600*48));
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $query = "UPDATE ".Mage::getSingleton('core/resource')->getTableName('magebird_popup_stats')." SET visitors=visitors+1"; 
        $write->query($query);     
      }
    } 
    
    public function uniqueViewStats($popupId){ 
      $popupId = intval($popupId);
      $lastPopups = Mage::helper('magebird_popup')->getPopupCookie('lastPopups');
      $idExists = false;
      $explode = explode(",",$lastPopups);
      foreach($explode as $popupId2){
        if($popupId2 == $popupId) $idExists = true;       
      }                  
      //make sure cookies are read. In some cases if ajax call is closed too fast cookies are not read.
      if(!$idExists && Mage::helper('magebird_popup')->getPopupCookie('magentoSessionId')){
        Mage::helper('magebird_popup')->setPopupCookie('lastPopups',$lastPopups.",".$popupId,time()+(3600*48));
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        if(Mage::helper('magebird_popup')->getPopupCookie('cartProductIds')){
          $query = "UPDATE ".Mage::getSingleton('core/resource')->getTableName('magebird_popup_stats')." 
                    SET popup_visitors=popup_visitors+1,popup_carts=popup_carts+1 WHERE popup_id=".$popupId;         
        }else{
          $query = "UPDATE ".Mage::getSingleton('core/resource')->getTableName('magebird_popup_stats')." 
                    SET popup_visitors=popup_visitors+1 WHERE popup_id=".$popupId;         
        }
        $write->query($query);     
      }        
    }     
                      
}