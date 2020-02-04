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
use \Magebird\MailChimp; 
class Magebird_Popup_Model_Mysql4_Popup extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('magebird_popup/popup', 'popup_id');  
    }
	  
  	protected function _beforeSave(Mage_Core_Model_Abstract $object) {              
          if(!Mage::app()->getStore()->isAdmin() 
          || Mage::app()->getRequest()->getActionName()=='massStatus'
          || Mage::app()->getRequest()->getActionName()=='massReset') return;
                    
          $this->mailchimpVars($object->getData('popup_content'));
          $this->getResponseCustoms($object->getData('popup_content'));
          $this->campaignMonitorCustoms($object->getData('popup_content'));
          if($object->getFromDate()) {       
              $fromDate = new Zend_Date($object->getFromDate(), Varien_Date::DATETIME_INTERNAL_FORMAT);
              $object->setFromDate($fromDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));                                                         
          }else{
            $object->setFromDate(null); //Magento 1.4 bugfix           
          }
          
          if($object->getToDate()) {
              $toDate = new Zend_Date($object->getToDate(), Varien_Date::DATETIME_INTERNAL_FORMAT);
              $object->setToDate($toDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
          }else{
            $object->setToDate(null); //Magento 1.4 bugfix           
          }
                    
          $pages = $object->getData('pages');  
          if(!$pages || in_array(6,$pages)===FALSE){ 
            $object->setSpecifiedUrl('');
          }else{
            $url = $object->getSpecifiedUrl();
            $url = str_replace(array("http://","https://","index.php/"),'',$url);            
            $object->setSpecifiedUrl($url);
          }
          
          $url = $object->getSpecifiedNotUrl();          
          $url = str_replace(array("http://","https://","index.php/"),'',$url);
          $object->setSpecifiedNotUrl($url);    

          $url = $object->getIfReferral();          
          $url = str_replace(array("http://","https://","index.php/"),'',$url);
          $object->setIfReferral($url);            

          $url = $object->getIfNotReferral();          
          $url = str_replace(array("http://","https://","index.php/"),'',$url);
          $object->setIfNotReferral($url);  
                              
          if(!$pages || in_array(2,$pages)===FALSE){
            $object->setProductIds('');
          }
          
          if(!$pages || in_array(3,$pages)===FALSE){
            $object->setCategoryIds('');
          } 
          if(!$object->getSearchResults() && $object->getSearchResults()!='0'){
            $object->setSearchResults(null);
          }
          
                               
          $object->setCookieId(str_replace(array("|","=",",",":"),"",$object->getCookieId()));                  
          $content = $object->getPopupContent();
          //to make widgets nicely centered
          if(strpos($content,'<p style="text-align: center;">{{')!==false){
            $content = str_replace('<p style="text-align: center;">{{','<div style="text-align: center;">{{',$content);
            $content = str_replace('}}</p>','}}</div>',$content);
            $object->setPopupContent($content);          
          }
           
          return $this;
      }
  
      protected function _afterSave(Mage_Core_Model_Abstract $object) { 
          if(!Mage::app()->getStore()->isAdmin() 
          || Mage::app()->getRequest()->getActionName()=='massStatus'
          || Mage::app()->getRequest()->getActionName()=='massReset') return;
          
  		    $storeTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_store');
          $condition = $this->_getWriteAdapter()->quoteInto('popup_id = ?', $object->getId());
          $this->_getWriteAdapter()->delete($storeTable, $condition);
          if (!$object->getData('stores')) {
              $object->setData('stores', array(0));
          }  
          foreach ((array) $object->getData('stores') as $store) {          
              $storeArray = array();
              $storeArray['popup_id'] = $object->getId();
              $storeArray['store_id'] = $store;
              $this->_getWriteAdapter()->insert($storeTable, $storeArray);
          }

  		    $pageTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_page');
          $this->_getWriteAdapter()->delete($pageTable, $condition);
          if (!$object->getData('pages')) {
              $object->setData('pages', $object->getData('page_id'));
          }
          $pages = $object->getData('pages');
          if (!$pages || in_array(0, $pages)) {
              $object->setData('pages', array(0));
          }
          foreach ((array) $object->getData('pages') as $page) {          
              $pageArray = array();
              $pageArray['popup_id'] = $object->getId();
              $pageArray['page_id'] = $page;
              $this->_getWriteAdapter()->insert($pageTable, $pageArray);
          }
                    
  		    $productTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_product');
          $this->_getWriteAdapter()->delete($productTable, $condition);
          if($object->getData('product_ids')) {
            $productIds = explode(",",$object->getData('product_ids'));               
          }
          if(empty($productIds)) $productIds[] = 0; 
          foreach($productIds as $productId) {
              $productArray = array();
              $productArray['popup_id'] = $object->getId();
              $productArray['product_id'] = $productId;              
              $this->_getWriteAdapter()->insert($productTable, $productArray);
          }           
          
  		    $categoryTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_category');          
          $this->_getWriteAdapter()->delete($categoryTable, $condition);
          if($object->getData('category_ids')) {
            $categoryIds = explode(",",$object->getData('category_ids'));               
          }
          if(empty($categoryIds)){
            $categoryIds[] = 0; 
          } 
          foreach($categoryIds as $categoryId) {
              $categoryArray = array();
              $categoryArray['popup_id'] = $object->getId();
              $categoryArray['category_id'] = $categoryId;
              $this->_getWriteAdapter()->insert($categoryTable, $categoryArray);
          }          
                    
  		    $nVisitorsTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_n_visitor');
          $this->_getWriteAdapter()->delete($nVisitorsTable, $condition);
          $nVisitors = $object->getData('show_every_n');

          foreach ((array) $object->getData('show_every_n') as $nVisitor) {          
              $nVisitorArray = array();
              $nVisitorArray['popup_id'] = $object->getId();
              $nVisitorArray['every_n_visitor'] = $nVisitor;
              $this->_getWriteAdapter()->insert($nVisitorsTable, $nVisitorArray);
          }                    
                    
  		    $customerGroupTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_customer_group');
          $this->_getWriteAdapter()->delete($customerGroupTable, $condition);
          foreach ((array) $object->getData('customer_group') as $group) {          
              $groupArray = array();
              $groupArray['popup_id'] = $object->getId();
              $groupArray['customer_group_id'] = $group;
              $this->_getWriteAdapter()->insert($customerGroupTable, $groupArray);
          }   
          
  		    $dayTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_day');
          $this->_getWriteAdapter()->delete($dayTable, $condition);
 
          foreach ((array) $object->getData('day') as $day) {          
              $dayArray = array();
              $dayArray['popup_id'] = $object->getId();
              $dayArray['day'] = $day;
              $this->_getWriteAdapter()->insert($dayTable, $dayArray);
          }          
          
  		    $countryTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_country');
          $this->_getWriteAdapter()->delete($countryTable, $condition);
          if($object->getData('country_ids')) {
            $countryIds = explode(",",$object->getData('country_ids'));               
          }
          if(empty($countryIds)) $countryIds[] = ''; 
          foreach($countryIds as $countryId) {
              $countryArray = array();
              $countryArray['popup_id'] = $object->getId();
              $countryArray['country_id'] = trim($countryId);              
              $this->_getWriteAdapter()->insert($countryTable, $countryArray);
          } 
          
  		    $notCountryTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_notcountry');
          $this->_getWriteAdapter()->delete($notCountryTable, $condition);
          if($object->getData('not_country_ids')) {
            $notcountryIds = explode(",",$object->getData('not_country_ids'));               
          }
          if(!empty($notcountryIds)){ 
            foreach($notcountryIds as $countryId) {
                $countryArray = array();
                $countryArray['popup_id'] = $object->getId();
                $countryArray['country_id'] = trim($countryId);              
                $this->_getWriteAdapter()->insert($notCountryTable, $countryArray);
            }           
          }
          
  		    $referralTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_referral');
          $this->_getWriteAdapter()->delete($referralTable, $condition);
          $referrals = null;
          if($object->getData('if_referral')) {
            $referrals = explode(",,",$object->getData('if_referral'));               
          }
          if(empty($referrals)) $referrals[] = ''; 
          foreach($referrals as $referral) {
              $referralArray = array();
              $referralArray['popup_id'] = $object->getId();
              $referralArray['referral'] = $referral;              
              $this->_getWriteAdapter()->insert($referralTable, $referralArray);
          }                                                                            

          $referrals = null;
  		    $notreferralTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_notreferral');
          $this->_getWriteAdapter()->delete($notreferralTable, $condition);
          if($object->getData('if_not_referral')) {
            $referrals = explode(",,",$object->getData('if_not_referral'));               
          }
          if(empty($referrals)) $referrals[] = ''; 
          foreach($referrals as $referral) {
              $referralArray = array();
              $referralArray['popup_id'] = $object->getId();
              $referralArray['not_referral'] = $referral;              
              $this->_getWriteAdapter()->insert($notreferralTable, $referralArray);
          }  
          
          $tableName = Mage::getSingleton("core/resource")->getTableName('magebird_popup_stats');        
          $popupId = intval($object->getId());
          $query = "INSERT IGNORE INTO `{$tableName}` (popup_id) VALUE ($popupId)";       
          $this->_getWriteAdapter()->query($query); 
                            
          return parent::_afterSave($object);
      }
      
      protected function _afterLoad(Mage_Core_Model_Abstract $object) {    
          if(!Mage::app()->getStore()->isAdmin() 
          || Mage::app()->getRequest()->getActionName()=='massStatus'
          || Mage::app()->getRequest()->getActionName()=='massReset') return;
          
          $storeTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_store');
          $select = $this->_getReadAdapter()->select()
                          ->from($storeTable)
                          ->where('popup_id = ?', $object->getId());
          if ($data = $this->_getReadAdapter()->fetchAll($select)) {
              $storesArray = array();
              foreach ($data as $row) {
                  $storesArray[] = $row['store_id'];
              }
              $object->setData('store_id', $storesArray);
          }
          
  		    $pageTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_page');
          $select = $this->_getReadAdapter()->select()
                          ->from($pageTable)
                          ->where('popup_id = ?', $object->getId());
          if ($data = $this->_getReadAdapter()->fetchAll($select)) {
              $pagesArray = array();
              foreach ($data as $row) {
                  $pagesArray[] = $row['page_id'];
              }
              $object->setData('page_id', $pagesArray);
          }          
          
  		    $productTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_product');
          $select = $this->_getReadAdapter()->select()
                          ->from($productTable)
                          ->where('popup_id = ?', $object->getId());
          if ($data = $this->_getReadAdapter()->fetchAll($select)) {
              $productsArray = array();
              foreach ($data as $row) {
                  $productsArray[] = $row['product_id'];
              }
              $productIds = implode(",",$productsArray);
              $object->setData('product_ids', $productIds);
          }  
          
  		    $categoryTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_category');
          $select = $this->_getReadAdapter()->select()
                          ->from($categoryTable)
                          ->where('popup_id = ?', $object->getId());
          if ($data = $this->_getReadAdapter()->fetchAll($select)) {
              $categoriesArray = array();
              foreach ($data as $row) {
                  $categoriesArray[] = $row['category_id'];
              }
              $categoryIds = implode(",",$categoriesArray);
              $object->setData('category_ids', $categoryIds);
          }  
          
  		    $nVisitorTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_n_visitor');
          $select = $this->_getReadAdapter()->select()
                          ->from($nVisitorTable)
                          ->where('popup_id = ?', $object->getId());
          if ($data = $this->_getReadAdapter()->fetchAll($select)) {
              $nVisitorsArray = array();
              foreach ($data as $row) {
                  $nVisitorsArray[] = $row['every_n_visitor'];
              }
              $nVisitors = implode(",",$nVisitorsArray);
              $object->setData('show_every_n', $nVisitors);
          }             
          
          $groupsTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_customer_group');
          $select = $this->_getReadAdapter()->select()
                          ->from($groupsTable)
                          ->where('popup_id = ?', $object->getId());
          if ($data = $this->_getReadAdapter()->fetchAll($select)) {
              $groupsArray = array();
              foreach ($data as $row) {
                  $groupsArray[] = $row['customer_group_id'];
              }
              $object->setData('customer_group', $groupsArray);
          }     
          
          $daysTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_day');
          $select = $this->_getReadAdapter()->select()
                          ->from($daysTable)
                          ->where('popup_id = ?', $object->getId());
          if ($data = $this->_getReadAdapter()->fetchAll($select)) {
              $daysArray = array();
              foreach ($data as $row) {
                  $daysArray[] = $row['day'];
              }
              $object->setData('day', $daysArray);
          }                  
          
  		    $countryTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_country');
          $select = $this->_getReadAdapter()->select()
                          ->from($countryTable)
                          ->where('popup_id = ?', $object->getId());
          if ($data = $this->_getReadAdapter()->fetchAll($select)) {
              $countriesArray = array();
              foreach ($data as $row) {
                  $countriesArray[] = $row['country_id'];
              }
              $countryIds = implode(",",$countriesArray);
              $object->setData('country_ids', $countryIds);
          }   
          
  		    $notCountryTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_notcountry');
          $select = $this->_getReadAdapter()->select()
                          ->from($notCountryTable)
                          ->where('popup_id = ?', $object->getId());
          if ($data = $this->_getReadAdapter()->fetchAll($select)) {
              $countriesArray = array();
              foreach ($data as $row) {
                  $countriesArray[] = $row['country_id'];
              }
              $countryIds = implode(",",$countriesArray);
              $object->setData('not_country_ids', $countryIds);
          }                                            

  		    $referralTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_referral');
          $select = $this->_getReadAdapter()->select()
                          ->from($referralTable)
                          ->where('popup_id = ?', $object->getId());
          if ($data = $this->_getReadAdapter()->fetchAll($select)) {
              $referralArray = array();
              foreach ($data as $row) {
                  $referralArray[] = $row['referral'];
              }
              $referrals = implode(",,",$referralArray);
              $object->setData('if_referral', $referrals);
          }                    
                    
  		    $notreferralTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_notreferral');
          $select = $this->_getReadAdapter()->select()
                          ->from($notreferralTable)
                          ->where('popup_id = ?', $object->getId());
          if ($data = $this->_getReadAdapter()->fetchAll($select)) {
              $referralArray = array();
              foreach ($data as $row) {
                  $referralArray[] = $row['not_referral'];
              }
              $referrals = implode(",,",$referralArray);
              $object->setData('if_not_referral', $referrals);
          }  
          
          return parent::_afterLoad($object);
      }
  
      protected function _beforeDelete(Mage_Core_Model_Abstract $object) {
  		    $storeTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_store');
          $adapter = $this->_getReadAdapter();
          $adapter->delete($storeTable, 'popup_id=' . $object->getId());
      }
      
      function mailchimpVars($content){                
          $explode = explode('mailchimp_list_id="',$content);
          if(!isset($explode[1])) return;
          
          require_once(Mage::getBaseDir('lib') . '/magebird/popup/Mailchimp/MailChimp.php');
          $api = new MailChimp(Mage::getStoreConfig('magebird_popup/services/mailchimp_key'));         
          $mailchimpListId = explode('"',$explode[1]);
          $mailchimpListId = $mailchimpListId[0];                    
          if($mailchimpListId){
            $result = $api->get("/lists/$mailchimpListId/merge-fields");
            if(!$result) Mage::throwException("Wrong Mailchimp Api Key");
            if(isset($result['status'])){
             if($result['title']=='Resource Not Found'){
               Mage::throwException("Wrong Mailchimp List id");
             }else{
               Mage::throwException("Mailchimp api error ".$result['detail']);
             }
             return;
            } 
                        
            $tagExists = false;
            foreach ($result['merge_fields'] as $res) {
                if ($res['tag'] == 'POPUP_COUP') {
                    $tagExists = true;
                    return;
                }
            }
           
            if(!$tagExists){
              $res = $api->post("/lists/$mailchimpListId/merge-fields", array(
                    "tag" => "POPUP_COUP",
                    "required" => false, // or true to set is as required 
                    "name" => "Popup Coupon Code",
                    "type" => "text", // text, number, address, phone, email, date, url, imageurl, radio, dropdown, checkboxes, birthday, zip
                    "default_value" => "", // anything
                    "public" => true, // or false to set it as not 
                ));                          
            }                    
          }              
      }
      
      function getResponseCustoms($content){                
          $explode = explode('gr_campaign_token="',$content);
          if(!isset($explode[1])) return;
          
          require_once(Mage::getBaseDir('lib') . '/magebird/popup/GetResponse/GetResponseAPI.class.php');
          $api = new GetResponse(Mage::getStoreConfig('magebird_popup/services/getresponse_key'));            
          $getResponseToken = explode('"',$explode[1]);
          $getResponseToken = $getResponseToken[0];          
          if($getResponseToken){
            $predefines = get_object_vars($api->getCampaignPredefines($getResponseToken));
            if(array_key_exists('code',$predefines)){
              Mage::throwException("GetResponse api error: ".$predefines['message']." or wrong campaign token.");
            }elseif(!in_array('POPUP_COUPON',$predefines)){
              $add = $api->addCampaignPredefine($getResponseToken,'POPUP_COUPON','Popup coupon');       
            }        
          }              
      }  
      
      function campaignMonitorCustoms($content){
          $explode = explode('cm_list_id="',$content);
          if(!isset($explode[1])) return;
                
          require_once(Mage::getBaseDir('lib') . '/magebird/popup/Campaignmonitor/csrest_lists.php');
          $auth = array('api_key' => Mage::getStoreConfig('magebird_popup/services/campaignmonitor_key'));
          $listId = explode('"',$explode[1]);
          $listId = $listId[0];                      
          $wrap = new CS_REST_Lists($listId, $auth);
          
          $result = $wrap->get_custom_fields();
          if(!$result->was_successful()) {          
              Mage::throwException("Campaign Monitor error: ".$result->http_status_code);
          }           
          $customFields = $result->response;
          
          $tagExists = false;           
          foreach($customFields as $field){ 
            if($field->Key=='[POPUP_COUPON]'){
              $tagExists = true;
              return;
            }
          } 
     
          if(!$tagExists){
            $result = $wrap->create_custom_field(array(
                'FieldName' => 'POPUP_COUPON',
                'DataType' => CS_REST_CUSTOM_FIELD_TYPE_TEXT
            ));
          }
          
          if(!$result->was_successful()) {          
              Mage::throwException("Campaign Monitor error: ".$result->http_status_code);
          }      
      }    
}