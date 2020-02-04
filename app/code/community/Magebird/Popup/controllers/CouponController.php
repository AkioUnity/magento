<?php
class Magebird_Popup_CouponController extends Mage_Core_Controller_Front_Action
{

    public function newAction()
    {      
        $coupon = '';        
        $_popup = Mage::getModel('magebird_popup/popup')->load($this->getRequest()->getParam('popupId'));
        $widgetValues = Mage::helper('magebird_popup')->getWidgetData($_popup->getPopupContent(),$this->getRequest()->getParam('widgetId'));
        if(isset($widgetValues['coupon_code']) && $widgetValues['coupon_code']){
          $coupon = $widgetValues['coupon_code'];
        }elseif(isset($widgetValues['rule_id'])){                   
          $rule = Mage::getModel('salesrule/rule')->load($widgetValues['rule_id']);
          if($rule->getData('rule_id')){
            $data = $widgetValues;
            $data['cpnExpInherit'] = $this->getRequest()->getParam('cpnExpInherit');
            $coupon = Mage::helper('magebird_popup/coupon')->generateCoupon($data);
          }                                 
        }
        if($coupon && isset($widgetValues['apply_coupon']) && $widgetValues['apply_coupon']==1){
          Mage::getSingleton("checkout/session")->setData("coupon_code",$coupon);
          Mage::getSingleton('checkout/cart')->getQuote()->setCouponCode($coupon)->save();        
        } 
        
        $_popup->setPopupData($_popup->getData('popup_id'),'goal_complition',$_popup->getData('goal_complition')+1);              
        $response = json_encode(array('success' => 'success', 'coupon' => $coupon));
        $this->getResponse()->setBody($response);         
    }
}