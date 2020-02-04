<?php
class AcimaCredit_AcimaCheckout_Model_Paymentmethod extends Mage_Payment_Model_Method_Abstract {
  protected $_code  = 'acimacheckout';
  protected $_formBlockType = 'acimacheckout/form_acimacheckout';
  protected $_infoBlockType = 'acimacheckout/info_acimacheckout';
 
  public function getOrderPlaceRedirectUrl()
  {
    return Mage::getUrl('acimacheckout/payment/redirect', array('_secure' => false));
  }
}