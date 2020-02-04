<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */  
class Amasty_Feed_Model_Field_Output extends Varien_Object
{
    protected $_field;
    
    public function init($field){
        $this->_field = $field;
        return $this;
    }
    
    public function getField(){
        return $this->_field;
    }
    
    public function getValue($productData){
        
        $ret = array();
        $feed = $this->getField()->getFeed();
        
        foreach($this->getData() as $item){
            if (isset($item['attribute'])){
                $val = $feed->getAttributeValue($item['attribute'], $productData);
                
                if (!empty($val))
                    $ret[] = $val;
                
            } else if (isset($item['static'])) {
                $ret[] = $item['static'];
            }
        }

        return implode("", $ret);
    }
}