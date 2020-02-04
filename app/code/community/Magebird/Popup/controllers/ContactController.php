<?php
class Magebird_Popup_ContactController extends Mage_Core_Controller_Front_Action
{
    public function submitAction()
    {  
        
        $ajaxExceptions = array();    
        $post = $this->getRequest()->getParams();
        if ( $post ) {
            $translate = Mage::getSingleton('core/translate');
            /* @var $translate Mage_Core_Model_Translate */
            $translate->setTranslateInline(false);
            try {
                $postObject = new Varien_Object();
                $postObject->setData($post);

                $error = false;
                
                if (!Zend_Validate::is(trim($post['name']) , 'NotEmpty')) {
                    $error = $this->__('Please write your name');
                }

                if (!Zend_Validate::is(trim($post['comment']) , 'NotEmpty')) {
                    $error = $this->__('Please write your message');
                }

                if (!Zend_Validate::is(trim($post['email']), 'EmailAddress')) {
                    $error = $this->__('Write a valid Email address');
                }

                if ($error) {
                    throw new Exception($error);
                }

                $mailTemplate = Mage::getModel('core/email_template');
                //contacts/email/email_template
                $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                    ->setReplyTo($post['email'])
                    ->sendTransactional(
                        'popup_contact_form',
                        Mage::getStoreConfig('contacts/email/sender_email_identity'),
                        Mage::getStoreConfig('contacts/email/recipient_email'),
                        null,
                        array('data' => $postObject)
                    );
                
                if (!$mailTemplate->getSentSuccess()) {
                    throw new Exception("Problem sending email");
                }
                $popupId         = $this->getRequest()->getParam('popupId');
                $_popup          = Mage::getModel('magebird_popup/popup')->load($popupId);
                $widgetId = $this->getRequest()->getParam('widgetId'); 
                $widgetData      = Mage::helper('magebird_popup')->getWidgetData($_popup->getPopupContent(),$widgetId);              
                $ruleId          = isset($widgetData['rule_id']) ? $widgetData['rule_id'] : '';
                $couponType      = isset($widgetData['coupon_option']) ? $widgetData['coupon_option'] : '';
                
                $widgetData['coupon_expiration'] = isset($widgetData['coupon_expiration']) ? $widgetData['coupon_expiration'] : '';
                $widgetData['rule_id']           = $ruleId;
                $widgetData['coupon_code']       = isset($widgetData['coupon_code']) ? $widgetData['coupon_code'] : '';
             
                $coupon = '';
                if($couponType==2 && $ruleId){ 
                  $coupon = Mage::helper('magebird_popup/coupon')->generateCoupon($widgetData);                                                      
                }elseif(isset($widgetData['coupon_code']) && $widgetData['coupon_code']){
                  $coupon = $widgetData['coupon_code'];
                } 
                        
                $response = json_encode(array('success' => 'success', 'coupon' => $coupon));
                $this->getResponse()->setBody($response);                                              
                return;    
            } catch (Exception $e) {
                $ajaxExceptions['exceptions'][] = $e->getMessage();
            }

        }
        $response = json_encode($ajaxExceptions);
        $this->getResponse()->setBody($response);
    }

}
