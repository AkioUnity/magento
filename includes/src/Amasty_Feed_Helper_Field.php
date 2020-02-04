<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */  
class Amasty_Feed_Helper_Field extends Mage_Core_Helper_Abstract
{
    protected $_customBlocks = array(
        'output_value' => 'amasty/amfeed/field/output_value/default.phtml',
        'output_value_static' => 'amasty/amfeed/field/output_value/static.phtml',
        'new_output' => 'amasty/amfeed/field/controls/new_output.phtml',
        'modification' => 'amasty/amfeed/field/modification/default.phtml',
        'new_condition' => 'amasty/amfeed/field/controls/new_condition.phtml',
        
        
        'actions' => 'amasty/amfeed/filter/actions/default.phtml',
        
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
            "nlike" => "not like",
            "isempty" => "is empty",
            "isnotempty" => "is not empty"
            );
        
        return $condtions;
    }
    
    function getSelectConditions(){
        $condtions = array(
            "eq" => "equal",
            "neq" => "not equal",
            "isempty" => "is empty",
            "isnotempty" => "is not empty"
        );
        
        return $condtions;
    }
}