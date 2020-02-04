<?php
class Biztech_Fedex_Model_Mysql4_Shipment extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("fedex/shipment", "shipment_id");
    }
}