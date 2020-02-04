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

class Magebird_Popup_Model_Widget_Buttonsize{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {              
        for($n=1;$n<=10;$n++){
          $options[] = array('value' => $n, 'label'=>$n);
        }       
        return $options;
    } 
}