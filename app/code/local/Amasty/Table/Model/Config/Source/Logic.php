<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Table
 */


class Amasty_Table_Model_Config_Source_Logic extends Varien_Object
{
    public function toOptionArray()
    {
        $options = array(
            '0' => Mage::helper('amtable')->__('Strings, e.g. AB2%'),
            '1'   => Mage::helper('amtable')->__('Numbers, e.g. from 111 to 222 or from AB2 to AB19'),
        );

        return $options;
    }
}