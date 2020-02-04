<?php
class Magebird_Popup_UserController extends Mage_Core_Controller_Front_Action
{


    public function loginAction()
    {
      $data = $this->getRequest()->getParams();  
      $session = Mage::getSingleton('customer/session');
      try {
          $session->login($data['login_email'], $data['login_password']);
          $customer = $session->getCustomer();          
          $session->setCustomerAsLoggedIn($customer);
          $response = json_encode(array('success' => 'success'));
          $this->getResponse()->setBody($response);                                              
      } catch (Exception $e) {
          $ajaxExceptions['exceptions'][] = 'Wrong login';
          $response = json_encode($ajaxExceptions);
          $this->getResponse()->setBody($response);            
      }
                
    }

    public function registerAction()
    {         
        if($this->getRequest()->getParam('emailCheck')!='') return;
        if($this->getRequest()->getParam('emailCheck2')!='') return;
        $_popup = Mage::getModel('magebird_popup/popup')->load($this->getRequest()->getParam('popupId'));
        $data1  = Mage::helper('magebird_popup')->getWidgetData($_popup->getPopupContent(),$this->getRequest()->getParam('widgetId'));
        $data2  = $this->getRequest()->getParams();
        $data   = array_merge($data1,$data2);
        
        $data['cpnExpInherit'] = $this->getRequest()->getParam('cpnExpInherit');
        $confirmNeed     = false;
        $magentoNative   = Mage::getStoreConfig('magebird_popup/services/enablemagento');
        $email           = (string) $this->getRequest()->getParam('email');         
        $firstName       = $this->getRequest()->getParam('first_name');
        $lastName        = $this->getRequest()->getParam('last_name');
        $alreadyConfirmed = false;             
        if(isset($data['newsletter_option']) && $data['newsletter_option']==1) $data['is_subscribed']=1;        

        $ajaxExceptions = array();
        $session = Mage::getSingleton('customer/session');

        // prevent XSS injection in user input
        $session->setEscapeMessages(true);
        if ($this->getRequest()->isPost() || $this->getRequest()->isGet()) {
            $errors = array();
            if (!$customer = Mage::registry('current_customer')) {
                $customer = Mage::getModel('customer/customer')->setId(null);
            }
            $customerForm = Mage::getModel('customer/form');
            $customerForm->setFormCode('customer_account_create')->setEntity($customer);
            $customerData = $customerForm->extractData($this->getRequest());                                                         
            if (isset($data['is_subscribed']) && $data['is_subscribed'] && $data['email']) {
                if($magentoNative){
                  $customer->setIsSubscribed(1);
                }                                                             
            }
            $customer->getGroupId();
            
            try {
                $customerErrors = $customerForm->validateData($customerData);
                if ($customerErrors !== true) {
                    $errors = array_merge($customerErrors, $errors);
                } else {                                        
                    $customerForm->compactData($customerData);
                    $customer->setPassword($this->getRequest()->getParam('password'));
                    $customer->setConfirmation($this->getRequest()->getParam('password'));
                    $customer->setPasswordConfirmation($this->getRequest()->getParam('password'));
                    $customerErrors = $customer->validate(); 
                    if (is_array($customerErrors)) {
                        $errors = array_merge($customerErrors, $errors);
                    }
                    if ($this->getRequest()->getPost('create_address')) {
                        $addressErrors = $this->addAddress($customer);
                        if (!empty($addressErrors)) {
                            $errors = array_merge($addressErrors, $errors);
                        }                        
                    }                    
                }
 
                if (count($errors) == 0) {
                        
                    $customer->save(); 
                         
                    Mage::dispatchEvent('customer_register_success',
                        array('account_controller' => $this, 'customer' => $customer)
                    );
                    
                    $coupon = '';
                    $data['coupon_option'] = isset($data['coupon_option']) ? $data['coupon_option'] : null;
                    if($data['coupon_option']==1 || ($data['coupon_option']==2 && isset($data['is_subscribed']) && $data['is_subscribed'])){
                      if(isset($data['coupon_code']) && $data['coupon_code']){
                        $coupon = $data['coupon_code'];
                      }elseif(isset($data['rule_id']) && $data['rule_id']){
                        $rule = Mage::getModel('salesrule/rule')->load($data['rule_id']);  
                        $coupon = Mage::helper('magebird_popup/coupon')->generateCoupon($data);                                 
                      }                      
                    }
                    
                    if(isset($data['send_coupon']) && $data['send_coupon']==1 && $coupon){
                      Mage::getModel('magebird_popup/subscriber')->mailCoupon($email,$coupon);                                                                 
                    }                            
                    
                    //if apply coupon to cart automatically
                    if(isset($data['apply_coupon']) && $data['apply_coupon']==1){        
                      Mage::getSingleton("checkout/session")->setData("coupon_code",$coupon);
                      Mage::getSingleton('checkout/cart')->getQuote()->setCouponCode($coupon)->save();        
                    }       
                                          
                    $this->subscribeNewsletter($data,$coupon);
                     
                    if ($customer->isConfirmationRequired()) {
                        $customer->sendNewAccountEmail(
                            'confirmation',
                            $session->getBeforeAuthUrl(),
                            Mage::app()->getStore()->getId()
                        );
                    } else {
                        $session->setCustomerAsLoggedIn($customer);
                        $customer->sendNewAccountEmail('registered',
                            '',
                            Mage::app()->getStore()->getId()
                        );                        
                    }                     
                    $_popup->setPopupData($_popup->getData('popup_id'),'goal_complition',$_popup->getData('goal_complition')+1);                       
                    $response = json_encode(array('success' => 'success', 'coupon' => $coupon));
                    $this->getResponse()->setBody($response);                                              
                    return;
                } else {
                    if (is_array($errors)) {
                        foreach ($errors as $errorMessage) {
                            $ajaxExceptions['exceptions'][] = $errorMessage;
                        }
                    } else {
                        $ajaxExceptions['exceptions'][] = 'Invalid customer data';
                    }
                }
            } catch (Mage_Core_Exception $e) {
                if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                    $url = Mage::getUrl('customer/account/forgotpassword');
                    $message = $this->__('There is already an account with this email address. <a href="%s">Click here</a> to get your password and access your account.', $url);
                    $session->setEscapeMessages(false);
                } else {
                    $message = $e->getMessage();
                }
 
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $ajaxExceptions['exceptions'][] = $message;
                }
            } catch (Exception $e) {
            	$ajaxExceptions['exceptions'][] = 'Cannot save the customer.';
            }
        }
        $response = json_encode($ajaxExceptions);
        $this->getResponse()->setBody($response);  
    }
    
    public function subscribeNewsletter($data,$coupon){
  
        $mailchimpListId = isset($data['mailchimp_list_id']) ? $data['mailchimp_list_id'] : '';
        $getResponseListToken = isset($data['gr_campaign_token']) ? $data['gr_campaign_token'] : '';
        $campaignMonitorId = isset($data['cm_list_id']) ? $data['cm_list_id'] : '';    
        $mailChimpOption = Mage::getStoreConfig('magebird_popup/services/mailchimp_option');
        $mailchimp       = Mage::getStoreConfig('magebird_popup/services/enablemailchimp');
        $campaignMonitor = Mage::getStoreConfig('magebird_popup/services/enablecampaignmonitor');
        $getResponse     = Mage::getStoreConfig('magebird_popup/services/enablegetresponse');   
        //Mailchimp subscription
        if($mailchimpListId && $mailchimp){
            $api = Mage::getModel('magebird_popup/subscriber')->subscribeMailchimp($mailchimpListId,$data['email'],$data['firstname'],$data['lastname'],$coupon);            
            if($api->errorCode){
              $ajaxExceptions['exceptions'][] = $api->errorMessage;
              $response = json_encode($ajaxExceptions);                
              return $response;           
            }                                                                                                    
        } 
        
        //Campaign monitor subscription
        if($campaignMonitorId && $campaignMonitor){
            $result = Mage::getModel('magebird_popup/subscriber')->subscribeCampaignMonitor($campaignMonitorId,$data['email'],$data['firstname'],$data['lastname'],$coupon);
            //echo "Result of POST /api/v3.1/subscribers/{list id}.{format}\n<br />";
            if(!$result->was_successful()) {
                $ajaxExceptions['exceptions'][] = 'Failed with code '.$result->http_status_code;
                $response = json_encode($ajaxExceptions);
                $this->getResponse()->setBody($response);  
                return $response;   
            }                                                                                                     
        } 
        
        //GetResponse subscription
        if($getResponseListToken && $getResponse){
            $api = Mage::getModel('magebird_popup/subscriber')->subscribeGetResponse($getResponseListToken,$data['email'],$data['firstname'],$data['lastname'],$coupon);
            if(isset($api->errorCode) && $api->errorCode){
              $ajaxExceptions['exceptions'][] = $api->errorMessage;
              $response = json_encode($ajaxExceptions);
              $this->getResponse()->setBody($response);  
              return $response;           
            }                                                                                                    
        }
        return '';     
    }
    
    public function addAddress($customer){
        $address = Mage::getModel('customer/address');
        $addressForm = Mage::getModel('customer/form');
        $addressForm->setFormCode('customer_register_address')
            ->setEntity($address);

        $addressData = $addressForm->extractData($this->getRequest(), 'address', false);
        $addressErrors = $addressForm->validateData($addressData);
        if (is_array($addressErrors)) {
            $errors = array_merge($errors, $addressErrors);
        }
        $address->setId(null)
            ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
            ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));
        $addressForm->compactData($addressData);
        $customer->addAddress($address);  
        $addressErrors = $address->validate();
        $errors = array();
        if (is_array($addressErrors)) {
            $errors = array_merge($errors, $addressErrors);
        }
        return $errors;          
    }
}