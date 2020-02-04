<?php

class Magebird_Popup_Model_Stats extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('magebird_popup/stats');
    }

    public function load($id, $field = null)
    {
        return parent::load($id, $field);
    }

    
    function cleanOldEmails(){    
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $table = Mage::getSingleton("core/resource")->getTableName('magebird_popup_subscriber');
        $where = array();
        $ago2months = strtotime("-4 month");
        $where[] =  $connection->quoteInto('date_created < ?',$ago2months);
        $connection->delete($table,$where);
    }
}