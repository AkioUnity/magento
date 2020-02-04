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
class Magebird_Popup_Helper_Mousetracking extends Mage_Core_Helper_Abstract
{
  public function handleMousetracking(){
      $mousetracking = Mage::app()->getRequest()->getParam('mousetracking');
      $mousetracking = json_decode($mousetracking);  
      $deviceType = $mousetracking->isMobile ? 2 : 1;
      $_mousetracking = Mage::getModel('magebird_popup/mousetracking');
      $_mousetracking->setWindowWidth($mousetracking->width);            
      $_mousetracking->setWindowHeight($mousetracking->height);      
      $_mousetracking->setMousetracking($mousetracking->cursor);
      $_mousetracking->setDevice($deviceType);
      $datetime = Zend_Date::now();
      $datetime->setLocale(Mage::getStoreConfig(
                   Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE))
               ->setTimezone(Mage::getStoreConfig(
                   Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE));
      $date= date("Y-m-d H:i:s",$datetime->get());  
      $_mousetracking->setDateCreated($date);
      $_mousetracking->setUserIp($_SERVER['REMOTE_ADDR']);
      $_mousetracking->save();
      
      $mousetrackingId = $_mousetracking->getId(); 
      $this->deleteOldMousetracking();    
        
 
      $mousetrackingPopups = Mage::app()->getRequest()->getParam('mousetrackingPopups');
      $mousetrackingPopups = json_decode($mousetrackingPopups);                                          
      foreach($mousetrackingPopups as $id => $popup){
        $_mousetrackingPopup = Mage::getModel('magebird_popup/mousetrackingpopup');   
        $_mousetrackingPopup->setMousetrackingId($mousetrackingId);
        $_mousetrackingPopup->setPopupId($id);
        $_mousetrackingPopup->setPopupWidth($popup->width);
        $_mousetrackingPopup->setPopupLeftPosition($popup->left);
        $_mousetrackingPopup->setPopupTopPosition($popup->top);
        $_mousetrackingPopup->setStartSeconds($popup->startDelayMs);   
        $_mousetrackingPopup->setTotalMs($popup->totalMiliSeconds);
        $_mousetrackingPopup->setBehaviour($popup->ca);        
        $_mousetrackingPopup->save();     
      }  
  }
  
  public function delete($strtotimeXAgo){
          $write = Mage::getSingleton('core/resource')->getConnection('core_write');
          $table = Mage::getSingleton("core/resource")->getTableName('magebird_mousetracking');
          $table2 = Mage::getSingleton("core/resource")->getTableName('magebird_mousetracking_popup');
          
          $query = "DELETE $table,$table2 FROM $table
          INNER JOIN $table2 ON $table.mousetracking_id=$table2.mousetracking_id
          WHERE date_created < '".@date('Y-m-d H:i:s', $strtotimeXAgo)."'";                               
          
          $write->query($query);                   
  }
  
  public function deleteOldMousetracking(){
      $deleteOld = Mage::getStoreConfig('magebird_popup/statistics/delete_mousetracking');       
      switch($deleteOld){
        case 1:
          $this->delete(strtotime("-1 month"));
          break;
        case 2:
          $this->delete(strtotime("-6 month"));        
          break;
        case 3:
          $this->delete(strtotime("-7 day"));          
          break;
        case 4:
          //dont delete data  
          break;          
        default:
          $this->delete(strtotime("-6 month"));                                              
      }  
  } 
 
}