<?php
require_once(Mage::getBaseDir().DS.'lib'.DS.'fedex'.DS.'library'.DS.'fedex-common.php');
class Biztech_Fedex_Adminhtml_PickupController extends Mage_Adminhtml_Controller_Action
{		

		public function statecheckAction(){

			$parrams = $this->getRequest()->getParams();
			$regionObj = Mage::getModel('directory/region')->load($parrams['pickupCity']);
			$this->getResponse()->setBody(json_encode($regionObj->getData()));

			
		}
		
		public function availabilityAction() 
		{
			$data = $this->getRequest()->getParams();
			$path_to_wsdl = Mage::getBaseDir().DS.'lib'.DS.'fedex'.DS.'wsdl'.DS.'PickupService_v13.wsdl';
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
			$request['TransactionDetail'] = array('CustomerTransactionId' => '*** Pickup Availability Request using PHP ***');
			$request['Version'] = array(
				'ServiceId' => 'disp', 
				'Major' => 13, 
				'Intermediate' => 0, 
				'Minor' => 0
			);
			// request parameter start
			$request['PickupAddress'] = array(
				'StreetLines' => array($data['pickup_address']),
				'City' => $data['pickup_city'],
				'StateOrProvinceCode' => $data['pickup_state'],
				'PostalCode' => $data['pickup_postcode'],
				'CountryCode' => $data['pickup_country']
			);
			$request['PickupRequestType'] = array($data['pickup_type']);
			$request['DispatchDate'] = $data['dispatch_date'];
			/*$request['PackageReadyTime'] = $fedexlib->getProperty('readytime');
			$request['CustomerCloseTime'] = $fedexlib->getProperty('closetime');*/
			$request['Carriers'] = array($data['pickup_carriers']);
			$request['ShipmentAttributes'] = array(
				'Dimensions'=>array(
					'Length'=> $data['shipment_length'],
					'Width'=> $data['shipment_width'],
					'Height'=> $data['shipment_height'],
					'Units'=> $data['shipment_unit']
				),
			  	'Weight'=>array(
				  	'Units'=> $data['shipment_weight_unit'],
				  	'Value'=> $data['shipment_weight']
				)
			);
			// request parameter start
			$result = array();
			$result['status'] = 'fail';
			try{

				$response = $client ->getPickupAvailability($request);
				if($response -> HighestSeverity != 'FAILURE' && $response -> HighestSeverity != 'ERROR'){
					$result['status'] = 'success';
					$layout = $this->getLayout()->createBlock('fedex/adminhtml_pickup_edit_tab_form');
					$result["timings"] = $layout->setData(array("cat_id"=>$response))->setTemplate('fedex/pickup/available.phtml')->toHtml();	
				}
				else{
					$result['message'] = $response->Notifications->Message;
				}
				
				
			} catch (SoapFault $e) {

				$result['message'] = $e->getMessage();

			}
			$this->getResponse()->setBody(json_encode($result));
		}



		protected function _initAction()
		{
				$this->loadLayout()->_setActiveMenu("fedex/pickup")->_addBreadcrumb(Mage::helper("adminhtml")->__("Pickup  Manager"),Mage::helper("adminhtml")->__("Pickup Manager"));
				return $this;
		}
		public function indexAction() 
		{
				
			    $this->_title($this->__("Fedex"));
			    $this->_title($this->__("Manager Pickup"));

				$this->_initAction();
				$this->renderLayout();
		}
		public function editAction()
		{			    
			    $this->_title($this->__("Fedex"));
				$this->_title($this->__("Pickup"));
			    $this->_title($this->__("Edit Item"));
				
				$id = $this->getRequest()->getParam("id");
				$model = Mage::getModel("fedex/pickup")->load($id);
				if ($model->getId()) {
					Mage::register("pickup_data", $model);
					$this->loadLayout();
					$this->_setActiveMenu("fedex/pickup");
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Pickup Manager"), Mage::helper("adminhtml")->__("Pickup Manager"));
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Pickup Description"), Mage::helper("adminhtml")->__("Pickup Description"));
					$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
					$this->_addContent($this->getLayout()->createBlock("fedex/adminhtml_pickup_edit"))->_addLeft($this->getLayout()->createBlock("fedex/adminhtml_pickup_edit_tabs"));
					$this->renderLayout();
				} 
				else {
					Mage::getSingleton("adminhtml/session")->addError(Mage::helper("fedex")->__("Item does not exist."));
					$this->_redirect("*/*/");
				}
		}

		public function newAction()
		{

			$this->_title($this->__("Fedex"));
			$this->_title($this->__("Pickup"));
			$this->_title($this->__("New Item"));

	        $id   = $this->getRequest()->getParam("id");
			$model  = Mage::getModel("fedex/pickup")->load($id);

			$data = Mage::getSingleton("adminhtml/session")->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register("pickup_data", $model);

			$this->loadLayout();
			$this->_setActiveMenu("fedex/pickup");

			$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

			$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Pickup Manager"), Mage::helper("adminhtml")->__("Pickup Manager"));
			$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Pickup Description"), Mage::helper("adminhtml")->__("Pickup Description"));


			$this->_addContent($this->getLayout()->createBlock("fedex/adminhtml_pickup_edit"))->_addLeft($this->getLayout()->createBlock("fedex/adminhtml_pickup_edit_tabs"));

			$this->renderLayout();

		}
		public function saveAction()
		{

			$post_data = $this->getRequest()->getPost();

				if ($post_data) {
					try {

						


						/*$ReadyTimestamp = explode('T',$post_data['ready_timestamp']);
						$date = new DateTime($readyTimeStamp[0]." ".$readyTimeStamp[1]);
						$finalTimeStamp  = $date->getTimestamp();*/

						$finalTimeStamp  = mktime(8, 0, 0, date("m")  , date("d"), date("Y"));
						$regionCollection = Mage::getModel('directory/region')->getCollection();
						foreach($regionCollection as $regions){
							if($post_data['pickup_state'] == $regions->getRegionId()){
								$regionState = $regions->getCode();
							}
						}

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
									'PersonName' => $post_data['person_name'],
						        	'CompanyName' => $post_data['company_name'],
						        	'PhoneNumber' => $post_data['phone_no']
						        ),
						      	'Address' => array(
						      		'StreetLines' => array($post_data['phone_no']),
						        	'City' => $post_data['pickup_city'],
						        	'StateOrProvinceCode' => $regionState,
						         	'PostalCode' => $post_data['pickup_postcode'],
						         	'CountryCode' => $post_data['pickup_country'])
						       	),
						   	'PackageLocation' => $post_data['package_location'], // valid values NONE, FRONT, REAR and SIDE
						    'BuildingPartCode' => $post_data['building_partcode'], // valid values APARTMENT, BUILDING, DEPARTMENT, SUITE, FLOOR and ROOM
						    'BuildingPartDescription' => $post_data['building_part_description'],
						    //'ReadyTimestamp' => mktime(8, 0, 0, date("m")  , date("d"), date("Y")), // Replace with your ready date time
						    'ReadyTimestamp' => $finalTimeStamp, // Replace with your ready date time
						    'CompanyCloseTime' => '20:00:00'
						);
						$request['PackageCount'] = $post_data['package_count'];
						$request['TotalWeight'] = array(
							'Value' => $post_data['total_weight_value'], 
							'Units' => $post_data['total_weight_unit'] // valid values LB and KG
						); 
						$request['CarrierCode'] = $post_data['courier_code']; // valid values FDXE-Express, FDXG-Ground, FDXC-Cargo, FXCC-Custom Critical and FXFR-Freight
						//$request['OversizePackageCount'] = '1';
						$request['CourierRemarks'] = $post_data['courier_remarks'];
						//create pick up request after



						
						$response = $client->createPickup($request);


						




						

						if($response->HighestSeverity != 'SUCCESS'){
							$error = $response->Notifications->Message;
							Mage::throwException($error);	
						}
						
						$pickConfirmNo = $response->PickupConfirmationNumber;
						$pickLocation = $response->Location;

						/*$pickObj = Mage::getModel('fedex/pickup');
						$LastPickupId = $pickObj->getCollection()->getLastItem()->getId();
						$pickObj1 = $pickObj->load($LastPickupId);
						$pickObj1->setStatus(1);
						$pickObj1->save();*/


						$data = array();
						$model = Mage::getModel("fedex/pickup")
						->addData($post_data)
						->setId($this->getRequest()->getParam("id"))
						->setConfirmationNo((int)$pickConfirmNo)
						->setPickupLocation($pickLocation)
						->setStatus(1)
						->save();




						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Pickup was successfully saved"));
						Mage::getSingleton("adminhtml/session")->setPickupData(false);

						if ($this->getRequest()->getParam("back")) {
							$this->_redirect("*/*/edit", array("id" => $model->getId()));
							return;
						}
						$this->_redirect("*/*/");
						return;
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						Mage::getSingleton("adminhtml/session")->setPickupData($this->getRequest()->getPost());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					return;
					}
					


				}
				$this->_redirect("*/*/");
		}



		public function deleteAction()
		{
				if( $this->getRequest()->getParam("id") > 0 ) {
					try {
						$model = Mage::getModel("fedex/pickup");
						$model->setId($this->getRequest()->getParam("id"))->delete();
						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item was successfully deleted"));
						$this->_redirect("*/*/");
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					}
				}
				$this->_redirect("*/*/");
		}

		
		public function massRemoveAction()
		{
			try {
				$ids = $this->getRequest()->getPost('pickup_ids', array());
				foreach ($ids as $id) {
                      $model = Mage::getModel("fedex/pickup");
					  $model->setId($id)->delete();
				}
				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item(s) was successfully removed"));
			}
			catch (Exception $e) {
				Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
			}
			$this->_redirect('*/*/');
		}
		public function massCancelAction(){
			try {
				$ids = $this->getRequest()->getPost('pickup_ids', array());
				foreach ($ids as $id) {
                    $model = Mage::getModel("fedex/pickup")->load($id);

                    $pickupCourierCode = $model->getCourierCode();
                    $pickupConfirmaionNo = $model->getConfirmationNo();
                    $pickupLocation = $model->getPickupLocation();
                    $pickupReadytimestamp = $model->getReadyTimestamp();
                    $pickupfinaltimeStamp = explode('T', $pickupReadytimestamp);


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


					$request['CarrierCode'] = $pickupCourierCode; // valid values FDXE-Express, FDXG-Ground, etc
					$request['PickupConfirmationNumber'] = $pickupConfirmaionNo; // Replace 'XXX' with your Pickup confirmation number
					//$request['ScheduledDate'] = $pickupReadytimestamp;
					$request['ScheduledDate'] = $pickupfinaltimeStamp[0];

					$request['Location'] = $pickupLocation; // Replace 'XXX' with your Pickip Loaction Code/ID
					$request['CourierRemarks'] = 'Do not pickup.  This is a test';


					
					$response = $client->cancelPickup($request);


					/*echo "<pre>";
					print_r($response);
					die;*/
					
				   	if($response->HighestSeverity != 'SUCCESS'){
				   		$error = $response->Notifications->Message;
						Mage::throwException($error);
				   	}
				   	else{
				   		$model->setStatus(0);
				   	}
				   	

                    // make pick up cancel request



					  
				}
				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item(s) pick up was successfully Canceled"));
			}
			catch (Exception $e) {
				Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
			}
			$this->_redirect('*/*/');
		}
			
		/**
		 * Export order grid to CSV format
		 */
		public function exportCsvAction()
		{
			$fileName   = 'pickup.csv';
			$grid       = $this->getLayout()->createBlock('fedex/adminhtml_pickup_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
		} 
		/**
		 *  Export order grid to Excel XML format
		 */
		public function exportExcelAction()
		{
			$fileName   = 'pickup.xml';
			$grid       = $this->getLayout()->createBlock('fedex/adminhtml_pickup_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
		}
                
                
                protected function _isAllowed()
                {
                    return Mage::getSingleton('admin/session')->isAllowed('Fedex/fedex');
                }
}
