<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */  
class Amasty_Feed_Model_Field_Modification extends Varien_Object
{
    protected $_field;
    
    public function init($field){
        $this->_field = $field;
        return $this;
    }
    
    public function getField(){
        return $this->_field;
    }
    
    public function modify($val){
        $ret = null;
        
        if (is_numeric($val)){
            $ret = $this->_transform($val);
        } else {
            $ret = $val;
        }
        
        return $ret;
        
    }
    
//    protected function _transform($attrValue){
//
//        $transform = $this->getValue();
//        if (!empty($transform)){
//            $delta = NULL;
//
//            preg_match("/[0-9]+(\.[0-9][0-9]?)?/", $transform, $matches);
//            if ('%' == $transform[strlen($transform)-1] && $matches[0]) {
//                $delta = $attrValue*$matches[0]/100;
//            } else {
//                $delta = $matches[0];
//            }
//
//            // transform the attribute value
//            switch ($transform[0]) {
//                case '+':
//                    $attrValue = $attrValue + $delta;
//                    break;
//                case '-':
//                    $attrValue = $attrValue - $delta;
//                    break;
//                case '*':
//                    $attrValue = $attrValue * $delta;
//                    break;
//                case '/':
//                    $attrValue = $attrValue / $delta;
//                    break;
//            }
//        }
//        return $attrValue;
//    }
    protected function _transform($attrValue){

        $transform = $this->getValue();
        if (!empty($transform)){
            $delta = NULL;
            if (is_string($transform) && strpos($transform,'(') !== false) {
                $data = str_replace(array('(',')'),'',$transform);
                $data = explode(' ',$data);
                if (isset($data[0]) && isset($data[1]) && isset($data[2]) && isset($data[3])) {

                    $attrValue = $this->caclculateTransformValue($attrValue, $data[1], $data[0]);
                    $attrValue = $this->caclculateTransformValue($attrValue, $data[3], $data[2]);

                    return $attrValue;
                }

            }
            preg_match("/[0-9]+(\.[0-9][0-9]?)?/", $transform, $matches);
            if ('%' == $transform[strlen($transform)-1] && $matches[0]) {
                $delta = $attrValue*$matches[0]/100;
            } else {
                $delta = $matches[0];
            }
            // transform the attribute value
            $attrValue = $this->caclculateTransformValue($attrValue, $delta, $transform[0]);
        }
        return $attrValue;
    }

    /**
    * @param $attrValue
    * @param $delta
    * @param $operator
    * @return float
    */
    protected function caclculateTransformValue($attrValue, $delta, $operator) {
        switch ($operator) {
            case '+':
                $attrValue = $attrValue + $delta;
            break;
            case '-':
                $attrValue = $attrValue - $delta;
            break;
            case '*':
                $attrValue = $attrValue * $delta;
            break;
            case '/':
                $attrValue = $attrValue / $delta;
            break;
        }
        return $attrValue;
    }
}