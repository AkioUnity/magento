<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */  
class Amasty_Feed_Model_Filter extends Mage_Core_Model_Abstract
{
    private $_condition = null;
        
    public static $_TYPE_ATTRIBUTE = 'attribute';
    public static $_TYPE_OTHER = 'other';
        
    public function getCondition(){
        $ret = array();
        
        if ($this->hasAdvancedCondition()){
            
            if ($this->_condition === NULL){
                $condition = unserialize($this->condition_serialized);
                foreach ($condition as &$value) {
            
                    if (array_key_exists('condition', $value) && 
                        array_key_exists('attribute', $value['condition'])){
                        
                        
                        if (!isset($value['condition']['type']) &&
                            isset($value['condition']['attribute'])){
                            $value['condition']['type'] = array_fill(
                                0, 
                                count($value['condition']['attribute']),
                                Amasty_Feed_Model_Filter::$_TYPE_ATTRIBUTE);
                        }
                    }
                }
                
                $this->_condition = $condition;
            }
            
            $ret = $this->_condition;
        }
        
        return $ret;
    }
    
    public function hasAdvancedCondition(){
        return $this->condition_serialized !== NULL;
    }
}