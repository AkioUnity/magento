<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */ 
class Amasty_Acart_Model_Blist extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('amacart/blist');
    }
}