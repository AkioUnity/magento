<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */

/**
 * @author Amasty
 */ 
class Amasty_Acart_Model_Mysql4_Blist_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('amacart/blist');
    }
}