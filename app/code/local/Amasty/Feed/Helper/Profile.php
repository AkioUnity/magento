<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */  
class Amasty_Feed_Helper_Profile extends Mage_Core_Helper_Abstract
{
    protected $_customBlocks = array(
        'actions' => 'amasty/amfeed/filter/actions/default.phtml',
        'new_condition' => 'amasty/amfeed/filter/controls/new_condition.phtml',
        'new_value' => 'amasty/amfeed/filter/controls/new_value.phtml',
    );
    
    function getCustomBlocks(){
        return $this->_customBlocks;
    }
    
    function getDefaultConditions(){
        
        $condtions = array(
            "eq" => "equal",
            "neq" => "not equal",
            "gt" => "greater than",
            "lt" => "less than",
            "gteq" => "greater than or equal to",
            "lteq" => "less than or equal to",
            "like" => "like",
            "nlike" => "not like");
        
        return $condtions;
    }
    
    function getSelectConditions(){
        $condtions = array(
            "eq" => "equal",
            "neq" => "not equal",
        );
        
        return $condtions;
    }
}