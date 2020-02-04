<?php
// Copyright 2009, FedEx Corporation. All rights reserved.

/**
 *  Print SOAP request and response
 */
define('Newline',"<br />");

class Fedexlibrary{

	public function printSuccess($client, $response) {
	    $this->printReply($client, $response);
	}

	public function printReply($client, $response){
		$highestSeverity=$response->HighestSeverity;
		if($highestSeverity=="SUCCESS"){$printReplyContent1 = '<h2>The transaction was successful.</h2><br>\n';return $printReplyContent1;}
		if($highestSeverity=="WARNING"){$printReplyContent2 = '<h2>The transaction returned a warning.</h2><br>\n';return $printReplyContent2;}
		if($highestSeverity=="ERROR"){$printReplyContent3 = '<h2>The transaction returned an Error.</h2><br>\n';return $printReplyContent3;}
		if($highestSeverity=="FAILURE"){$printReplyContent4 ='<h2>The transaction returned a Failure.</h2><br>\n';return $printReplyContent4;}
                
		$this->printNotifications($response -> Notifications);
		$this->printRequestResponse($client, $response);
	}

	public function printRequestResponse($client){
            $printReplyContent2 = '<h2>Request</h2>' . "\n";
            $printReplyContent2 = $printReplyContent2 + '<pre>' . htmlspecialchars($client->__getLastRequest()). '</pre>';  
            $printReplyContent2 = $printReplyContent2 + '<h2>Response</h2>'. "\n";
            $printReplyContent2 = $printReplyContent2 + '<pre>' . htmlspecialchars($client->__getLastResponse()). '</pre>';
            
            return $printReplyContent2;
            
            
	}

	/**
	 *  Print SOAP Fault
	 */  
	public function printFault($exception, $client) {
	   
           $printFault = '<h2>Fault</h2>' . "<br>\n";                        
	   $printFault = $printFault +"<b>Code:</b>{$exception->faultcode}<br>\n";
	   $printFault = $printFault + "<b>String:</b>{$exception->faultstring}<br>\n";
           
           return $printFault;
           
           
	   $this->writeToLog($client);
           
	    $printFault1 = '<h2>Request</h2>' . "\n";
            $printFault1 = $printFault1 +'<pre>' . htmlspecialchars($client->__getLastRequest()). '</pre>';  
	    $printFault1 = $printFault1 +  "\n";
            return $printFault1;
	}

	/**
	 * SOAP request/response logging to a file
	 */                                  
	public function writeToLog($client){  

	  /**
		 * __DIR__ refers to the directory path of the library file.
		 * This location is not relative based on Include/Require.
		 */
		if (!$logfile = fopen(__DIR__.'/fedextransactions.log', "a"))
		{
	   		error_func("Cannot open " . __DIR__.'/fedextransactions.log' . " file.\n", 0);
	   		return;
                        
		}
		fwrite($logfile, sprintf("\r%s:- %s",date("D M j G:i:s T Y"), $client->__getLastRequest(). "\r\n" . $client->__getLastResponse()."\r\n\r\n"));

	}

	/**
	 * This section provides a convenient place to setup many commonly used variables
	 * needed for the php sample code to function.
	 */
	public function getProperty($var){

		$accountNo = Mage::getStoreConfig('carriers/fedex/account');


	  	if($var == 'key') Return Mage::getStoreConfig('carriers/fedex/key'); 
		if($var == 'password') Return Mage::getStoreConfig('carriers/fedex/password'); 
		if($var == 'shipaccount') Return $accountNo;
		if($var == 'billaccount') Return $accountNo;
		if($var == 'dutyaccount') Return $accountNo; 
		if($var == 'freightaccount') Return $accountNo;  
		if($var == 'trackaccount') Return $accountNo; 
		if($var == 'dutiesaccount') Return $accountNo;
		if($var == 'importeraccount') Return $accountNo;
		if($var == 'brokeraccount') Return $accountNo;
		if($var == 'distributionaccount') Return $accountNo;
		if($var == 'locationid') Return 'PLBA';
		if($var == 'printlabels') Return true;
		if($var == 'printdocuments') Return true;
		if($var == 'packagecount') Return '4';
		if($var == 'validateaccount') Return $accountNo;
		if($var == 'meter') Return Mage::getStoreConfig('carriers/fedex/meter_number');
			
		if($var == 'shiptimestamp') Return mktime(10, 0, 0, date("m"), date("d")+1, date("Y"));

		if($var == 'spodshipdate') Return '2016-04-13';
		if($var == 'serviceshipdate') Return '2013-04-26';
	  	if($var == 'shipdate') Return '2016-04-21';

		if($var == 'readydate') Return '2014-12-15T08:44:07';
		//if($var == 'closedate') Return date("Y-m-d");
		if($var == 'closedate') Return '2016-04-18';
		if($var == 'pickupdate') Return date("Y-m-d", mktime(8, 0, 0, date("m")  , date("d")+1, date("Y")));
		if($var == 'pickuptimestamp') Return mktime(8, 0, 0, date("m")  , date("d"), date("Y"));
		if($var == 'pickuplocationid') Return 'SQLA';
		if($var == 'pickupconfirmationnumber') Return '1';

		if($var == 'dispatchdate') Return date("Y-m-d", mktime(8, 0, 0, date("m")  , date("d")+1, date("Y")));
		if($var == 'dispatchlocationid') Return 'NQAA';
		if($var == 'dispatchconfirmationnumber') Return '4';		
		
		if($var == 'tag_readytimestamp') Return mktime(10, 0, 0, date("m"), date("d")+1, date("Y"));
		if($var == 'tag_latesttimestamp') Return mktime(20, 0, 0, date("m"), date("d")+1, date("Y"));	

		if($var == 'expirationdate') Return date("Y-m-d", mktime(8, 0, 0, date("m"), date("d")+15, date("Y")));
		if($var == 'begindate') Return '2014-10-16';
		if($var == 'enddate') Return '2014-10-16';	

		if($var == 'trackingnumber') Return '794634473666';

		if($var == 'hubid') Return '5531';
		
		if($var == 'jobid') Return 'XXX';

		if($var == 'searchlocationphonenumber') Return '5555555555';
		if($var == 'customerreference') Return '510087224';

		if($var == 'shipper') Return array(
			'Contact' => array(
				'PersonName' => 'Sender Name',
				'CompanyName' => 'Sender Company Name',
				'PhoneNumber' => '1234567890'
			),
			'Address' => array(
				'StreetLines' => array('Addres \r  s Line 1'),
				'City' => 'Collierville',
				'StateOrProvinceCode' => 'TN',
				'PostalCode' => '38017',
				'CountryCode' => 'US',
				'Residential' => 1
			)
		);
		if($var == 'recipient') Return array(
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
				'Residential' => 1
			)
		);	

		if($var == 'address1') Return array(
			'StreetLines' => array('10 Fed Ex Pkwy'),
			'City' => 'Memphis',
			'StateOrProvinceCode' => 'TN',
			'PostalCode' => '38115',
			'CountryCode' => 'US'
	    );
		if($var == 'address2') Return array(
			'StreetLines' => array('13450 Farmcrest Ct'),
			'City' => 'Herndon',
			'StateOrProvinceCode' => 'VA',
			'PostalCode' => '20171',
			'CountryCode' => 'US'
		);					  
		if($var == 'searchlocationsaddress') Return array(
			'StreetLines'=> array('240 Central Park S'),
			'City'=>'Austin',
			'StateOrProvinceCode'=>'TX',
			'PostalCode'=>'78701',
			'CountryCode'=>'US'
		);
										  
		if($var == 'shippingchargespayment') Return array(
			'PaymentType' => 'SENDER',
			'Payor' => array(
				'ResponsibleParty' => array(
					'AccountNumber' => $this->getProperty('billaccount'),
					'Contact' => null,
					'Address' => array('CountryCode' => 'US')
				)
			)
		);	
		if($var == 'freightbilling') Return array(
			'Contact'=>array(
				'ContactId' => 'freight1',
				'PersonName' => 'Big Shipper',
				'Title' => 'Manager',
				'CompanyName' => 'Freight Shipper Co',
				'PhoneNumber' => '1234567890'
			),
			'Address'=>array(
				'StreetLines'=>array(
					'1202 Chalet Ln', 
					'Do Not Delete - Test Account'
				),
				'City' =>'Harrison',
				'StateOrProvinceCode' => 'AR',
				'PostalCode' => '72601-6353',
				'CountryCode' => 'US'
				)
		);
		return $var;
	}

	public function setEndpoint($var){
		if($var == 'changeEndpoint') Return false;
		if($var == 'endpoint') Return 'XXX';
	}

	public function printNotifications($notes){
		foreach($notes as $noteKey => $note){
			if(is_string($note)){    
	            return $noteKey . ': ' . $note . Newline;
	        }
	        else{
	        	$this->printNotifications($note);
	        }
		}
		return Newline;
	}

	public function printError($client, $response){
	    $this->printReply($client, $response);
	}

	public function trackDetails($details, $spacer){
		foreach($details as $key => $value){
			if(is_array($value) || is_object($value)){
	        	$newSpacer = $spacer. '&nbsp;&nbsp;&nbsp;&nbsp;';
	    		return '<tr><td>'. $spacer . $key.'</td><td>&nbsp;</td></tr>';
	    		$this->trackDetails($value, $newSpacer);
	    	}elseif(empty($value)){
	    		return '<tr><td>'.$spacer. $key .'</td><td>'.$value.'</td></tr>';
	    	}else{
	    		return '<tr><td>'.$spacer. $key .'</td><td>'.$value.'</td></tr>';
	    	}
	    }
	}
}
