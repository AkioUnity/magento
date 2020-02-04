<?php
require_once(Mage::getBaseDir().DS.'lib'.DS.'fedex'.DS.'library'.DS.'fedex-common.php');

class Biztech_Fedex_HalController extends Mage_Core_Controller_Front_Action{
	public function locationsAction(){


        


		$params = $this->getRequest()->getParams();
		
		$countryId = $params['countryId'];
		$regionId = $params['regionId'];
		$postcode = $params['postcode'];

		$regionCode = Mage::getModel('directory/region')->load($regionId)->getCode();



		



        $path_to_wsdl = Mage::getBaseDir().DS.'lib'.DS.'fedex'.DS.'wsdl'.DS.'LocationsService_v5.wsdl';
        ini_set("soap.wsdl_cache_enabled", "0");
        $client = new SoapClient($path_to_wsdl, array('trace' => 1)); 
        $client->__setLocation(Mage::getStoreConfig('carriers/fedex/sandbox_mode')
            ? 'https://wsbeta.fedex.com:443/web-services '
            : 'https://ws.fedex.com:443/web-services'
        );







        

        // make soap request
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
                'StateOrProvinceCode'=>$regionCode,
                'PostalCode'=>$postcode,
                'CountryCode'=>$countryId
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
            // 'ResultFilters' => 'EXCLUDE_LOCATIONS_OUTSIDE_STATE_OR_PROVINCE',
        //  'SupportedRedirectToHoldServices' => array('FEDEX_EXPRESS', 'FEDEX_GROUND', 'FEDEX_GROUND_HOME_DELIVERY'),
            'RequiredLocationAttributes' => array(
                'ACCEPTS_CASH','ALREADY_OPEN'
            ),
            'ResultsRequested' => Mage::getStoreConfig('carriers/fedex/requested_result'),
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
       

        $layout = $this->getLayout()->createBlock('core/template');

		$result["hals"] = $layout->setData(array("location"=>$response))->setTemplate('fedex/hal/locations.phtml')->toHtml();

        $this->getResponse()->setBody(json_encode($result["hals"]));
        
	}
}
