<?php
require_once(Mage::getBaseDir().DS.'lib'.DS.'fedex'.DS.'library'.DS.'fedex-common.php');
class Biztech_Fedex_Adminhtml_OrdersController extends Mage_Adminhtml_Controller_Action{
	public function indexAction(){
		
	}
        protected function _isAllowed()
        {
            return Mage::getSingleton('admin/session')->isAllowed('Fedex/fedex');
        }

}