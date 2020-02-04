<?php

class Magebird_Popup_Model_Mysql4_Stats extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('magebird_popup/stats', 'stat_id');
    }


}