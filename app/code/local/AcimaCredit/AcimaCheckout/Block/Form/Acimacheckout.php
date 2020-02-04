<?php
class AcimaCredit_AcimaCheckout_Block_Form_Acimacheckout extends Mage_Payment_Block_Form
{
  protected function _construct()
  {
    parent::_construct();

    $this->setTemplate('acimacheckout/form/acimacheckout.phtml');
  }
}