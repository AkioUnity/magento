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

class Magebird_Popup_Model_Widget_Newsletter_Confirmneedfield {
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    { 
        $options = array();
        $isConfirmNeed   = (Mage::getStoreConfig('newsletter/subscription/confirm') == 1) ? true : false;
        $options[] = array('value' => 0, 'label'=>Mage::helper('magebird_popup')->__("No"));       
        if($isConfirmNeed){
          $options[] = array('value' => 1, 'label'=>Mage::helper('magebird_popup')->__("Yes"));
        }
        return $options;
    } 
}