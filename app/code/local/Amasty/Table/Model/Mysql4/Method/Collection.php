<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Table
 */ 
class Amasty_Table_Model_Mysql4_Method_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('amtable/method');
    }
    
    public function addStoreFilter($storeId)
    {
        $storeId = intVal($storeId);
        $this->getSelect()->where('stores="" OR stores LIKE "%,'.$storeId.',%"');
        
        return $this;
    }    
    
    public function addCustomerGroupFilter($groupId)
    {
        $groupId = intVal($groupId);
        $this->getSelect()->where('cust_groups="" OR cust_groups LIKE "%,'.$groupId.',%"');
        
        return $this;
    }

    public function toOptionHash()
    {
        return $this->_toOptionHash('method_id','comment');
    }

    public function hashComment($storeId)
    {
        $res = array();
        foreach ($this as $item) {
            $res[$item->getData('method_id')] = $item->getCommentLabel($storeId);
        }
        return $res;
    }

    public function hashMinRate()
    {
        return $this->_toOptionHash('method_id','min_rate');
    }

    public function hashMaxRate()
    {
        return $this->_toOptionHash('method_id','max_rate');
    }
}