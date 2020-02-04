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
class Magebird_Popup_Helper_Coupon extends Mage_Core_Helper_Abstract
{
  public function generateCoupon($data)
  {         
      $rule = Mage::getModel('salesrule/rule')->load($data['rule_id']);  
      $generator = $rule->getCouponMassGenerator();       
      $data['format'] = 'alphanum';
      $data['length'] = isset($data['coupon_length']) ? $data['coupon_length'] : 12;
      $data['qty'] = 1;
      $data['prefix'] = isset($data['coupon_prefix']) ? $data['coupon_prefix'] : '';
      $data['rule_id'] = isset($data['rule_id']) ? $data['rule_id'] : '';
      $data['uses_per_coupon'] = 1;
      $data['uses_per_customer'] = 1; 
      $data['coupon_expiration'] = isset($data['coupon_expiration']) ? $data['coupon_expiration'] : '';
      $data['cpnExpInherit'] = isset($data['cpnExpInherit']) ? $data['cpnExpInherit'] : '';
      $data['expiration_date'] = isset($data['expiration_date']) ? $data['expiration_date'] : '';       
      //Show only once per IP?
      //means coupon info and user ip was stored in db because user needed to confirm subscription first      
      if(isset($data['user_ip'])){
        $ip = $data['user_ip'];        
      //means the data came directly from widget and not db, no confirmation was required
      }elseif(isset($data['coupon_limit_ip']) && $data['coupon_limit_ip']==1){
        $ip = $_SERVER['REMOTE_ADDR'];
      }else{
        $ip = '';
      }     
      if (!$generator->validateData($data)) {
          $result['error'] = Mage::helper('salesrule')->__('Not valid data provided');
      } else {
          $generator->setData($data);
          $generator->generatePool();
          $collection = Mage::getResourceModel('salesrule/coupon_collection')
                      ->addRuleToFilter($rule)
                      ->addGeneratedCouponsFilter();
          $_coupon = $collection->getLastItem();
          if(($data['coupon_expiration'] && $data['coupon_expiration']!='inherit') || ($data['coupon_expiration']=='inherit' && $data['cpnExpInherit'])){
              if($data['coupon_expiration']=='inherit'){
                $expiration = date("Y-m-d H:i:s",Mage::getModel('core/date')->timestamp(time())+$data['cpnExpInherit']);
              }else{
                $expiration = date("Y-m-d H:i:s",Mage::getModel('core/date')->timestamp(time())+($data['coupon_expiration']*60));
              }                            
              $_coupon->setExpirationDate($expiration);                                                        
          }elseif($data['expiration_date']){
              $_coupon->setExpirationDate($data['expiration_date']);
                          
          }   
          $_coupon->setUserIp($ip);  
          //at the moment only newsletter widget has this support
          if(isset($data['popup_cookie_id'])){
            $_coupon->setPopupCookieId($data['popup_cookie_id']);
          }   
          $popupId = Mage::app()->getRequest()->getParam('popupId');  
          if($popupId){
            $_coupon->setIsPopup($popupId);
          }else{
            //it still need to be true and not empty or false, we put 5000 to make sure that id doesn't exist
            $_coupon->setIsPopup(5000);
          }                                   
          $_coupon->save();                 
          $coupon = $_coupon->getData('code');       
      }                                             
      return $coupon;
  }
}	
 
