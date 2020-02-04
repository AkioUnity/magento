<?php
require_once(Mage::getBaseDir().DS.'lib'.DS.'fedex'.DS.'pdf'.DS.'html2pdf.class.php');
require_once(Mage::getBaseDir().DS.'lib'.DS.'fedex'.DS.'library'.DS.'fedex-common.php');

class Biztech_Fedex_Model_Shipping_Carrier_Fedex extends Mage_Usa_Model_Shipping_Carrier_Fedex
{   
    
    protected function _doShipmentRequest(Varien_Object $request)
    {

        $this->_prepareShipmentRequest($request);
        $result = new Varien_Object();
        $client = $this->_createShipSoapClient();
        $requestClient = $this->_formShipmentRequest($request);
        $etdType = $request->getPackageParams()->getData()['etdtype'];
        $etdSource = $request->getPackageParams()->getData()['etdSource'];
       
        $customerReference = $request->getPackageParams()->getData()['customerReference'];
        //call for Write ETD document Biztech Fedex
        $halEnable = $request->getOrderShipment()->getOrder()->getFedexHalEnable();
        if($halEnable == 1){
            $halContent = json_decode($request->getOrderShipment()->getOrder()->getFedexHalContent());
            
            $halContentArray = (array)$halContent->LocationDetail->LocationContactAndAddress->Contact;
            $locationAddressArray = (array)$halContent->LocationDetail->LocationContactAndAddress->Address;
            $halContentPhone = $halContent->LocationDetail->LocationContactAndAddress->Contact->PhoneNumber;
            $requestClient['RequestedShipment']['SpecialServicesRequested'] = array(
                'SpecialServiceTypes' => array(
                    '0' => 'HOLD_AT_LOCATION',
                ),
                'HoldAtLocationDetail' => array(
                    'LocationContactAndAddress' => array(
                        'Contact' => $halContentArray,
                        'Address' => $locationAddressArray
                    ),
                    'PhoneNumber' => $halContentPhone
                )
            );
        }
        $IsDangerousShipment = $request->getOrderShipment()->getOrder()->getIsDangerousGoods();
      

        if($IsDangerousShipment == 1){
            
            $requestClient['RequestedShipment']['RequestedPackageLineItems']['SpecialServicesRequested']['SpecialServiceTypes'][] = 'DANGEROUS_GOODS';

            $requestClient['RequestedShipment']['RequestedPackageLineItems']['SpecialServicesRequested']['DangerousGoodsDetail'] = 'test goods';

            $requestClient['RequestedShipment']['RequestedPackageLineItems']['SpecialServicesRequested']['DangerousGoodsDetail'] = array(
                'Accessibility' => 'INACCESSIBLE',
                'CargoAircraftOnly' => 'test',
                'Options' => 'HAZARDOUS_MATERIALS',
                'PackingOption' => 'OVERPACK',
                'ReferenceID' => 'test-ref-11',
                'Containers' => 'test-contaimer'
            );
        }
            
        $IsAlchohol = $request->getOrderShipment()->getOrder()->getIsAlchohol();
        if($IsAlchohol == 1){
            $requestClient['RequestedShipment']['RequestedPackageLineItems']['SpecialServicesRequested']['SpecialServiceTypes'][] = 'ALCOHOL';  
        }
       /* $DryIce = $request->getOrderShipment()->getOrder()->getDryIce();
        if($DryIce == 1){
            $requestClient['RequestedShipment']['RequestedPackageLineItems']['SpecialServicesRequested']['SpecialServiceTypes'][] = 'DRY_ICE';
            $requestClient['RequestedShipment']['RequestedPackageLineItems']['SpecialServicesRequested']['DryIceWeight'] = '5';
        }*/
        
        /*$DryIce = $request->getOrderShipment()->getOrder()->getDryIce();
        if($DryIce == 1){
            $requestClient['RequestedShipment']['RequestedPackageLineItems']['SpecialServicesRequested']['SpecialServiceTypes'][] = 'DRY_ICE';  
            $requestClient['RequestedShipment']['RequestedPackageLineItems']['SpecialServicesRequested']['DryIceWeight'] = array(
                    'Weight' => 10,
                    'Unit' => 'KG'
                );
        }*/
        


        /*if($etdType != ""){
            $requestClient['RequestedShipment']['RequestedPackageLineItems']['SpecialServicesRequested']['SpecialServiceTypes'][] = 'ELECTRONIC_TRADE_DOCUMENTS';  
        }*/



        if(($etdType != "") && ($etdSource != "")){
            
            if($etdSource == 'auto'){
                if($etdType == 'COMMERCIAL_INVOICE'){
                    $requestClient['RequestedShipment']['SpecialServicesRequested'] = array(
                        'SpecialServiceTypes' => 'ELECTRONIC_TRADE_DOCUMENTS',
                        'EtdDetail' => array(
                        'RequestedDocumentCopies' => 'COMMERCIAL_INVOICE'
                        )
                    );
                    $requestClient['RequestedShipment']['ShippingDocumentSpecification'] = array(
                        'ShippingDocumentTypes' => 'COMMERCIAL_INVOICE',
                        'CommercialInvoiceDetail' => array(
                            'Format' => array(
                                'ImageType' => 'PDF',
                                'StockType' => 'PAPER_LETTER',
                            )
                        )
                    );
                    // $requestClient['RequestedShipment']['EdtRequestType'] = 'ALL';
                }
            }
            else{


                $etdCollection  = Mage::getModel('fedex/etdtype')->getCollection();
                foreach($etdCollection as $etdCl){
                    if($etdType == $etdCl->getName()){
                        $etdContent = Mage::getModel('fedex/etdtype')->load($etdCl->getId())->getContent();
                    }
                }
                $fileDir = $this->makeEtdPdf($request->getOrderShipment()->getOrder()->getIncrementId(),$etdContent);
                $fileName = 'etd-'.$request->getOrderShipment()->getOrder()->getIncrementId().'.pdf';
                $documentId = $this->uploadEtdDocument($fileDir,$fileName,$etdType);
                
                $requestClient['RequestedShipment']['SpecialServicesRequested']['SpecialServiceTypes'][] = 'ELECTRONIC_TRADE_DOCUMENTS';

                $requestClient['RequestedShipment']['SpecialServicesRequested']['EtdDetail'] = array(
                    'DocumentReferences' =>
                    array('DocumentType' => $etdType,
                        'DocumentId' => $documentId,
                        'LineNumber' => '2',
                        'CustomerReference' => 'refId-2'),
                );

                $requestClient['RequestedShipment']['SpecialServicesRequested']['EMailNotificationDetail'] = array(
                        'AggregationType' => 'PER_SHIPMENT',
                        'PersonalMessage' => '',
                        'Recipients' => array(
                            'EMailNotificationRecipientType' => 'RECIPIENT',
                            'EMailAddress' => 'test@gmail.com',
                            'NotificationEventsRequested' => 'ON_SHIPMENT',
                            'Format' => 'HTML',
                            'Localization' => array(
                                'LanguageCode' => '',
                                'LocaleCode' => 'US'
                            ),
                        )
                );



            }

        }



        /*$createReturnLabel = true;
        if($createReturnLabel == true){
            $returnRequestClient = $requestClient;
           
            $returnRequestClient['RequestedShipment']['SpecialServicesRequested'] = array(
                'SpecialServiceTypes' => array('RETURN_SHIPMENT'),
                'ReturnShipmentDetail' => array(
                     'ReturnType' => 'PRINT_RETURN_LABEL',
                )
            );
            $responseReturn = $client->processShipment($returnRequestClient);
            
            $ReturnShippingLabelContent = $responseReturn->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image;

            $result->setReturnShippingLabel($ReturnShippingLabelContent);
            
        }*/

        // address validatuion in backend

        $addressErrorAdmin = $this->checkAddressForAdmin($requestClient);

        if($addressErrorAdmin == true){
             $debugData = array(
                'request' => $client->__getLastRequest(),
                'result' => array(
                    'error' => '',
                    'code' => '',
                    'xml' => $client->__getLastResponse()
                )
            );
            
            // $debugData['result']['code'] = $response->Notifications->Code . ' ';
            $debugData['result']['error'] = 'Please Correct Shipping Address';
            
            $this->_debug($debugData);
            $result->setErrors($debugData['result']['error']);
        }
        else{


            /*echo "<pre>";
            print_r($requestClient);*/
            $response = $client->processShipment($requestClient);
           
            


            if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR') {
                $shippingLabelContent = $response->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image;
                $trackingNumber = $response->CompletedShipmentDetail->CompletedPackageDetails->TrackingIds->TrackingNumber;
                $result->setShippingLabelContent($shippingLabelContent);




                if(($etdType != "") && ($etdSource != "")){
                    if($etdSource == 'auto'){
                        $result->setEtdLabelContent($response->CompletedShipmentDetail->ShipmentDocuments->Parts->Image);
                    }
                    else{
                        $result->setEtdLabelContent(file_get_contents($fileDir));
                    }
                }
                $result->setTrackingNumber($trackingNumber);
                $debugData = array('request' => $client->__getLastRequest(), 'result' => $client->__getLastResponse());
                $this->_debug($debugData);
            } else {
                $debugData = array(
                    'request' => $client->__getLastRequest(),
                    'result' => array(
                        'error' => '',
                        'code' => '',
                        'xml' => $client->__getLastResponse()
                    )
                );
                if (is_array($response->Notifications)) {
                    foreach ($response->Notifications as $notification) {
                        $debugData['result']['code'] .= $notification->Code . '; ';
                        $debugData['result']['error'] .= $notification->Message . '; ';
                    }
                } else {
                    $debugData['result']['code'] = $response->Notifications->Code . ' ';
                    $debugData['result']['error'] = $response->Notifications->Message . ' ';
                }
                $this->_debug($debugData);
                $result->setErrors($debugData['result']['error']);
            } 
        }


        

        $result->setGatewayResponse($client->__getLastResponse());
        
        return $result;
    }
    public function checkAddressForAdmin($requestClient){

       


        $path_to_wsdl = Mage::getBaseDir().DS.'lib'.DS.'fedex'.DS.'wsdl'.DS.'AddressValidationService_v4.wsdl';
        ini_set("soap.wsdl_cache_enabled", "0");
        $client = new SoapClient($path_to_wsdl, array('trace' => 1)); 
        $client->__setLocation($this->getConfigFlag('sandbox_mode')
            ? 'https://wsbeta.fedex.com:443/web-services '
            : 'https://ws.fedex.com:443/web-services'
        );


        

        //make soap request to api
        $request['WebAuthenticationDetail'] = array(
            'UserCredential' => array(
                'Key' => $this->getConfigData('key'), 
                'Password' => $this->getConfigData('password')
            )
        );
        $request['ClientDetail'] = array(
            'AccountNumber' => $this->getConfigData('account'), 
            'MeterNumber' => $this->getConfigData('meter_number')
        );
        $request['TransactionDetail'] = array('CustomerTransactionId' => ' *** Address Validation Request using PHP ***');
        $request['Version'] = array(
            'ServiceId' => 'aval', 
            'Major' => '4', 
            'Intermediate' => '0', 
            'Minor' => '0'
        );
        $CityToValidate = $requestClient['RequestedShipment']['Recipient']['Address']['City'];
        $StateOrProvinceCodeToValidate = $requestClient['RequestedShipment']['Recipient']['Address']['StateOrProvinceCode'];
        $PostalCodeToValidate = $requestClient['RequestedShipment']['Recipient']['Address']['PostalCode'];
        $CountryCodeToValidate = $requestClient['RequestedShipment']['Recipient']['Address']['CountryCode'];
        $request['AddressesToValidate'] = array(
            0 => array(
                'ClientReferenceId' => 'ClientReferenceId1',
                'Address' => array(
                    'StreetLines' => array('100 NICKERSON RD'),
                    'PostalCode' => $PostalCodeToValidate,
                    'City' => $CityToValidate,
                    'StateOrProvinceCode' => $StateOrProvinceCodeToValidate,
                    'CountryCode' => $CountryCodeToValidate
                )
            ),
        );

        
        $response = $client ->addressValidation($request);


        $addressError  = false;


        $resCity = $response->AddressResults->EffectiveAddress->City;

        $resStateOrProvinceCode = $response->AddressResults->EffectiveAddress->StateOrProvinceCode;
        $resPostalCode = $response->AddressResults->EffectiveAddress->PostalCode;
        $resCountryCode = $response->AddressResults->EffectiveAddress->CountryCode;

        /*if($resCity != $CityToValidate){
            $addressError = true;
        }*/


        if(($resCity != "") && ($resCity !=null)){
            if(strcasecmp($resCity,$CityToValidate) != 0){
                $addressError = true;
            }
        }

        if(($resStateOrProvinceCode != "") && ($resStateOrProvinceCode !=null)){
            if($resStateOrProvinceCode != $StateOrProvinceCodeToValidate){
                $addressError = true;
            }
        }

        if(($resPostalCode != "") && ($resPostalCode !=null)){
            if($resPostalCode != $PostalCodeToValidate){
                $addressError = true;
            }
        }
        
        if(($resCountryCode != "") && ($resCountryCode !=null)){
            if($resCountryCode != $CountryCodeToValidate){
                $addressError = true;
            }
        }

       /* if(!Mage::getStoreConfig('carriers/fedex/enable_addressvalidationadmin')){
            $addressError = false;
        }*/

        
        return $addressError;
    }
    protected function _doRatesRequest($purpose)
    {
        
        $ratesRequest = $this->_formRateRequest($purpose);
        $requestString = serialize($ratesRequest);
        $response = $this->_getCachedQuotes($requestString);
        $debugData = array('request' => $ratesRequest);
        if ($response === null) {
            try {
                $client = $this->_createRateSoapClient();



                

                $response = $client->getRates($ratesRequest);
                /*Biztech Fedex*/
                //$locationRes = $this->getLocations();
                //$response->fedex_location = $locationRes;
                
                $this->_setCachedQuotes($requestString, serialize($response));
                $debugData['result'] = $response;
            } catch (Exception $e) {
                $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
                Mage::logException($e);
            }
        } else {
            $response = unserialize($response);
            $debugData['result'] = $response;
        }
        $this->_debug($debugData);
        return $response;
    }
    protected function _getXMLTracking($tracking)
    {

        



        $path_to_wsdl = Mage::getBaseDir().DS.'lib'.DS.'fedex'.DS.'wsdl'.DS.'TrackService_v12.wsdl';
        ini_set("soap.wsdl_cache_enabled", "0");
        $clientTrack = new SoapClient($path_to_wsdl, array('trace' => 1)); 
        $clientTrack->__setLocation($this->getConfigFlag('sandbox_mode')
            ? 'https://wsbeta.fedex.com:443/web-services '
            : 'https://ws.fedex.com:443/web-services'
        );



        

        //make soap request to api
        $trackRequest['WebAuthenticationDetail'] = array(
            'UserCredential' => array(
                'Key' => $this->getConfigData('key'), 
                'Password' => $this->getConfigData('password')
            )
        );
        $trackRequest['ClientDetail'] = array(
            'AccountNumber' => $this->getConfigData('account'), 
            'MeterNumber' => $this->getConfigData('meter_number')
        );


        


        $trackRequest['TransactionDetail'] = array('CustomerTransactionId' => '*** Track Request using PHP ***');
        $trackRequest['Version'] = array(
            'ServiceId' => 'trck', 
            'Major' => '12', 
            'Intermediate' => '0', 
            'Minor' => '0'
        );
        $trackRequest['SelectionDetails'] = array(
            'PackageIdentifier' => array(
                'Type' => 'TRACKING_NUMBER_OR_DOORTAG',
                'Value' => $tracking // Replace 'XXX' with a valid tracking identifier
            )
        );

        $requestString = serialize($trackRequest);
        $response = $this->_getCachedQuotes($requestString);
        $debugData = array('request' => $trackRequest);


        try {
            /*if($clientTrack->setEndpoint('changeEndpoint')){
                $newLocation = $clientTrack->__setLocation($clientTrack->setEndpoint('endpoint'));
            }*/
            


            
            


            $trackResponse = $clientTrack ->track($trackRequest);

            /*Biztech Fedex*/
            if(Mage::getStoreConfig('carriers/fedex/enable_spod')){
                $spodFile = $this->retriveSpod($tracking);
            }

            

            $trackResponse->CompletedTrackDetails->TrackDetails->spodFile = $spodFile;
            /*Biztech Fedex*/



            if ($trackResponse -> HighestSeverity != 'FAILURE' && $trackResponse -> HighestSeverity != 'ERROR'){
                if($trackResponse->HighestSeverity != 'SUCCESS'){
                    
                }else{
                    if ($trackResponse->CompletedTrackDetails->HighestSeverity != 'SUCCESS'){
                       
                    }else{

                       
                    }
                }
                
            }else{
                
            } 
            
            
        } catch (SoapFault $exception) {
            //printFault($exception, $client);
        }




        

        $this->_parseTrackingResponse($tracking, $trackResponse);
    }
    protected function _parseTrackingResponse($trackingValue, $response)
    {

        if (is_object($response)) {
           
            if ($response->HighestSeverity == 'FAILURE' || $response->HighestSeverity == 'ERROR') {
                
            } elseif (isset($response->CompletedTrackDetails->TrackDetails)) {

                $trackInfo = $response->CompletedTrackDetails->TrackDetails;
                /*echo "<pre>";
                print_r($trackInfo->Notification->Severity);
                die;*/
                if($trackInfo->Notification->Severity == 'FAILURE' || $trackInfo->Notification->Severity == 'ERROR'){
                    
                    $errorTitle = (string)$response->Notifications->Message;
                }else{

                    //$resultArray['status'] = (string)$trackInfo->StatusDescription;
                    $resultArray['service'] = (string)$trackInfo->Service->Description;
                    $resultArray['spodfile'] = (string)$trackInfo->spodFile;
                    $resultArray['datesortimes'] = $trackInfo->DatesOrTimes;
                }

            }
        }

        if (!$this->_result) {
            $this->_result = Mage::getModel('shipping/tracking_result');
        }


        

        if (isset($resultArray)) {
            $tracking = Mage::getModel('shipping/tracking_result_status');
            $tracking->setCarrier('fedex');
            $tracking->setCarrierTitle($this->getConfigData('title'));
            $tracking->setTracking($trackingValue);



            $tracking->addData($resultArray);
            $this->_result->append($tracking);
        } else {
           $error = Mage::getModel('shipping/tracking_result_error');
           $error->setCarrier('fedex');
           $error->setCarrierTitle($this->getConfigData('title'));
           $error->setTracking($trackingValue);
           $error->setErrorMessage($errorTitle ? $errorTitle : Mage::helper('usa')->__('Unable to retrieve tracking'));
           $this->_result->append($error);
        }
    }
    protected function _formShipmentRequest(Varien_Object $request)
    {

        if ($request->getReferenceData()) {
            $referenceData = $request->getReferenceData() . $request->getPackageId();
        } else {
            $referenceData = 'Order #'
                             . $request->getOrderShipment()->getOrder()->getIncrementId()
                             . ' P'
                             . $request->getPackageId();
        }
        $packageParams = $request->getPackageParams();


        // Biztech Fedex
        $customerReference = $packageParams->getData()['customerReference'];
        $labelType = $packageParams->getData()['labelType'];

        

        $customsValue = $packageParams->getCustomsValue();
        $height = $packageParams->getHeight();
        $width = $packageParams->getWidth();
        $length = $packageParams->getLength();
        $weightUnits = $packageParams->getWeightUnits() == Zend_Measure_Weight::POUND ? 'LB' : 'KG';
        $dimensionsUnits = $packageParams->getDimensionUnits() == Zend_Measure_Length::INCH ? 'IN' : 'CM';
        $unitPrice = 0;
        $itemsQty = 0;
        $itemsDesc = array();
        $countriesOfManufacture = array();
        $productIds = array();
        $packageItems = $request->getPackageItems();
        foreach ($packageItems as $itemShipment) {
                $item = new Varien_Object();
                $item->setData($itemShipment);

                $unitPrice  += $item->getPrice();
                $itemsQty   += $item->getQty();

                $itemsDesc[]    = $item->getName();
                $productIds[]   = $item->getProductId();
        }

        // get countries of manufacture
        $productCollection = Mage::getResourceModel('catalog/product_collection')
            ->addStoreFilter($request->getStoreId())
            ->addFieldToFilter('entity_id', array('in' => $productIds))
            ->addAttributeToSelect('country_of_manufacture');
        foreach ($productCollection as $product) {
            $countriesOfManufacture[] = $product->getCountryOfManufacture();
        }

        $paymentType = $request->getIsReturn() ? 'RECIPIENT' : 'SENDER';

       


        $requestClient = array(
            'RequestedShipment' => array(
                'ShipTimestamp' => time(),
                'DropoffType'   => $this->getConfigData('dropoff'),
                'PackagingType' => $request->getPackagingType(),
                'ServiceType' => $request->getShippingMethod(),
                'Shipper' => array(
                    'Contact' => array(
                        'PersonName' => $request->getShipperContactPersonName(),
                        'CompanyName' => $request->getShipperContactCompanyName(),
                        'PhoneNumber' => $request->getShipperContactPhoneNumber()
                    ),
                    'Address' => array(
                        'StreetLines' => array(
                            $request->getShipperAddressStreet1(),
                            $request->getShipperAddressStreet2()
                        ),
                        'City' => $request->getShipperAddressCity(),
                        'StateOrProvinceCode' => $request->getShipperAddressStateOrProvinceCode(),
                        'PostalCode' => $request->getShipperAddressPostalCode(),
                        'CountryCode' => $request->getShipperAddressCountryCode()
                    )
                ),
                'Recipient' => array(
                    'Contact' => array(
                        'PersonName' => $request->getRecipientContactPersonName(),
                        'CompanyName' => $request->getRecipientContactCompanyName(),
                        'PhoneNumber' => $request->getRecipientContactPhoneNumber()
                    ),
                    'Address' => array(
                        'StreetLines' => array(
                            $request->getRecipientAddressStreet1(),
                            $request->getRecipientAddressStreet2()
                        ),
                        'City' => $request->getRecipientAddressCity(),
                        'StateOrProvinceCode' => $request->getRecipientAddressStateOrProvinceCode(),
                        'PostalCode' => $request->getRecipientAddressPostalCode(),
                        'CountryCode' => $request->getRecipientAddressCountryCode(),
                        'Residential' => (bool)$this->getConfigData('residence_delivery')
                    ),
                ),
                'ShippingChargesPayment' => array(
                    'PaymentType' => $paymentType,
                    'Payor' => array(
                        'AccountNumber' => $this->getConfigData('account'),
                        'CountryCode'   => Mage::getStoreConfig(
                            Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID,
                            $request->getStoreId()
                        )
                    )
                ),
                'LabelSpecification' =>array(
                    'LabelFormatType' => 'COMMON2D',
                    'ImageType' => 'PNG',
                    'LabelStockType' => $labelType,
                ),
                'RateRequestTypes'  => array('ACCOUNT'),
                'PackageCount'      => 1,
                'RequestedPackageLineItems' => array(
                    'SequenceNumber' => '1',
                    'Weight' => array(
                        'Units' => $weightUnits,
                        'Value' =>  $request->getPackageWeight()
                    ),
                    'CustomerReferences' => array(
                        'CustomerReferenceType' => 'CUSTOMER_REFERENCE',
                        'Value' => $customerReference
                    ),
                    'SpecialServicesRequested' => array(
                        'SpecialServiceTypes' => array('SIGNATURE_OPTION'),
                        'SignatureOptionDetail' => array('OptionType' => $packageParams->getDeliveryConfirmation())
                    ),
                    
                )
            )
        );

        // for international shipping
        if ($request->getShipperAddressCountryCode() != $request->getRecipientAddressCountryCode()) {
            $requestClient['RequestedShipment']['CustomsClearanceDetail'] =
                array(
                    'CustomsValue' =>
                    array(
                        'Currency' => $request->getBaseCurrencyCode(),
                        'Amount' => $customsValue,
                    ),
                    'DutiesPayment' => array(
                        'PaymentType' => $paymentType,
                        'Payor' => array(
                            'AccountNumber' => $this->getConfigData('account'),
                            'CountryCode'   => Mage::getStoreConfig(
                                Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID,
                                $request->getStoreId()
                            )
                        )
                    ),
                    'Commodities' => array(
                        'Weight' => array(
                            'Units' => $weightUnits,
                            'Value' =>  $request->getPackageWeight()
                        ),
                        'NumberOfPieces' => 1,
                        'CountryOfManufacture' => implode(',', array_unique($countriesOfManufacture)),
                        'Description' => implode(', ', $itemsDesc),
                        'Quantity' => ceil($itemsQty),
                        'QuantityUnits' => 'pcs',
                        'UnitPrice' => array(
                            'Currency' => $request->getBaseCurrencyCode(),
                            'Amount' =>  $unitPrice
                        ),
                        'CustomsValue' => array(
                            'Currency' => $request->getBaseCurrencyCode(),
                            'Amount' =>  $customsValue
                        ),
                    )
                );
        }

        if ($request->getMasterTrackingId()) {
            $requestClient['RequestedShipment']['MasterTrackingId'] = $request->getMasterTrackingId();
        }

        // set dimensions
        if ($length || $width || $height) {
            $requestClient['RequestedShipment']['RequestedPackageLineItems']['Dimensions'] = array();
            $dimenssions = &$requestClient['RequestedShipment']['RequestedPackageLineItems']['Dimensions'];
            $dimenssions['Length'] = $length;
            $dimenssions['Width']  = $width;
            $dimenssions['Height'] = $height;
            $dimenssions['Units'] = $dimensionsUnits;
        }

        return $this->_getAuthDetails() + $requestClient;
    }
    public function makeEtdPdf($orderIncrementId,$makeEtdPdf){
        $content = $makeEtdPdf.$orderIncrementId;
        $size = 'A1';
        $etd_pdf_dir = Mage::getBaseDir('media') .DS .'fedex'.DS.'etd';
        $etd_pdf_file = Mage::getBaseDir('media') .DS .'fedex'.DS.'etd'.DS. 'etd-'.$orderIncrementId.'.pdf';
        if(!file_exists($etd_pdf_dir)){
            mkdir($etd_pdf_dir, 0777, true);
        }



        $name = $this->createPdf($content,$etd_pdf_file,$size);
        return $name;
    }
    public function uploadEtdDocument($fileDir, $fileName, $etdtype){



        $etdCollection  = Mage::getModel('fedex/etdtype')->getCollection();
        foreach($etdCollection as $etdCl){
            if($etdtype == $etdCl->getName()){
                $etdContent = Mage::getModel('fedex/etdtype')->load($etdCl->getId())->getContent();

            }
        }



        

        

        $path_to_wsdl = Mage::getBaseDir().DS.'lib'.DS.'fedex'.DS.'wsdl'.DS.'UploadDocumentService_v8.wsdl';
        ini_set("soap.wsdl_cache_enabled", "0");
        $client = new SoapClient($path_to_wsdl, array('trace' => 1)); 
        $client->__setLocation($this->getConfigFlag('sandbox_mode')
            ? 'https://wsbeta.fedex.com:443/web-services '
            : 'https://ws.fedex.com:443/web-services'
        );




        

        //make soap request to api
        
        $request['WebAuthenticationDetail'] = array(
            'UserCredential' => array(
                'Key' => $this->getConfigData('key'), 
                'Password' => $this->getConfigData('password')
            )
        );
        $request['ClientDetail'] = array(
            'AccountNumber' => $this->getConfigData('account'), 
            'MeterNumber' => $this->getConfigData('meter_number')
        );


        $request['TransactionDetail'] = array('CustomerTransactionId' => '*** Upload Documents Request using PHP ***');
        $request['Version'] = array(
            'ServiceId' => 'cdus', 
            'Major' => '8', 
            'Intermediate' => '0', 
            'Minor' => '0'
        );
        $request['OriginCountryCode'] = 'US';  
        $request['DestinationCountryCode'] = 'CA'; 
        $request['DocumentUsageType'] = array(
            '0' => 'CUSTOMER_INFORMATION',
            '1' => 'ELECTRONIC_TRADE_DOCUMENTS'
        ); 
        $request['Documents'] = array(
            '0' => array (
                'LineNumber' => '1', 
                'CustomerReference' => 'refId-1',
                'DocumentType' => $etdtype, 
                'FileName' => $fileName,
                'DocumentContent' => stream_get_contents(fopen($fileDir, "r"))
            )
        );  
        try {
            
            $response = $client->uploadDocuments($request);



            if ($response -> HighestSeverity != 'FAILURE' && $response -> HighestSeverity != 'ERROR'){
                return $response->DocumentStatuses->DocumentId;    
            } 
        } catch (SoapFault $exception) {
            
        }
    }
    public function createPdf($content,$name,$size)
    {   
        $html2pdf = new HTML2PDF('P',$size,'en');
        $html2pdf->WriteHTML($content);
        $html2pdf->Output($name,'F');
        return $name;
    }
    public function retriveSpod($tracking){
        



        $path_to_wsdl = Mage::getBaseDir().DS.'lib'.DS.'fedex'.DS.'wsdl'.DS.'TrackService_v12.wsdl';
        ini_set("soap.wsdl_cache_enabled", "0");
        $client = new SoapClient($path_to_wsdl, array('trace' => 1)); 
        $client->__setLocation($this->getConfigFlag('sandbox_mode')
            ? 'https://wsbeta.fedex.com:443/web-services '
            : 'https://ws.fedex.com:443/web-services'
        );





        


        //make soap request to api
        $request['WebAuthenticationDetail'] = array(
            'UserCredential' => array(
                'Key' => $this->getConfigData('key'), 
                'Password' => $this->getConfigData('password')
            )
        );
        $request['ClientDetail'] = array(
            'AccountNumber' => $this->getConfigData('account'), 
            'MeterNumber' => $this->getConfigData('meter_number')
        );


        $request['TransactionDetail'] = array(
            'CustomerTransactionId' => '*** SPOD Request using PHP ***',
            'Localization' => array(
                'LanguageCode'=>'EN'
            )
        );
        $request['Version'] = array(
            'ServiceId' => 'trck', 
            'Major' => '12', 
            'Intermediate' => '0', 
            'Minor' => '0'
        );

        /*Request Elements*/

        $request['QualifiedTrackingNumber'] = array (
            'TrackingNumber' => $tracking// Replace 'XXX' with actual tracking number
        );
        $request['AdditionalComments'] = 'NONE';
        $request['LetterFormat'] = 'PDF';  
        /*$request['Consignee'] = array(
            'Contact' => array(
                'PersonName' => 'John Smith',
                'CompanyName' => 'Company Name',
                'PhoneNumber' => '4075551212'
            ),
            'Address' => array(
                'StreetLines' => array('123 S. Main St'),
                'City' => 'Lake Mary',
                'StateOrProvinceCode' => 'FL',
                'PostalCode' => '32746',
                'CountryCode' => 'US'
            )
        ); */
        
        
        $response = $client->retrieveSignatureProofOfDeliveryLetter($request);

        $spod_pdf_dir = Mage::getBaseDir('media') .DS .'fedex'.DS.'spod';        
        if(!file_exists($spod_pdf_dir)){
            mkdir($spod_pdf_dir, 0777, true);
        }
        $spod_pdf_file = Mage::getBaseDir('media') .DS .'fedex'.DS.'spod'.DS. 'spod-'.$tracking.'.pdf';
        $spod_pdf_url = Mage::getBaseUrl('media') .DS .'fedex'.DS.'spod'.DS. 'spod-'.$tracking.'.pdf';

        if(file_exists($spod_pdf_file)){
            unlink($spod_pdf_file);
        }


        file_put_contents($spod_pdf_file, $response->Letter);
        return $spod_pdf_url;
    }
    public function getLocations(){

        



        $path_to_wsdl = Mage::getBaseDir().DS.'lib'.DS.'fedex'.DS.'wsdl'.DS.'LocationsService_v5.wsdl';
        ini_set("soap.wsdl_cache_enabled", "0");
        $client = new SoapClient($path_to_wsdl, array('trace' => 1)); 
        $client->__setLocation($this->getConfigFlag('sandbox_mode')
            ? 'https://wsbeta.fedex.com:443/web-services '
            : 'https://ws.fedex.com:443/web-services'
        );



        // make soap request
        $request['WebAuthenticationDetail'] = array(
            'UserCredential' => array(
                'Key' => $this->getConfigData('key'), 
                'Password' => $this->getConfigData('password')
            )
        );
        $request['ClientDetail'] = array(
            'AccountNumber' => $this->getConfigData('account'), 
            'MeterNumber' => $this->getConfigData('meter_number')
        );
        $request['TransactionDetail'] = array('CustomerTransactionId' => '*** Search Locations Request using PHP ***');
        $request['Version'] = array(
            'ServiceId' => 'locs', 
            'Major' => '5', 
            'Intermediate' => '0', 
            'Minor' => '0'
        );

        // make request element

        $request['EffectiveDate'] = date('Y-m-d');
        $bNearToPhoneNumber = false;
        if ($bNearToPhoneNumber){
            $request['LocationsSearchCriterion'] = 'PHONE_NUMBER';
            $request['PhoneNumber'] = '555555555'; // Replace 'XXX' with phone number
        }else{
            $request['LocationsSearchCriterion'] = 'ADDRESS';
            $request['Address'] = array(
                'StreetLines'=> array('240 Central Park S'),
                'City'=>'Austin',
                'StateOrProvinceCode'=>'TX',
                'PostalCode'=>'78701',
                'CountryCode'=>'US'
            );
        }

        $request['MultipleMatchesAction'] = 'RETURN_ALL';
        $request['SortDetail'] = array(
            'Criterion' => 'DISTANCE',
            'Order' => 'LOWEST_TO_HIGHEST'
        );
        $request['Constraints'] = array(
            'RadiusDistance' => array(
                'Value' => 15.0,
                'Units' => 'KM'
            ),
            'ExpressDropOfTimeNeeded' => '15:00:00.00',
            'ResultFilters' => 'EXCLUDE_LOCATIONS_OUTSIDE_STATE_OR_PROVINCE',
        //  'SupportedRedirectToHoldServices' => array('FEDEX_EXPRESS', 'FEDEX_GROUND', 'FEDEX_GROUND_HOME_DELIVERY'),
            'RequiredLocationAttributes' => array(
                'ACCEPTS_CASH','ALREADY_OPEN'
            ),
            'ResultsRequested' => 1,
        //  'LocationContentOptions' => array('HOLIDAYS'),
            'LocationTypesToInclude' => array('FEDEX_OFFICE')
        );

        $request['DropoffServicesDesired'] = array(
            'Express' => 1, // Location desired services
            'FedExStaffed' => 1,
            'FedExSelfService' => 1,
            'FedExAuthorizedShippingCenter' => 1,
            'HoldAtLocation' => 1
        );

        
        $response = $client->searchLocations($request);

        return $response;
    }
}
