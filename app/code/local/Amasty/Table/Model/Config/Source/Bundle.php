<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Table
 */
class Amasty_Table_Model_Config_Source_Bundle extends Varien_Object
{
    public function toOptionArray()
    {
        $vals = array(
            '0' => Mage::helper('amtable')->__('As in "Ship Bundle Items" setting'),
            '1'   => Mage::helper('amtable')->__('From bundle product'),
            '2'   => Mage::helper('amtable')->__('From items in bundle'),
        );

        $options = array();
        foreach ($vals as $k => $v)
            $options[] = array(
                    'value' => $k,
                    'label' => $v
            );
        
        return $options;
    }
}