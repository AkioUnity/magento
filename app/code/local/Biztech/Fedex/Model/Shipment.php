<?php

    class Biztech_Fedex_Model_Shipment extends Mage_Core_Model_Abstract
    {
        public function _construct()
        {
            parent::_construct();
            $this->_init('fedex/shipment');
        }

        
}