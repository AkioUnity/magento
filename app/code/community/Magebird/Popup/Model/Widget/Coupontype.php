<?php

/**
 * Magebird.com
 *
 * @category   Magebird
 * @package    Magebird_FloatingCart
 * @copyright  Copyright (c) 2018 Magebird (http://www.Magebird.com)
 * @license    http://www.magebird.com/licence
 * Any form of ditribution, sell, transfer forbidden see licence above 
 */

class Magebird_Popup_Model_Widget_Coupontype{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {              
        $options[] = array('value' => 0, 'label'=>Mage::helper('magebird_popup')->__('No coupon'));
        $options[] = array('value' => 1, 'label'=>Mage::helper('magebird_popup')->__('Specific static coupon code'));        
     
        if (version_compare(Mage::getVersion(), '1.7', '>=')){
          $options[] = array('value' => 2, 'label'=>Mage::helper('magebird_popup')->__('Auto generate dynamic coupon code for each customer'));
        }       
        return $options;
    } 
}