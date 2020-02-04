<?php
require_once(Mage::getBaseDir().DS.'lib'.DS.'fedex'.DS.'library'.DS.'fedex-common.php');
require_once(Mage::getModuleDir('controllers','Mage_Checkout').DS.'OnepageController.php');

class Biztech_Fedex_OnepageController extends Mage_Checkout_OnepageController
{
    const XML_PATH_ACCOUNT_NO = 'carriers/auspost_configuration/accountNo';
    const XML_PATH_API_KEY = 'carriers/auspost_configuration/apiKey';
    const XML_PATH_PASSWORD = 'carriers/auspost_configuration/password';
    
    public function saveBillingAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('billing', array());
            $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);

            if (isset($data['email'])) {
                $data['email'] = trim($data['email']);
            }
            $result = $this->getOnepage()->saveBilling($data, $customerAddressId);            
            $moduleName = 'Biztech_Auspost'; 
            if (Mage::helper('core')->isModuleEnabled($moduleName)) {
                if (Mage::helper('auspost')->isEnable() && Mage::getStoreConfig('carriers/other_configuration/enable_addressvalidationfront')) {               
                    // biztech auspost address suggestions
                    $result['auspost_address_validate'] = false;
                    if($data['use_for_shipping'] == 1){
                            if($data['country_id'] == 'AU') {
                                $account_no = Mage::helper('core')->decrypt(Mage::getStoreConfig(self::XML_PATH_ACCOUNT_NO));
                                $username = Mage::helper('core')->decrypt(Mage::getStoreConfig(self::XML_PATH_API_KEY));
                                $password = Mage::helper('core')->decrypt(Mage::getStoreConfig(self::XML_PATH_PASSWORD));
                                $suburb = $data['city'];
                                $region_id = $data['region_id'];
                                $region = Mage::getModel('directory/region')->load($region_id);
                                $state = $region->getCode();
                                $postcode = $data['postcode'];

                                if(Mage::getStoreConfig('carriers/auspost_configuration/operationMode') == 2)
                                {
                                    $url = 'https://digitalapi.auspost.com.au/testbed/shipping/v1/address?suburb='.urlencode($suburb).'&state='.$state.'&postcode='.$postcode;
                                }elseif(Mage::getStoreConfig('carriers/auspost_configuration/operationMode') == 1){            
                                    $url = 'https://digitalapi.auspost.com.au/shipping/v1/address?suburb='.urlencode($suburb).'&state='.$state.'&postcode='.$postcode;
                                }

                                $ch = curl_init($url); 
                                curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
                                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Account-Number: ' . $account_no));
                                $curlResults = curl_exec($ch);
                                $responseData = json_decode($curlResults); 
                                if(!$responseData->found && sizeof($responseData->results))
                                {   
                                    $auspost_address = array();
                                    $auspost_address['suggesstions'] = implode(', ',$responseData->results);  
                                    $result['auspost_address_validate'] = true;                                    

                                }  else {
                                    if (!isset($result['error'])) {
                                        if ($this->getOnepage()->getQuote()->isVirtual()) {
                                            $result['goto_section'] = 'payment';
                                            $result['update_section'] = array(
                                                'name' => 'payment-method',
                                                'html' => $this->_getPaymentMethodsHtml()
                                            );
                                        } elseif (isset($data['use_for_shipping']) && $data['use_for_shipping'] == 1) {
                                            $result['goto_section'] = 'shipping_method';
                                            $result['update_section'] = array(
                                                'name' => 'shipping-method',
                                                'html' => $this->_getShippingMethodsHtml()
                                            );

                                            $result['allow_sections'] = array('shipping');
                                            $result['duplicateBillingInfo'] = 'true';
                                        } else {
                                            $result['goto_section'] = 'shipping';
                                        }
                                    }
                                }

                            } else {
                                /*if (!isset($result['error'])) {
                                        if ($this->getOnepage()->getQuote()->isVirtual()) {
                                            $result['goto_section'] = 'payment';
                                            $result['update_section'] = array(
                                                'name' => 'payment-method',
                                                'html' => $this->_getPaymentMethodsHtml()
                                            );
                                        } elseif (isset($data['use_for_shipping']) && $data['use_for_shipping'] == 1) {
                                            $result['goto_section'] = 'shipping_method';
                                            $result['update_section'] = array(
                                                'name' => 'shipping-method',
                                                'html' => $this->_getShippingMethodsHtml()
                                            );

                                            $result['allow_sections'] = array('shipping');
                                            $result['duplicateBillingInfo'] = 'true';
                                        } else {
                                            $result['goto_section'] = 'shipping';
                                        }
                                } */                           
                            }                    
                        if($this->getRequest()->getPost('auspost_address_resolve_billing') != 1){
                            $result['auspost_address_validate'] = true;
                            $result['auspost'] = $auspost_address;
                        }
                        else{

                            $result['auspost_address_validate'] = false;   
                        }
                    }                
                }


                if (Mage::helper('auspost')->isEnable() && Mage::getStoreConfig('carriers/other_configuration/enable_addressvalidationfront')){
                    if (!isset($result['error']) && $result['auspost_address_validate'] == false) {
                        /* if ($this->getOnepage()->getQuote()->isVirtual()) {
                            $result['goto_section'] = 'payment';
                            $result['update_section'] = array(
                                'name' => 'payment-method',
                                'html' => $this->_getPaymentMethodsHtml()
                            );
                        } elseif (isset($data['use_for_shipping']) && $data['use_for_shipping'] == 1) {
                            /*$result['goto_section'] = 'shipping_method';
                            $result['update_section'] = array(
                                'name' => 'shipping-method',
                                'html' => $this->_getShippingMethodsHtml()
                            );

                            $result['allow_sections'] = array('shipping');
                            $result['duplicateBillingInfo'] = 'true';
                        } else {
                            $result['goto_section'] = 'shipping';
                        }*/
                    }
                }
                else{
                    if (!isset($result['error'])) {
                        /*if ($this->getOnepage()->getQuote()->isVirtual()) {
                            $result['goto_section'] = 'payment';
                            $result['update_section'] = array(
                                'name' => 'payment-method',
                                'html' => $this->_getPaymentMethodsHtml()
                            );
                        } elseif (isset($data['use_for_shipping']) && $data['use_for_shipping'] == 1) {
                            $result['goto_section'] = 'shipping_method';
                            $result['update_section'] = array(
                                'name' => 'shipping-method',
                                'html' => $this->_getShippingMethodsHtml()
                            );

                            $result['allow_sections'] = array('shipping');
                            $result['duplicateBillingInfo'] = 'true';
                        } else {
                            $result['goto_section'] = 'shipping';
                        }*/
                    }
                }
            }
            
            
            
            
            
            
            if (Mage::helper('fedex')->isEnable() && Mage::getStoreConfig('carriers/fedex/enable_addressvalidationfront')){
                // biztech fedex
                $result['fedex_address_validate'] = false;
                if($data['use_for_shipping'] == 1){
                    $path_to_wsdl = Mage::getBaseDir().DS.'lib'.DS.'fedex'.DS.'wsdl'.DS.'AddressValidationService_v4.wsdl';
                    ini_set("soap.wsdl_cache_enabled", "0");
                    $client = new SoapClient($path_to_wsdl, array('trace' => 1)); 
                    $client->__setLocation(Mage::getStoreConfig('carriers/fedex/sandbox_mode')
                        ? 'https://wsbeta.fedex.com:443/web-services '
                        : 'https://ws.fedex.com:443/web-services'
                    );
                    //make soap request to api
                    $request['WebAuthenticationDetail'] = array(
                        'UserCredential' => array(
                            'Key' => Mage::getStoreConfig('carriers/fedex/key'), 
                            'Password' => Mage::getStoreConfig('carriers/fedex/password')
                        )
                    );

                    $request['ClientDetail'] = array(
                        'AccountNumber' => Mage::getStoreConfig('carriers/fedex/account'), 
                        'MeterNumber' => Mage::getStoreConfig('carriers/fedex/meter_number')
                    );
                    $request['TransactionDetail'] = array('CustomerTransactionId' => ' *** Address Validation Request using PHP ***');
                    $request['Version'] = array(
                        'ServiceId' => 'aval', 
                        'Major' => '4', 
                        'Intermediate' => '0', 
                        'Minor' => '0'
                    );
                    $request['InEffectAsOfTimestamp'] = date('c');
                    /*if($data['region_id'] != ""){
                        $region = Mage::getModel('directory/region')->load($data['region_id']);
                        $state_code = $region->getCode();
                    }
                    else{
                        $state_code = $data['region'];
                    }*/
                    
                    if($data['region_id'] != "" || $data['region_id'] != NULL){

                        $state_code = Mage::getModel('directory/region')->load($data['region_id'])->load()->getCode();
                    }
                    else{
                        $state_code = "";
                    }
                    $request['AddressesToValidate'] = array(
                        0 => array(
                            'ClientReferenceId' => 'ClientReferenceId1',
                            'Address' => array(
                                'StreetLines' => $data['street'],
                                'PostalCode' => $data['postcode'],
                                'City' => $data['city'],
                                'StateOrProvinceCode' => $state_code,
                                'CountryCode' => $data['country_id']
                            )
                        )
                    );
                    try {
                        $response = $client->addressValidation($request);
                        $fedex_address = array();
                        $fedex_address['fedex_effective_validate'] = (array)$response->AddressResults->EffectiveAddress;                        
                        $fedex_address['state'] = $response->AddressResults->State;
                        $fedex_address['classification'] = $response->AddressResults->Classification;                        
                        if($this->getRequest()->getPost('fedex_address_resolve') != 1){
                            $result['fedex_address_validate'] = true;
                        }
                        else{
                            $result['fedex_address_validate'] = false;   
                        }
                        $result['fedex'] = $fedex_address;
                    } catch (SoapFault $exception) {
                        
                    }
                }
                // biztech fedex
            }
            if (Mage::helper('fedex')->isEnable() && Mage::getStoreConfig('carriers/fedex/enable_addressvalidationfront')){
                if (!isset($result['error']) && $result['fedex_address_validate'] == false) {
                    if ($this->getOnepage()->getQuote()->isVirtual()) {
                        $result['goto_section'] = 'payment';
                        $result['update_section'] = array(
                            'name' => 'payment-method',
                            'html' => $this->_getPaymentMethodsHtml()
                        );
                    } elseif (isset($data['use_for_shipping']) && $data['use_for_shipping'] == 1) {
                        $result['goto_section'] = 'shipping_method';
                        $result['update_section'] = array(
                            'name' => 'shipping-method',
                            'html' => $this->_getShippingMethodsHtml()
                        );

                        $result['allow_sections'] = array('shipping');
                        $result['duplicateBillingInfo'] = 'true';
                    } else {
                        $result['goto_section'] = 'shipping';
                    }
                }
            }
            else{
                if (!isset($result['error'])) {
                    if ($this->getOnepage()->getQuote()->isVirtual()) {
                        $result['goto_section'] = 'payment';
                        $result['update_section'] = array(
                            'name' => 'payment-method',
                            'html' => $this->_getPaymentMethodsHtml()
                        );
                    } elseif (isset($data['use_for_shipping']) && $data['use_for_shipping'] == 1) {
                        $result['goto_section'] = 'shipping_method';
                        $result['update_section'] = array(
                            'name' => 'shipping-method',
                            'html' => $this->_getShippingMethodsHtml()
                        );

                        $result['allow_sections'] = array('shipping');
                        $result['duplicateBillingInfo'] = 'true';
                    } else {
                        $result['goto_section'] = 'shipping';
                    }
                }
            }
            
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    /**
     * Shipping address save action
     */
    public function saveShippingAction()
    {
            

        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('shipping', array());



            $customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);
            $result = $this->getOnepage()->saveShipping($data, $customerAddressId);
            
            $moduleName = 'Biztech_Auspost'; 
            if (Mage::helper('core')->isModuleEnabled($moduleName)) {
               if (Mage::helper('auspost')->isEnable() && $frontValidationConfig==1) {
                $result['auspost_address_validate'] = false; 
                    if($data['country_id'] == 'AU') 
                    {
                        $account_no = Mage::helper('core')->decrypt(Mage::getStoreConfig(self::XML_PATH_ACCOUNT_NO));
                        $username = Mage::helper('core')->decrypt(Mage::getStoreConfig(self::XML_PATH_API_KEY));
                        $password = Mage::helper('core')->decrypt(Mage::getStoreConfig(self::XML_PATH_PASSWORD));
                        $suburb = $data['city'];
                        $region_id = $data['region_id'];
                        $region = Mage::getModel('directory/region')->load($region_id);
                        $state = $region->getCode();
                        $postcode = $data['postcode'];

                        if(Mage::getStoreConfig('carriers/auspost_configuration/operationMode') == 2)
                        {
                            $url = 'https://digitalapi.auspost.com.au/testbed/shipping/v1/address?suburb='.urlencode($suburb).'&state='.$state.'&postcode='.$postcode;
                        }elseif(Mage::getStoreConfig('carriers/auspost_configuration/operationMode') == 1){            
                            $url = 'https://digitalapi.auspost.com.au/shipping/v1/address?suburb='.urlencode($suburb).'&state='.$state.'&postcode='.$postcode;
                        }

                        $ch = curl_init($url); 
                        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);                                                                
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Account-Number: ' . $account_no));
                        $curlResults = curl_exec($ch);
                        $responseData = json_decode($curlResults);                        
                        if(!$responseData->found && sizeof($responseData->results))
                        {
                                $auspost_address = array();
                                $auspost_address['suggesstions'] = implode(', ',$responseData->results);  
                                $result['auspost_address_validate'] = true;
                        }
                        else {
                            if (!isset($result['error'])) {
                                $result['goto_section'] = 'shipping_method';
                                $result['update_section'] = array(
                                    'name' => 'shipping-method',
                                    'html' => $this->_getShippingMethodsHtml()
                                );
                            }                            
                        }
                    
                    }

                if($this->getRequest()->getPost('auspost_address_resolve_shipping') != 1){
                    $result['auspost_address_validate'] = true;
                    $result['auspost'] = $auspost_address;
                }
                else{
                    $result['auspost_address_validate'] = false;   
                }
            }
                if (Mage::helper('auspost')->isEnable()){
                    if (!isset($result['error']) && $result['auspost_address_validate'] == false) {
                        $result['goto_section'] = 'shipping_method';
                        $result['update_section'] = array(
                            'name' => 'shipping-method',
                            'html' => $this->_getShippingMethodsHtml()
                        );
                    }
                }
                else{
                    if (!isset($result['error'])) {
                        $result['goto_section'] = 'shipping_method';
                        $result['update_section'] = array(
                            'name' => 'shipping-method',
                            'html' => $this->_getShippingMethodsHtml()
                        );
                    }
                }
            }    

            if (Mage::helper('fedex')->isEnable() && Mage::getStoreConfig('carriers/fedex/enable_addressvalidationfront')){
            
                $result['fedex_address_validate'] = false;
                // biztech fedex
                $path_to_wsdl = Mage::getBaseDir().DS.'lib'.DS.'fedex'.DS.'wsdl'.DS.'AddressValidationService_v4.wsdl';
                ini_set("soap.wsdl_cache_enabled", "0");
                $client = new SoapClient($path_to_wsdl, array('trace' => 1)); 
                $client->__setLocation(Mage::getStoreConfig('carriers/fedex/sandbox_mode')
                    ? 'https://wsbeta.fedex.com:443/web-services '
                    : 'https://ws.fedex.com:443/web-services'
                );
                //make soap request to api
                
                $request['WebAuthenticationDetail'] = array(
                    'UserCredential' => array(
                        'Key' => Mage::getStoreConfig('carriers/fedex/key'), 
                        'Password' => Mage::getStoreConfig('carriers/fedex/password')
                    )
                );

                $request['ClientDetail'] = array(
                    'AccountNumber' => Mage::getStoreConfig('carriers/fedex/account'), 
                    'MeterNumber' => Mage::getStoreConfig('carriers/fedex/meter_number')
                );
                $request['TransactionDetail'] = array('CustomerTransactionId' => ' *** Address Validation Request using PHP ***');
                $request['Version'] = array(
                    'ServiceId' => 'aval', 
                    'Major' => '4', 
                    'Intermediate' => '0', 
                    'Minor' => '0'
                );
                $request['InEffectAsOfTimestamp'] = date('c');
                
                
               

                if($data['region_id'] != "" || $data['region_id'] != NULL){

                        $state_code = Mage::getModel('directory/region')->load($data['region_id'])->load()->getCode();
                    }
                    else{
                        $state_code = "";
                    }



                

                $request['AddressesToValidate'] = array(
                    0 => array(
                        'ClientReferenceId' => 'ClientReferenceId1',
                        'Address' => array(
                            /*'StreetLines' => $data['street'],*/
                            'PostalCode' => $data['postcode'],
                            'City' => $data['city'],
                            'StateOrProvinceCode' => $state_code,
                            'CountryCode' => $data['country_id']
                        )
                    )
                );
                    

                try {
                    

                    $response = $client->addressValidation($request);
                    
                    
                    

                    $fedex_address = array();
                    $fedex_address['fedex_effective_validate'] = (array)$response->AddressResults->EffectiveAddress;
                    
                    $fedex_address['state'] = $response->AddressResults->State;
                    $fedex_address['classification'] = $response->AddressResults->Classification;
                    
                    
                    
                    if($this->getRequest()->getPost('fedex_address_resolve_shipping') != 1){
                        $result['fedex_address_validate'] = true;
                    }
                    else{
                        $result['fedex_address_validate'] = false;   
                    }

                    $result['fedex'] = $fedex_address;
                } catch (SoapFault $exception) {
                    
                }
                
                // biztech fedex

            }

            if (Mage::helper('fedex')->isEnable() && Mage::getStoreConfig('carriers/fedex/enable_addressvalidationfront')){
                if (!isset($result['error']) && $result['fedex_address_validate'] == false) {
                    $result['goto_section'] = 'shipping_method';
                    $result['update_section'] = array(
                        'name' => 'shipping-method',
                        'html' => $this->_getShippingMethodsHtml()
                    );
                }
            }
            else{
                if (!isset($result['error'])) {
                    $result['goto_section'] = 'shipping_method';
                    $result['update_section'] = array(
                        'name' => 'shipping-method',
                        'html' => $this->_getShippingMethodsHtml()
                    );
                }
            }

           

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    
}
