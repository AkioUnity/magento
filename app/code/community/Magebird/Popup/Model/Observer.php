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
 
class Magebird_Popup_Model_Observer
{  
    public function checkIfNoResults(Varien_Event_Observer $observer){
      $block = Mage::app()->getLayout()->getBlock('search_result_list');
      if($block){
        $collection = $block->getLoadedProductCollection();    
        if ($collection) {
          //this will set no results cookie for 3 seconds
          Mage::helper('magebird_popup')->setPopupCookie('resN',$collection->getSize());
        }
      }
    }
    public function updateCurrency(Varien_Event_Observer $observer){
        $request = Mage::app()->getRequest();
        if($request->getControllerName()!="currency" || $request->getActionName()!="switch"){
          return;
        }else{
          $currency = $request->getParam('currency');
          $symbol = Mage::app()->getLocale()->currency($currency)->getSymbol();
          $format = Mage::app()->getLocale()->currency($currency)->toCurrency(1);
          $cookies[] = array('cookieName'=>'currency','value'=>$currency,'expired'=>false);
          //currency symbol
          $cookies[] = array('cookieName'=>'csy','value'=>$symbol,'expired'=>false);
          //currency format
          $cookies[] = array('cookieName'=>'cfo','value'=>$format,'expired'=>false);
          //base currency
          $cookies[] = array('cookieName'=>'bc','value'=>Mage::app()->getStore()->getBaseCurrencyCode(),'expired'=>false);
          Mage::helper('magebird_popup')->setPopupMultiCookie($cookies);
        }    
    }
    
    public function updatePopupSession(Varien_Event_Observer $observer){      
      if($observer->getData('event')->getName()!='customer_logout'){
        $_cart = Mage::getModel('checkout/cart')->getQuote(); 
        $productIds = array();                      
        $count = 0;                           
        foreach ($_cart->getAllItems() as $item) {             
            if($count>20) break;
            $productIds[] = $item->getProduct()->getId();
            $count++;
        }     
        $productIds = implode(",",$productIds);             
        $cartSubtotal = Mage::helper('magebird_popup')->getBaseSubtotal();                     
      }else{
        $productIds = '';    
        $cartSubtotal = 0;                                                                             
      }
      $cookies[] = array('cookieName'=>'cartSubtotal','value'=>$cartSubtotal,'expired'=>false);
      $cookies[] = array('cookieName'=>'cartProductIds','value'=>$productIds,'expired'=>false);
      $cookies[] = array('cookieName'=>'cartQty','value'=>Mage::helper('checkout/cart')->getSummaryCount(),'expired'=>false);
      
      $isSubscribed = false; 
      if($observer->getData('event')->getName()=='customer_logout'){
        $cookies[] = array('cookieName'=>'customerGroupId','value'=>0,'expired'=>false);     
        $cookies[] = array('cookieName'=>'loggedIn','value'=>0,'expired'=>false);   
      }elseif(Mage::getSingleton('customer/session')->isLoggedIn()){      
        $cookies[] = array('cookieName'=>'loggedIn','value'=>'1','expired'=>false);
        $value = Mage::getSingleton('customer/session')->getCustomerGroupId();
        $cookies[] = array('cookieName'=>'customerGroupId','value'=>$value,'expired'=>false);      
        $email = Mage::getSingleton('customer/session')->getCustomer()->getData('email');
        $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);        
        if($subscriber->getId()){
            $isSubscribed = $subscriber->getData('subscriber_status') == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED;
        }                
      } else {
        $cookies[] = array('cookieName'=>'customerGroupId','value'=>0,'expired'=>false); 
        $cookies[] = array('cookieName'=>'loggedIn','value'=>0,'expired'=>false);      
      }  
      //we need this is show when is 8
      if($observer->getData('event')->getName()=='checkout_cart_save_after' && !Mage::helper('magebird_popup')->getPopupCookie('cartAddedTime')){
        $cookies[] = array('cookieName'=>'cartAddedTime','value'=>Mage::getModel('core/date')->timestamp(time()),'expired'=>time()+7200);
      } 
                   
      $cookies[] = array('cookieName'=>'isSubscribed','value'=>$isSubscribed,'expired'=>false);
      $cookies[] = array('cookieName'=>'pendingOrder','value'=>$this->checkPendingOrder(),'expired'=>false);
      //reset session because logout (and probably also other actino) will rename session id
      //we will update cookie with correct magentosessionid with next ajax call 
      $cookies[] = array('cookieName'=>'magentoSessionId','value'=>'','expired'=>false);
      Mage::helper('magebird_popup')->setPopupMultiCookie($cookies);          
    }

    public function newOrder(Varien_Event_Observer $observer){
      $_event = $observer->getEvent();
      $orderIds = $_event->getData('order_ids');
      $orderId = intval($orderIds[0]);
      $couponCode = Mage::getModel('sales/order')->load($orderId)->getData('coupon_code');
      $popupId = Mage::getModel('salesrule/coupon')->load($couponCode, 'code')->getData('is_popup');      
      if(!$popupId){                
        $lastPopupCoupon = Mage::helper('magebird_popup')->getPopupCookie('lastCoupon');
        $lastPopupCoupon = explode("-",$lastPopupCoupon);
        if(isset($lastPopupCoupon[1]) && $lastPopupCoupon[1] == $couponCode){          
          $popupId = $lastPopupCoupon[0];                
        }
      }
      //used for Cpn Sales statistics
      $write = Mage::getSingleton('core/resource')->getConnection('core_write');
      if($popupId){
        $tableName = Mage::getSingleton("core/resource")->getTableName('magebird_popup_orders');        
        $popupId = intval($popupId);
        $query = "INSERT IGNORE INTO `{$tableName}` (popup_id,order_id) VALUES ($popupId,$orderId)";       
        $write->query($query);        
      } 
      
      //used for Cpn Orders, conversion and abonded cart statistics
      $lastPopups = Mage::helper('magebird_popup')->getPopupCookie('lastPopups');
      $lastPopups = explode(",",$lastPopups);
      $tableName = Mage::getSingleton("core/resource")->getTableName('magebird_popup_stats');
      foreach($lastPopups as $popupId){
        $popupId = intval($popupId);
        $sql = "UPDATE $tableName SET popup_purchases=popup_purchases+1 WHERE popup_id=$popupId";
        $write->query($sql);        
      }
      $query = "UPDATE $tableName SET purchases=purchases+1";
      $write->query($query);       
    }
    
    public function checkPendingOrder(){
      $customerId = Mage::getSingleton('customer/session')->getId();   
      if(!$customerId) return 0;
      $orderCollection = Mage::getResourceModel('sales/order_collection');
      //->addFieldToFilter('created_at', array('gt' =>  new Zend_Db_Expr("DATE_ADD('".now()."', INTERVAL -'240:00' HOUR_MINUTE)")))    
      $orderCollection
              ->addFieldToFilter('status', 'pending')            
              ->addFieldToFilter('customer_id', $customerId)
              ->getSelect()
              ->limit(1)                   
      ; 
      
      if(count($orderCollection)){
        return 1;
      }else{
        return 0;
      }
    }    
    
    public function deleteExpired(Varien_Event_Observer $observer){      
      $coupon = $observer->getData('quote')->getData('coupon_code');
      if($coupon){
        $_coupons = Mage::getModel('salesrule/coupon')->getCollection();
        $_coupons->addFieldToFilter('expiration_date',
                                      array(
                                          'notnull' => true
                                        )
                                   );
        
        $_coupons->addFieldToFilter('expiration_date',
                                      array(
                                          'to' => date("Y-m-d H:i:s",Mage::getModel('core/date')->timestamp(time())),
                                          'datetime' => true
                                        )
                                   );
                                    
        $_coupons->addFieldToFilter('is_popup',array('notnull' => true));
        //delete coupon only if there is no user ip stored or coupon hasn't been used yet.
        //if ip is stored we don't allow the same user to get 2 coupon codes and this is why we need to keep ip in database
        //after 1 year delete coupon anyway
        $_coupons->getSelect()->where("(`times_used` = 0) OR (`user_ip` IS NULL) 
          OR `expiration_date`< '".date("Y-m-d H:i:s",Mage::getModel('core/date')->timestamp(time()-(60*60*24*365)))."'"); 
                                                                                      
        foreach($_coupons as $_coupon){
          $_coupon->delete();
        }              
      }    
    }
    
    public function sendCoupon(Varien_Event_Observer $observer)
    {  
        $currentUrl = Mage::helper('core/url')->getCurrentUrl();   
        if(strpos($currentUrl,'subscriber/confirm')!==false){                      
          $explode = explode("confirm/id/",$currentUrl);
          $explode = explode("/code/",$explode[1]);
          $subscriberId = $explode[0]; 
          $subscriber = Mage::getModel('newsletter/subscriber')->load($subscriberId);
          $email = $subscriber->getData('subscriber_email');        
          $model = Mage::getModel('magebird_popup/subscriber')->getCollection();
          $model->addFieldToFilter('subscriber_email', $email);
          $popupSubscriberData = $model->getLastItem()->getData();                               
          if($popupSubscriberData){                                
            //old version had field cart_rule_id, new version has rule_id
            if(isset($popupSubscriberData['cart_rule_id'])){
              $popupSubscriberData['rule_id'] = $popupSubscriberData['cart_rule_id'];
            }                      
            $coupon = $popupSubscriberData['coupon_code'];
            if(!$coupon && isset($popupSubscriberData['rule_id']) && $popupSubscriberData['rule_id']){            
              $coupon = Mage::helper('magebird_popup/coupon')->generateCoupon($popupSubscriberData);
            }
            if($popupSubscriberData['apply_coupon']==1){        
              Mage::getSingleton("checkout/session")->setData("coupon_code",$coupon);
              Mage::getSingleton('checkout/cart')->getQuote()->setCouponCode($coupon)->save();        
            }   
            if($popupSubscriberData['send_coupon']==1){              
              Mage::getModel('magebird_popup/subscriber')->mailCoupon($email,$coupon);
            }           
            Mage::getModel('magebird_popup/subscriber')->cleanOldEmails();
            $session = Mage::getSingleton('core/session');
            $session->addSuccess(Mage::helper('core')->__('Your coupon code is:')." ".$coupon);   
            Mage::getModel('magebird_popup/subscriber')->deleteTempSubscriber($email);               
          } 
        } 
    }
    
    public function applyCoupon(Varien_Event_Observer $observer)
    {            
      $coupon_code = trim(Mage::getSingleton("checkout/session")->getData("coupon_code"));
      if($coupon_code){
        Mage::getSingleton('checkout/cart')->getQuote()->setCouponCode($coupon_code)->save();
      }
      $this->cartAdded();
    }   

    public function cartAdded()
    {            
      //to make sure to not repeat the process, only on first item added submit stats
      if(Mage::helper('checkout/cart')->getItemsCount()) return;      
      if(!Mage::helper('magebird_popup')->getPopupCookie('cartAdded')){        
        Mage::helper('magebird_popup')->setPopupCookie('cartAdded',1,time()+10800);
        $tableName = Mage::getSingleton("core/resource")->getTableName('magebird_popup_stats');
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');    
        $sql = "UPDATE $tableName SET total_carts=total_carts+1";
        $write->query($sql);              
        $lastPopups = Mage::helper('magebird_popup')->getPopupCookie('lastPopups');
        $lastPopups = explode(",",$lastPopups);
        foreach($lastPopups as $popupId){
          $popupId = intval($popupId);
          $sql = "UPDATE $tableName SET popup_carts=popup_carts+1 WHERE popup_id=$popupId";
          $write->query($sql);        
        }
      }
    } 
    
    public function subscribed(Varien_Event_Observer $observer)
    {            
      //don't work in old versions of Magento. Put this also in NewsletterController.php.
      //also there won't work 100% because user can subscribe also outside popup
      Mage::helper('magebird_popup')->setPopupCookie('isSubscribed',1);
    }              
              
}