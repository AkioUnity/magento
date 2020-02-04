<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Table
 */
class Amasty_Table_Model_Config_Source_Configurable extends Varien_Object
{
    public function toOptionArray()
    {
        $vals = array(
            '0' => Mage::helper('amtable')->__('From associated products'),
            '1'   => Mage::helper('amtable')->__('From parent product'),
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