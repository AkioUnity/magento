<?php
class Biztech_Fedex_Model_Mysql4_Pshipment extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("fedex/pshipment", "pickup_shipment_id");
    }
}