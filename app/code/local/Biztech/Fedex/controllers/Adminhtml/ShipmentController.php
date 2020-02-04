<?php
require_once(Mage::getBaseDir().DS.'lib'.DS.'fedex'.DS.'library'.DS.'fedex-common.php');

class Biztech_Fedex_Adminhtml_ShipmentController extends Mage_Adminhtml_Controller_Action
{	
	public function masscancelpickupAction()
	{
		$params = $this->getRequest()->getParams();
		foreach($params['shipment_ids'] as $param)
		{
			$shipmentId = $param;
			$result = $this->cancelpickup($shipmentId);
			if($result['status'] == 'success')
			{
				$pShipmentModel = Mage::getModel("fedex/pickup");
				$pShipmentCol = $pShipmentModel->getCollection()->addFieldToFilter('shipment_id',array('eq'=>$shipmentId));
				$pickupShipmentId = $pShipmentCol->getFirstItem()->getPickupId();
				Mage::getModel("fedex/pickup")->load($pickupShipmentId)
					->setStatus(0)
					->save();
			}else
			{
				Mage::getSingleton("adminhtml/session")->addError($result['message']);	
			}
			$this->_redirect("adminhtml/sales_shipment/index");
			//$this->_redirect("adminhtml/pickup/index");


		}
		Mage::getSingleton("adminhtml/session")->addSuccess($result['message']);	
	}

	public function masspickupAction()
	{
		$params = $this->getRequest()->getParams();
		foreach($params['shipment_ids'] as $param)
		{
			$shipmentId = $param;
			//$shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
			$result = $this->createPickup($shipmentId);

			if($result['status'] == 'success')
			{

				Mage::getSingleton("adminhtml/session")->addSuccess($result['message']);	
				Mage::getSingleton("adminhtml/session")->addSuccess("Pickup Confimation No : ".$result['confnum']);

				$pShipmentModel = Mage::getModel("fedex/pickup");
				$pShipmentCol = $pShipmentModel->getCollection()->addFieldToFilter('shipment_id',array('eq'=>$shipmentId));
				
				if(count($pShipmentCol) == 0)
				{
					$data = array('shipment_id'=>$shipmentId,
					'person_name'=>Mage::getStoreConfig('general/store_information/name'),
					'company_name'=>Mage::getStoreConfig('general/store_information/name'),
					'phone_no'=>Mage::getStoreConfig('general/store_information/phone'),
					'pickup_address'=>$result,
					'pickup_city'=>Mage::getStoreConfig('shipping/origin/city'),
					'pickup_state'=>Mage::getStoreConfig('shipping/origin/region_id'),
					'pickup_postcode'=>Mage::getStoreConfig('shipping/origin/postcode'),
					'pickup_country'=>Mage::getStoreConfig('shipping/origin/country_id'),
					'package_location'=> 'FRONT',
					'building_partcode'=> 'SUITE',
					'building_part_description'=>'3B',
					'ready_timestamp'=>mktime(8, 0, 0, date("m")  , date("d"), date("Y")),
					'company_closetime'=>'20:00:00',
					'package_count'=>'1',
					'total_weight_unit'=>'LB',
					'total_weight_value'=>'1',
						'status'=>1,
						'confirmation_no'=>$result['confnum']
						); 
					$pShipmentModel = $pShipmentModel->setData($data);
					$pShipmentModel->save();
				}
				else
				{
					$pickupShipmentId = $pShipmentCol->getFirstItem()->getPickupId();
						Mage::getModel("fedex/pickup")->load($pickupShipmentId)
						->setPersonName(Mage::getStoreConfig('general/store_information/name'))
						->setCompanyName(Mage::getStoreConfig('general/store_information/name'))
						->setPhoneNo(Mage::getStoreConfig('general/store_information/phone'))
						->setPickupAddress()
						->setPickupCity(Mage::getStoreConfig('shipping/origin/city'))
						->setPickupState(Mage::getStoreConfig('shipping/origin/region_id'))
						->setPickupPostcode(Mage::getStoreConfig('shipping/origin/postcode'))
						->setPickupCountry(Mage::getStoreConfig('shipping/origin/country_id'))
						->setPackageCount('1')
						->setTotalWeightUnit('LB')
						->setTotalWeightValue('1')
						->setPackageLocation('FRONT')
						->setBuildingPartcode('SUITE')
						->setBuildingPartDescription('3B')
						->setReadyTimestamp(mktime(8, 0, 0, date("m")  , date("d"), date("Y")))
						->setCompanyClosetime('20:00:00')
						->setShipmentId($shipmentId)
						->setStatus(1)
						->setConfirmationNo($result['confnum'])
						->save();
				}
			}
			else
			{
				Mage::getSingleton("adminhtml/session")->addError($result['message']);			
			}
			$this->_redirect("adminhtml/sales_shipment/index");
			//$this->_redirect("adminhtml/pickup/index");
		}
	}

	public function cancelpickupAction()
	{
		$params = $this->getRequest()->getParams();
		$shipmentId = $params['id'];

		$result = $this->cancelpickup($shipmentId);
		if($result['status'] == 'success')
		{
			$pShipmentModel = Mage::getModel("fedex/pickup");
			$pShipmentCol = $pShipmentModel->getCollection()->addFieldToFilter('shipment_id',array('eq'=>$shipmentId));
			$pickupShipmentId = $pShipmentCol->getFirstItem()->getPickupId();
			Mage::getModel("fedex/pickup")->load($pickupShipmentId)
				->setStatus(0)
				->save();
			Mage::getSingleton("adminhtml/session")->addSuccess($result['message']);	
		}
		else
		{
			Mage::getSingleton("adminhtml/session")->addError($result['message']);	
		}
		$this->_redirect("adminhtml/sales_shipment/view",array('shipment_id'=>$shipmentId));

		//$this->_redirect("adminhtml/pickup/index");
	}
	public function cancelpickup($shipmentId)
	{
		// make pick up cancel request
        $path_to_wsdl = Mage::getBaseDir().DS.'lib'.DS.'fedex'.DS.'wsdl'.DS.'PickupService_v13.wsdl';
		ini_set("soap.wsdl_cache_enabled", "0");
        $client = new SoapClient($path_to_wsdl, array('trace' => 1)); 
        $client->__setLocation(Mage::getStoreConfig('carriers/fedex/sandbox_mode')
            ? 'https://wsbeta.fedex.com:443/web-services '
            : 'https://ws.fedex.com:443/web-services'
        );
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
		$request['TransactionDetail'] = array('CustomerTransactionId' => '*** Create Pickup Request using PHP ***');
		$request['Version'] = array(
			'ServiceId' => 'disp', 
			'Major' => 13, 
			'Intermediate' => 0, 
			'Minor' => 0
		);

		$request['CarrierCode'] = 'FDXE'; // valid values FDXE-Express, FDXG-Ground, etc
		$request['PickupConfirmationNumber'] = '10'; // Replace 'XXX' with your Pickup confirmation number
		//$request['ScheduledDate'] = $pickupReadytimestamp;
		$request['ScheduledDate'] = date("Y-m-d", mktime(8, 0, 0, date("m")  , date("d")+6, date("Y")));

		$request['Location'] = 'SQLA'; // Replace 'XXX' with your Pickip Loaction Code/ID
		$request['CourierRemarks'] = 'Do not pickup.  This is a test';
		$result = array();
		$result['status'] = 'fail';
		try{
			$response = $client->cancelPickup($request);
			/*if ($response -> HighestSeverity != 'FAILURE' && $response -> HighestSeverity != 'ERROR'){*/
				$result['status'] = 'success';
				$result['message'] = 'Pickup has been cancelled successfully.';
			/*}else{
				$result['message'] = $response->Notifications->Message;
			}*/
		}
		catch (SoapFault $e){
			$result['message'] = $e->getMessage();
		}
		return $result;

	}

	public function pickupAction()
	{
		
		$params = $this->getRequest()->getParams();
		$shipmentId = $params['id'];
		$shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
		$result = $this->createPickup($shipment);
		if($result['status'] == 'success'){
			Mage::getSingleton("adminhtml/session")->addSuccess($result['message']);	
			Mage::getSingleton("adminhtml/session")->addSuccess("Pickup Confimation No : ".$result['confnum']);
			$pShipmentModel = Mage::getModel("fedex/pickup");
			$pShipmentCol = $pShipmentModel->getCollection()->addFieldToFilter('shipment_id',array('eq'=>$shipmentId));
			if(count($pShipmentCol) == 0){
				$data = array('shipment_id'=>$shipmentId,
					'status'=>1,
					'confirmation_no'=>$result['confnum'],
					'person_name'=>Mage::getStoreConfig('general/store_information/name'),
					'company_name'=>Mage::getStoreConfig('general/store_information/name'),
					'phone_no'=>Mage::getStoreConfig('general/store_information/phone'),
					'pickup_address'=>$result,
					'pickup_city'=>Mage::getStoreConfig('shipping/origin/city'),
					'pickup_state'=>Mage::getStoreConfig('shipping/origin/region_id'),
					'pickup_postcode'=>Mage::getStoreConfig('shipping/origin/postcode'),
					'pickup_country'=>Mage::getStoreConfig('shipping/origin/country_id'),
					'package_location'=> 'FRONT',
					'building_partcode'=> 'SUITE',
					'building_part_description'=>'3B',
					'ready_timestamp'=>mktime(8, 0, 0, date("m")  , date("d"), date("Y")),
					'company_closetime'=>'20:00:00',
					'package_count'=>'1',
					'total_weight_unit'=>'LB',
					'total_weight_value'=>'1',

				); 
				$pShipmentModel = $pShipmentModel->setData($data);
				$pShipmentModel->save();
			}
			else{
				$pickupShipmentId = $pShipmentCol->getFirstItem()->getPickupId();				
				//$data = array('shipment_id'=>$shipmentId,'status'=>1,'confnum'=>$result['confnum']); 
				Mage::getModel("fedex/pickup")->load($pickupShipmentId)
				->setShipmentId($shipmentId)
				->setPersonName(Mage::getStoreConfig('general/store_information/name'))
				->setCompanyName(Mage::getStoreConfig('general/store_information/name'))
				->setPhoneNo(Mage::getStoreConfig('general/store_information/phone'))
				->setPickupAddress()
				->setPickupCity(Mage::getStoreConfig('shipping/origin/city'))
				->setPickupState(Mage::getStoreConfig('shipping/origin/region_id'))
				->setPickupPostcode(Mage::getStoreConfig('shipping/origin/postcode'))
				->setPickupCountry(Mage::getStoreConfig('shipping/origin/country_id'))
				->setPackageCount('1')
				->setTotalWeightUnit('LB')
				->setTotalWeightValue('1')
				->setPackageLocation('FRONT')
				->setBuildingPartcode('SUITE')
				->setBuildingPartDescription('3B')
				->setReadyTimestamp(mktime(8, 0, 0, date("m")  , date("d"), date("Y")))
				->setCompanyClosetime('20:00:00')
				->setStatus(1)
				->setConfirmationNo($result['confnum'])
				->save();
				//Mage::getModel("fedex/pickup")->addData($data)->setId($pickupShipmentId)->save();
			}
			//$reviewLink = $this->getUrl("adminhtml/pickup/edit", array("_secure" => $this->getRequest()->isSecure(),'id' => Mage::getModel("fedex/pickup")->getId()));
			$reviewLink = $this->getUrl("adminhtml/pickup/index", array("_secure" => $this->getRequest()->isSecure()));
			Mage::getSingleton("adminhtml/session")->addSuccess("<a href=$reviewLink> Click here to Review</a>");
		}
		else{
			Mage::getSingleton("adminhtml/session")->addError($result['message']);			
		}
		$this->_redirect("adminhtml/sales_shipment/view",array('shipment_id'=>$shipmentId));
		//$this->_redirect("adminhtml/pickup/index");
	}
	public function pickupAvail()
	{

	}

	public function createPickup($shipment)
	{

		//create pick up request after 

		$path_to_wsdl = Mage::getBaseDir().DS.'lib'.DS.'fedex'.DS.'wsdl'.DS.'PickupService_v13.wsdl';
		ini_set("soap.wsdl_cache_enabled", "0");
        $client = new SoapClient($path_to_wsdl, array('trace' => 1)); 
        $client->__setLocation(Mage::getStoreConfig('carriers/fedex/sandbox_mode')
            ? 'https://wsbeta.fedex.com:443/web-services '
            : 'https://ws.fedex.com:443/web-services'
        );
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
		$request['TransactionDetail'] = array('CustomerTransactionId' => '*** Create Pickup Request using PHP ***');
		$request['Version'] = array(
			'ServiceId' => 'disp', 
			'Major' => 13, 
			'Intermediate' => 0, 
			'Minor' => 0
		);
		$request['OriginDetail'] = array(
			'PickupLocation' => array(
				'Contact' => array(
					'PersonName' => Mage::getStoreConfig('general/store_information/name'),
		        	'CompanyName' => Mage::getStoreConfig('general/store_information/name'),
		        	'PhoneNumber' => Mage::getStoreConfig('general/store_information/phone')
		        ),
		      	'Address' => array(
		      		'StreetLines' => array('Address Line 1'),
		        	'City' => Mage::getStoreConfig('shipping/origin/city'),
		        	'StateOrProvinceCode' => Mage::getModel('directory/region')->load(Mage::getStoreConfig('shipping/origin/region_id'))->getCode(),
		         	'PostalCode' => Mage::getStoreConfig('shipping/origin/postcode'),
		         	'CountryCode' => Mage::getStoreConfig('shipping/origin/country_id'))
		       	),
		   	'PackageLocation' => 'FRONT', // valid values NONE, FRONT, REAR and SIDE
		    'BuildingPartCode' => 'SUITE', // valid values APARTMENT, BUILDING, DEPARTMENT, SUITE, FLOOR and ROOM
		    'BuildingPartDescription' => '3B',
		    'ReadyTimestamp' => mktime(8, 0, 0, date("m")  , date("d"), date("Y")), // Replace with your ready date time
		    'CompanyCloseTime' => '20:00:00'
		);
		$request['PackageCount'] = '1';
		$request['TotalWeight'] = array(
			'Value' => '1.0', 
			'Units' => 'LB' // valid values LB and KG
		); 
		$request['CarrierCode'] = 'FDXE'; // valid values FDXE-Express, FDXG-Ground, FDXC-Cargo, FXCC-Custom Critical and FXFR-Freight
		//$request['OversizePackageCount'] = '1';
		$request['CourierRemarks'] = 'This is a test.  Do not pickup';

		$result = array();
		$result['status'] = 'fail';
		try{
			$response = $client->createPickup($request);
			if($response -> HighestSeverity != 'FAILURE' && $response -> HighestSeverity != 'ERROR'){
				$result['status'] = 'success';
				$result['confnum'] = $response->PickupConfirmationNumber;
				$result['message'] = 'Pickup Created Successfully';
			}
			else{
				$result['message'] = $response->Notifications->Message;
			}
		}
		catch(SoapFault $e){
			$result['message'] = $e->getMessage();
		}
		return $result;
	}

	public function cancelAction()
	{
		$params = $this->getRequest()->getParams();
		$serviceType = $params['serviceType'];
		$trackingId = $params['trackingId'];
		$path_to_wsdl = Mage::getBaseDir().DS.'lib'.DS.'fedex'.DS.'wsdl'.DS.'ShipService_v19.wsdl';
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
		$request['TransactionDetail'] = array('CustomerTransactionId' => ' *** Cancel Shipment Request using PHP ***');
		$request['Version'] = array(
			'ServiceId' => 'ship', 
			'Major' => '19', 
			'Intermediate' => '0', 
			'Minor' => '0'
		);

		$request['ShipTimestamp'] = date('c');
		$request['TrackingId'] = array(
			'TrackingIdType' =>'GROUND', // valid values EXPRESS, GROUND, USPS, etc
		   	'TrackingNumber'=>$trackingId
		);  

		$request['DeletionControl'] = 'DELETE_ONE_PACKAGE'; // Package/Shipment
		try {
			
			$response = $client->deleteShipment($request);
			$this->getResponse()->setBody(json_encode($response));
		    
		} catch (SoapFault $exception) {
		    
		}

	}
	public function returnAction()
	{
		$params = $this->getRequest()->getParams();
		$serviceType = $params['serviceType'];
		$path_to_wsdl = Mage::getBaseDir().DS.'lib'.DS.'fedex'.DS.'wsdl'.DS.'ShipService_v19.wsdl';
		ini_set("soap.wsdl_cache_enabled", "0");
		$client = new SoapClient($path_to_wsdl, array('trace' => 1)); 
		$fedexlib = new Fedexlibrary();

		// add shipping account no
		$request['WebAuthenticationDetail'] = array(
			'ParentCredential' => array(
				'Key' => $fedexlib->getProperty('parentkey'), 
				'Password' => $fedexlib->getProperty('parentpassword')
			),
			'UserCredential' => array(
				'Key' => $fedexlib->getProperty('key'), 
				'Password' => $fedexlib->getProperty('password')
			)
		);
		$request['ClientDetail'] = array(
			'AccountNumber' => $fedexlib->getProperty('shipaccount'), 
			'MeterNumber' => $fedexlib->getProperty('meter')
		);
		$request['TransactionDetail'] = array('CustomerTransactionId' => '*** Express Call Tag Request using PHP ***');
		$request['Version'] = array(
			'ServiceId' => 'ship', 
			'Major' => '19', 
			'Intermediate' => '0', 
			'Minor' => '0'
		);
		$request['RequestedShipment']['DropoffType'] = Mage::getStoreConfig('carriers/fedex/dropoff'); // valid values REGULAR_PICKUP, REQUEST_COURIER, ...
		// add shipping type method
		$request['RequestedShipment']['ServiceType'] = $serviceType; // valid values STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_GROUND, ...
		$request['RequestedShipment']['PackagingType'] = 'YOUR_PACKAGING'; // valid values FEDEX_BOX, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
		
		$order = Mage::getModel('sales/order');
		$shipperAddress = $order->loadByIncrementId($params['orderIncrementId'])->getShippingAddress()->getData();	
		
		$orderData = $order->loadByIncrementId($params['orderIncrementId'])->getData();
		$dateFromcustomer = $orderData['return_avail_date'];
	
		// add shipper and recipient address
		$request['RequestedShipment']['Shipper'] = array(
			'Contact' => array(
				'PersonName' => $shipperAddress['firstname']." ".$shipperAddress['lastname'],
				'CompanyName' => $shipperAddress['company'],
				'PhoneNumber' => $shipperAddress['telephone']
			),
			'Address' => array(
				'StreetLines' => array($shipperAddress['street']),
				'City' => $shipperAddress['city'],
				'StateOrProvinceCode' => 'NY',
				'PostalCode' => $shipperAddress['postcode'],
				'CountryCode' => $shipperAddress['country_id'],
				'Residential' => 1
			)
		);

		// $request['RequestedShipment']['Shipper'] = $fedexlib->getProperty('shipper');

		$request['RequestedShipment']['Recipient'] = $fedexlib->getProperty('recipient');
		$request['RequestedShipment']['ShippingChargesPayment'] = $fedexlib->getProperty('shippingchargespayment');	

		//add reason for return																 
		$request['RequestedShipment']['SpecialServicesRequested'] = array(
			'SpecialServiceTypes' => 'RETURN_SHIPMENT', 
		   	'ReturnShipmentDetail' => array(
		   		'ReturnType' => 'FEDEX_TAG',
		     	'Rma' => array(
		     		'Number' => '012', 
		     		'Reason' => 'reason'
		     	)
		    )
		);

		
		//Add shipdate,ready date, pickupdae
		$ship_date = mktime(10, 0, 0, date("m"), date("d")+1, date("Y"));
		$ready_date = mktime(20, 0, 0, date("m"), date("d"), date("Y"));
		$pickup_date = mktime(20, 0, 0, date("m"), date("d"), date("Y"));

		$request['RequestedShipment']['ShipTimestamp'] = $ship_date;
		$request['RequestedShipment']['PickupDetail'] = array(
			'ReadyDateTime' => $ready_date, 
			'LatestPickupDateTime' => $pickup_date, 
			'CourierInstructions' => 'Left on porch'
		);


		//add return label specifies
		$request['RequestedShipment']['LabelSpecification'] = array(
			'LabelFormatType' => 'COMMON2D',
		  	'ImageType' => 'PNG'
		);
		$request['RequestedShipment']['PackageCount'] = '1';
		$request['RequestedShipment']['PackageDetail'] = 'INDIVIDUAL_PACKAGES';

		//add all order items
		
		$allItesmArray = array();
		$allOrderItems = $order->loadByIncrementId($params['orderIncrementId'])->getAllVisibleItems();
		foreach ($allOrderItems as $item) {

			$itemData = $item->getData();
			$productId = $itemData['product_id'];
			$product = Mage::getModel('catalog/product')->load($productId);
			$allItesmArray[] =  array(
				'SequenceNumber' => '1',
		   		'InsuredValue' => array(
		   			'Amount' => $item['price_incl_tax'],
		         	'Currency' => Mage::app()->getStore()->getCurrentCurrencyCode()
		        ),
		    	'ItemDescription' => $product->getDescription(),
		   		'Weight' => array(
		   			'Value' => $product->getWeight(),
		         	'Units' => 'LB'
		        ),
		     	'Dimensions' => array(
		     		'Length' => 25,
		   			'Width' => 25,
		        	'Height' => 25,
		          	'Units' => 'IN'
		        ),
		      	'CustomerReferences' => array(
		      		'CustomerReferenceType' => 'INVOICE_NUMBER',
		           	'Value' => 'INV4567892'
				)
			);
		}
		$request['RequestedShipment']['RequestedPackageLineItems'] = $allItesmArray;
		try {
			if($fedexlib->setEndpoint('changeEndpoint')){
				$newLocation = $client->__setLocation($fedexlib->setEndpoint('endpoint'));
			}
			$response = $client ->processTag($request);



			$this->getResponse()->setBody(json_encode($response));
		    
		} catch (SoapFault $exception) {
		    $fedexlib->printFault($exception, $client);
		}
	}

	public function cancelreturnAction()
	{

		$params = $this->getRequest()->getParams();		
		$serviceType = $params['serviceType'];
		$path_to_wsdl = Mage::getBaseDir().DS.'lib'.DS.'fedex'.DS.'wsdl'.DS.'ShipService_v19.wsdl';
		ini_set("soap.wsdl_cache_enabled", "0");
		$client = new SoapClient($path_to_wsdl, array('trace' => 1)); 
		$fedexlib = new Fedexlibrary();

		// add shipping account no
		$request['WebAuthenticationDetail'] = array(
			'ParentCredential' => array(
				'Key' => $fedexlib->getProperty('parentkey'), 
				'Password' => $fedexlib->getProperty('parentpassword')
			),
			'UserCredential' => array(
				'Key' => $fedexlib->getProperty('key'), 
				'Password' => $fedexlib->getProperty('password')
			)
		);
		$request['ClientDetail'] = array(
			'AccountNumber' => $fedexlib->getProperty('shipaccount'), 
			'MeterNumber' => $fedexlib->getProperty('meter')
		);
		$request['TransactionDetail'] = array('CustomerTransactionId' => '*** Express Call Tag Request using PHP ***');
		$request['Version'] = array(
			'ServiceId' => 'ship', 
			'Major' => '19', 
			'Intermediate' => '0', 
			'Minor' => '0'
		);

		$request['DispatchLocationId'] = $fedexlib->getProperty('dispatchlocationid');  // Replace 'XXX' with your dispatch location id (in ExpressTag response)
		$request['DispatchDate'] = $fedexlib->getProperty('dispatchdate');  // Replace with your ready date (in ProcessTag request) 
		$request['Payment'] = array(
			'PaymentType' => 'SENDER',
		  	'Payor' => array(
		  		'ResponsibleParty' => array(
		  			'AccountNumber' => $fedexlib->getProperty('billaccount'),
		  			'Contact' => null,
		      		'CountryCode' => 'US'
		      	)
		    )
		);
		$request['ConfirmationNumber'] = $fedexlib->getProperty('dispatchconfirmationnumber'); // Replace 'XXX' with your 

		try {
			if($fedexlib->setEndpoint('changeEndpoint')){
				$newLocation = $client->__setLocation($fedexlib->setEndpoint('endpoint'));
			}
			
			$response = $client -> deleteTag($request);

		    $this->getResponse()->setBody(json_encode($response));
		} catch (SoapFault $exception) {
		    $fedexlib->printFault($exception, $client);
		}
	}
	public function generatecreditmemoAction()
	{
		$params = $this->getRequest()->getParams();
		$orderIncrementId = $params['orderIncrementId'];

		$order = Mage::getModel('sales/order')->load($orderIncrementId, 'increment_id');
        if (!$order->getId()) {
            $this->_fault('order_not_exists');
        }
        if (!$order->canCreditmemo()) {
            $this->_fault('cannot_create_creditmemo');
        }
        $data = array();
		$service = Mage::getModel('sales/service_order', $order);
        $creditmemo = $service->prepareCreditmemo($data);
		// refund to Store Credit
        if ($refundToStoreCreditAmount) {
            // check if refund to Store Credit is available
            if ($order->getCustomerIsGuest()) {
                $this->_fault('cannot_refund_to_storecredit');
            }
            $refundToStoreCreditAmount = max(
                0,     min($creditmemo->getBaseCustomerBalanceReturnMax(), $refundToStoreCreditAmount)
            );
            if ($refundToStoreCreditAmount) {
                $refundToStoreCreditAmount = $creditmemo->getStore()->roundPrice($refundToStoreCreditAmount);
                $creditmemo->setBaseCustomerBalanceTotalRefunded($refundToStoreCreditAmount);
                $refundToStoreCreditAmount = $creditmemo->getStore()->roundPrice(
                    $refundToStoreCreditAmount*$order->getStoreToOrderRate()
                );
                // this field can be used by customer balance observer
                $creditmemo->setBsCustomerBalTotalRefunded($refundToStoreCreditAmount);
                // setting flag to make actual refund to customer balance after credit memo save
                $creditmemo->setCustomerBalanceRefundFlag(true);
            }
        }
        $creditmemo->setPaymentRefundDisallowed(true)->register();
        // add comment to creditmemo
        if (!empty($comment)) {
            $creditmemo->addComment($comment, $notifyCustomer);
        }
        $response = array();
        try {
            Mage::getModel('core/resource_transaction')
                ->addObject($creditmemo)
                ->addObject($order)
                ->save();
            // send email notification
            $creditmemo->sendEmail($notifyCustomer, ($includeComment ? $comment : ''));
            $response['status'] = 'success';
        } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
            $response['status'] = 'fail';
            $response['message'] = $e->getMessage();
        }

        $this->getResponse()->setBody(json_encode($response));
	}
	public function createReturnLabelAction()
	{
		$path_to_wsdl = Mage::getBaseDir().DS.'lib'.DS.'fedex'.DS.'wsdl'.DS.'ShipService_v19.wsdl';
		ini_set("soap.wsdl_cache_enabled", "0");
		$client = new SoapClient($path_to_wsdl, array('trace' => 1)); 
		$fedexlib = new Fedexlibrary();


		// add shipping account no
		$request['WebAuthenticationDetail'] = array(
			'ParentCredential' => array(
				'Key' => $fedexlib->getProperty('parentkey'), 
				'Password' => $fedexlib->getProperty('parentpassword')
			),
			'UserCredential' => array(
				'Key' => $fedexlib->getProperty('key'), 
				'Password' => $fedexlib->getProperty('password')
			)
		);
		$request['ClientDetail'] = array(
			'AccountNumber' => $fedexlib->getProperty('shipaccount'), 
			'MeterNumber' => $fedexlib->getProperty('meter')
		);
		$request['TransactionDetail'] = array('CustomerTransactionId' => '*** Express Call Tag Request using PHP ***');
		$request['Version'] = array(
			'ServiceId' => 'ship', 
			'Major' => '19', 
			'Intermediate' => '0', 
			'Minor' => '0'
		);

		$request['RequestedShipment'] = array(
			'ShipTimestamp' => date('c'),
			'DropoffType' => 'REGULAR_PICKUP', // valid values REGULAR_PICKUP, REQUEST_COURIER, DROP_BOX, BUSINESS_SERVICE_CENTER and STATION
			'ServiceType' => 'PRIORITY_OVERNIGHT', // valid values STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_GROUND, ...
			'PackagingType' => 'YOUR_PACKAGING', // valid values FEDEX_BOX, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
			'TotalWeight' => array(
				'Value' => 50.0, 
				'Units' => 'LB' // valid values LB and KG
			), 
			'Shipper' => $this->addShipper(),
			'Recipient' => $this->addRecipient(),
			'ShippingChargesPayment' => $this->addShippingChargesPayment(),
			'SpecialServicesRequested' => $this->addSpecialServices(),
			
			'LabelSpecification' => $this->addLabelSpecification(), 
			'PackageCount' => 1,
			'RequestedPackageLineItems' => array(
				'0' => $this->addPackageLineItem1()
			)
		);
		
		try {
			if($fedexlib->setEndpoint('changeEndpoint')){
				$newLocation = $client->__setLocation($fedexlib->setEndpoint('endpoint'));
			}
			
			$response = $client->processShipment($request);  // FedEx web service invocation

		    
		} catch (SoapFault $exception) {
		    printFault($exception, $client);
		}
	}

	public function addShipper()
	{
		$shipper = array(
			'Contact' => array(
				'PersonName' => 'Sender Name',
				'CompanyName' => 'Sender Company Name',
				'PhoneNumber' => '1234567890'
			),
			'Address' => array(
				'StreetLines' => array('Address Line 1'),
				'City' => 'Austin',
				'StateOrProvinceCode' => 'TX',
				'PostalCode' => '73301',
				'CountryCode' => 'US'
			)
		);
		return $shipper;
	}

	public function addRecipient()
	{
		$recipient = array(
			'Contact' => array(
				'PersonName' => 'Recipient Name',
				'CompanyName' => 'Recipient Company Name',
				'PhoneNumber' => '1234567890'
			),
			'Address' => array(
				'StreetLines' => array('Address Line 1'),
				'City' => 'Herndon',
				'StateOrProvinceCode' => 'VA',
				'PostalCode' => '20171',
				'CountryCode' => 'US',
				'Residential' => true
			)
		);
		return $recipient;	                                    
	}

	public function addShippingChargesPayment()
	{
		$shippingChargesPayment = array(
			'PaymentType' => 'SENDER',
	        'Payor' => array(
				'ResponsibleParty' => array(
					'AccountNumber' => Mage::getStoreConfig('carriers/fedex/account'),
					'Contact' => null,
					'Address' => array(
						'CountryCode' => 'US')
					)
			)
		);
		return $shippingChargesPayment;
	}

	public function addSpecialServices()
	{
		$specialServices = array(
			'SpecialServiceTypes' => array('RETURN_SHIPMENT'),
			'ReturnShipmentDetail' => array(
				 'ReturnType' => 'PRINT_RETURN_LABEL',

			)
		);
		/*$specialServices = array(
			'SpecialServiceTypes' => array('COD'),
			'CodDetail' => array(
				'CodCollectionAmount' => array(
					'Currency' => 'USD', 
					'Amount' => 150
				),
				'CollectionType' => 'ANY' // ANY, GUARANTEED_FUNDS
			)
		);*/
		return $specialServices; 
	}


	public function addLabelSpecification()
	{
		$labelSpecification = array(
			'LabelFormatType' => 'COMMON2D', // valid values COMMON2D, LABEL_DATA_ONLY
			'ImageType' => 'PDF',  // valid values DPL, EPL2, PDF, ZPLII and PNG
			'LabelStockType' => 'PAPER_7X4.75'
		);
		return $labelSpecification;
	}

	public function addPackageLineItem1()
	{
		$packageLineItem = array(
			'SequenceNumber'=>1,
			'GroupPackageCount'=>1,
			'Weight' => array(
				'Value' => 5.0,
				'Units' => 'LB'
			),
			'Dimensions' => array(
				'Length' => 20,
				'Width' => 20,
				'Height' => 10,
				'Units' => 'IN'
			)
		);
		return $packageLineItem;
	}

	public function checkaddrAction()
	{
		$params = $this->getRequest()->getParams();
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
        
        if($params['region_id'] != "" || $params['region_id'] != NULL){

        	$finalRegion = Mage::getModel('directory/region')->load($params['region_id'])->load()->getCode();
        }
        else{
        	$finalRegion = "";
        }
        $request['AddressesToValidate'] = array(
            0 => array(
                'ClientReferenceId' => 'ClientReferenceId1',
                'Address' => array(
                    'StreetLines' => array($params['street0']),
                    'PostalCode' => $params['postcode'],
                    'City' => $params['city'],
                    'StateOrProvinceCode' => $finalRegion,
                    'CountryCode' => $params['country_id']
                )
            ),
        );
        $response = $client ->addressValidation($request);
		$resCity = $response->AddressResults->EffectiveAddress->City;
		$resStateOrProvinceCode = $response->AddressResults->EffectiveAddress->StateOrProvinceCode;
        $resPostalCode = $response->AddressResults->EffectiveAddress->PostalCode;
        $resCountryCode = $response->AddressResults->EffectiveAddress->CountryCode;
        $result = array();
        $addressError  = false;
        $result['address'] = false;
        
        if(($resCity != "") && ($resCity !=null)){
	        if(strcasecmp($resCity,$params['city']) != 0){
	        	$addressError = true;
	        }
	    }        
      	if(($resStateOrProvinceCode != "") && ($resStateOrProvinceCode !=null)){
	        if($resStateOrProvinceCode != $finalRegion){
	        	$addressError = true;
	        }
	    }        
        if(($resPostalCode != "") && ($resPostalCode !=null)){
	        if($resPostalCode != $params['postcode']){
	        	$addressError = true;
	        }
	    }
        if(($resCountryCode != "") && ($resCountryCode !=null)){
	        if($resCountryCode != $params['country_id']){
	            $addressError = true;
	        }
	    }
        if($addressError == true){
        	$result['address'] = true;
        }
        $result['newAddress'] = $response->AddressResults->EffectiveAddress;
        $this->getResponse()->setBody(json_encode($result));
	}

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('fedex');
    }
}