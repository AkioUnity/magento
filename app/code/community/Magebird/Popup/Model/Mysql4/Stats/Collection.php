<?php
class Magebird_Popup_Model_Mysql4_Stats_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('magebird_popup/stats');
    }

}