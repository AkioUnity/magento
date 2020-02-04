<?php

class Biztech_Fedex_CustomerController extends Mage_Core_Controller_Front_Action{
	public function returnAction(){
		$params = $this->getRequest()->getParams();
		$orderId = $params['orderId'];


		/*$returnReadyDate = $params['returnReadyDate'];
		$returnReadyDate = new DateTime($returnReadyDate);
		$returnReadyDateFormatted = $returnReadyDate->format('Y-m-d H:i:s');
		$reasonForReturn = $params['reasonForReturn'];*/

		$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
		// $order->setData('reason_for_return',$reasonForReturn);
		// $order->setData('return_avail_date',$returnReadyDateFormatted);
		$order->setData('is_return',1);

		$order->save();

		$result = array();
		$result['status'] = true;

		$this->getResponse()->setBody(json_encode($result));
		
	}
}
