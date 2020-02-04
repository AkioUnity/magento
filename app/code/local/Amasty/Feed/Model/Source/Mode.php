<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */
class Amasty_Feed_Model_Source_Mode extends Varien_Object
{
    public function toOptionArray($vl = true)
    {
        if ($vl) {
            $options = array(
                array('value'=> '0', 'label' => Mage::helper('amfeed')->__('Manually')),
                array('value'=> '4', 'label' => Mage::helper('amfeed')->__('Hourly')),
                array('value'=> '1', 'label' => Mage::helper('amfeed')->__('Daily')),
                array('value'=> '2', 'label' => Mage::helper('amfeed')->__('Weekly')),
                array('value'=> '3', 'label' => Mage::helper('amfeed')->__('Monthly')),
            );
        } else {
            $options = array(
                '0' => Mage::helper('amfeed')->__('Manually'),
                '4' => Mage::helper('amfeed')->__('Hourly'),
                '1' => Mage::helper('amfeed')->__('Daily'),
                '2' => Mage::helper('amfeed')->__('Weekly'),
                '3' => Mage::helper('amfeed')->__('Monthly'),
            );
        }
        
        return $options;
    }
}