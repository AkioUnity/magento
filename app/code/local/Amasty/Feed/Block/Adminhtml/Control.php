<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

class Amasty_Feed_Block_Adminhtml_Control extends Mage_Core_Block_Template
{
    public static $_OTHER_CONDITION_CATEGORY = 'category';
    
    protected $_attribute = null;
    protected $_otherCondition = null;
    protected $_templates = 'filter';
    
    public function initTemplate($attribute){
        $this->_attribute = $attribute;
        
        if ($attribute){
            switch($attribute->getFrontendInput()){
                case "select":
                case "multiselect":
                case "boolean":
                    $this->setTemplate('amasty/amfeed/' . $this->_templates . '/conditions/select.phtml');
                    break;
                default:
                    $this->setTemplate('amasty/amfeed/' . $this->_templates . '/conditions/default.phtml');
                    break;
            }            
        }
        return $this;
    }
    
    public function initOtherConditionTemplate($code){
        
        $this->_otherCondition = $code;
        
        switch ($code){
            case self::$_OTHER_CONDITION_CATEGORY:
                $this->setTemplate('amasty/amfeed/' . $this->_templates . '/conditions/category.phtml');
                break;
            default:
                $this->setTemplate('amasty/amfeed/' . $this->_templates . '/conditions/other.phtml');
                break;
        }
        return $this;
    }
    
    public function getConditions(){
        $ret = array();
        $helper = Mage::helper('amfeed/field');
        
        if ($this->_attribute){
            switch($this->_attribute->getFrontendInput()){
                case "select":
                    $ret = $helper->getSelectConditions();
                    break;
                default:
                    $ret = $helper->getDefaultConditions();
                    break;
            }            
        } else {
            $ret = $helper->getDefaultConditions();
        }
        
        
        return $ret;
    }
    
    public function getProductAttributes(){
        
        return Mage::helper('amfeed/attribute')->getProductAttributes();
    }
    
    public function getCompoundAttributes($checkHasCondition = FALSE, $checkHasFilterCondition = FALSE){
        $ret = array();

        $compoundAttributes = array_merge(
            Mage::helper('amfeed/attribute')->getCompoundAttributes(),
            Mage::helper('amfeed/attribute')->getPriceAttributes()
        );
        
        foreach($compoundAttributes as $code => $name){
            $attribute = Mage::helper("amfeed/attribute")->getCompoundAttribute($code);
            
            $isValid = true;
            
            if ($checkHasCondition)
                $isValid = $attribute->hasCondition();
            
            if ($checkHasFilterCondition)
                $isValid = $attribute->hasFilterCondition();;
            
            if ($isValid){
                $ret[$code] = $name;
            }
        }
        
        return $ret;
    }
    
    public function getPriceAttributes($checkHasCondition = FALSE, $checkHasFilterCondition = FALSE){
        $ret = array();
        $priceAttributes = Mage::helper('amfeed/attribute')->getPriceAttributes();

        foreach($priceAttributes as $code => $name){
            $attribute = Mage::helper("amfeed/attribute")->getCompoundAttribute($code);

            $isValid = true;

            if ($checkHasCondition)
                $isValid = $attribute->hasCondition();

            if ($checkHasFilterCondition)
                $isValid = $attribute->hasFilterCondition();;

            if ($isValid){
                $ret[$code] = $name;
            }
        }

        return $ret;
    }
    
    public function getCategories(){
        return Mage::helper('amfeed/attribute')->getCategories();
    } 
}