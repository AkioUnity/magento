<?php
require_once(Mage::getBaseDir().DS.'lib'.DS.'fedex'.DS.'library'.DS.'fedex-common.php');
class Biztech_Fedex_TrackController extends Mage_Core_Controller_Front_Action{
	public function indexAction(){
		$this->loadLayout();
		$this->renderLayout();
	}
	public function trackbyrefAction(){

		$params = $this->getRequest()->getParams();
		$referenceNumber = $params['refText'];


		$path_to_wsdl = Mage::getBaseDir().DS.'lib'.DS.'fedex'.DS.'wsdl'.DS.'TrackService_v12.wsdl';
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



        $request['TransactionDetail'] = array('CustomerTransactionId' => '*** Track Request using PHP ***');
		$request['Version'] = array(
			'ServiceId' => 'trck', 
			'Major' => '12', 
			'Intermediate' => '0', 
			'Minor' => '0'
		);
		$request['SelectionDetails'] = array(
			'PackageIdentifier' => array(
				'Type' => 'CUSTOMER_REFERENCE',
				'Value' => $referenceNumber // Replace with a valid customer reference
			),
			'ShipmentAccountNumber' => Mage::getStoreConfig('carriers/fedex/account') // Replace with account used 
		);
		try {
			
			$response = $client->track($request);
			$layout = $this->getLayout()->createBlock('core/template');
			$result["trackings"] = $layout->setData(array("track_data"=>$response))->setTemplate('fedex/trackdetails.phtml')->toHtml();
			$this->getResponse()->setBody(json_encode($result["trackings"]));


			
		    
		    
		} catch (SoapFault $exception) {
		    print_r($exception);
		}




	}
}
