<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */  
class Amasty_Feed_Model_Field_Condition extends Varien_Object
{
    protected $_field;
    protected $_attributeHelper;
    
    public function init($field){
        $this->_field = $field;
        return $this;
    }
    
    public function getField(){
        return $this->_field;
    }
    
    public function validate($productData){
        $ret = TRUE;
        
        $type = $this->getType();
        $attribute = $this->getAttribute();
        $operator = $this->getOperator();
        $value = $this->getValue();
        
        if (is_array($type) && is_array($attribute) && is_array($operator) && is_array($value)){
        
            foreach($type as $order => $typeCode){
                $operatorCode = $operator[$order];
                $valueCode = $this->_getOutputValue($value[$order]);
                $attributeCode = $attribute[$order];
                
                switch($typeCode){
                    case Amasty_Feed_Model_Filter::$_TYPE_ATTRIBUTE:
                        $ret = $this->_compare($productData, $attributeCode, $operatorCode, $valueCode);
                        break;
                    case Amasty_Feed_Model_Filter::$_TYPE_OTHER:
                        $compoundAttribute = $this->_getCompoundAttribute($attributeCode);
                        
                        $ret = $compoundAttribute->validateFilterCondition($productData, $operatorCode, $valueCode);
                        break;
                }
                
                
                
                if ($ret === FALSE){
                    break;
                }
            }
        }
        
        return $ret;
    }
    
    protected function _getAttributeHelper(){
        if (!$this->_attributeHelper){
            $this->_attributeHelper = Mage::helper("amfeed/attribute");
        }
        return $this->_attributeHelper;
    }
    
    protected function _getCompoundAttribute($code){
        return $this->_getAttributeHelper()
                ->getCompoundAttribute($code)
                ->init($this->getField()->getFeed());
    }
    
    protected function _compareCategories($productData, $operator, $value){
//        $ret = FALSE;
//        $ids = $product->getCategoryIds();
//        
//        switch ($operator){
//            case "eq":
//                $ret = in_array($value, $ids);
//                break;
//            case "neq":
//                $ret = !in_array($value, $ids);
//                break;
//        }
//        return $ret;
    }
    
    protected function _compare($productData, $code, $operator, $value){
        $productValue = isset($productData[$code]) ? $productData[$code] : null;
        
        return self::compare($operator, $productValue, $value);
    }
    
    static function compare($operator, $productValue, $compareValue){
        $ret = FALSE;
        
        switch ($operator){
            case "eq":
                $productValueArr = explode(',', $productValue);
                if (count($productValueArr) > 0 ){
                    $ret = in_array($compareValue, $productValueArr);
                } else {
                    $ret = $productValue == $compareValue;
                }
                break;
            case "neq":
                $ret = $productValue != $compareValue;
                break;
            case "gt":
                $ret = $productValue > $compareValue;
                break;
            case "lt":
                $ret = $productValue < $compareValue;
                break;
            case "gteq":
                $ret = $productValue >= $compareValue;
                break;
            case "lteq":
                $ret = $productValue <= $compareValue;
                break;
            case "like":
                $ret = mb_strpos($productValue, $compareValue) !== FALSE;
                break;
            case "nlike":
                $ret = mb_strpos($productValue, $compareValue) === FALSE;
                break;
            case "isempty":
                $ret = empty($productValue);
                break;
            case "isnotempty":
                $ret = !empty($productValue);
                break;
        }
        
        return $ret;
    }
    
    protected function _getOutputValue($val){
        $ret = $val;
        
        return $ret;
    }
}