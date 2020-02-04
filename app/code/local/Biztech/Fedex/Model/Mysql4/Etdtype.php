<?php
class Biztech_Fedex_Model_Mysql4_Etdtype extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("fedex/etdtype", "etdtype_id");
    }
}