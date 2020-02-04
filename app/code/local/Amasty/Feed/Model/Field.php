<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */  
class Amasty_Feed_Model_Field extends Amasty_Feed_Model_Filter
{
    protected $_feed;
    
    public function _construct()
    {    
        $this->_init('amfeed/field');
    }
    
    public function init($feed){
        $this->_feed = $feed;
        return $this;
    }
    
    public function getFeed(){
        return $this->_feed;
    }
    
    public function getMappingConfig(){
        return unserialize($this->getMapping());
    }
    
    public function getAdvencedAttributes(){
        $retAttrs = array();
        
        $condition = $this->getCondition();
        
        foreach($condition as $value){
            if (array_key_exists("condition", $value) &&
                array_key_exists("type", $value["condition"])){
                    foreach($value["condition"]["type"] as $order => $type){
                        $attributeCode = $value["condition"]["attribute"][$order];
                        $retAttrs[$attributeCode] = $attributeCode;
                    }      
            }
            
            if (array_key_exists("output", $value) ){
                foreach($value["output"] as $output){
                    if (isset($output['attribute']))
                        $retAttrs[$output['attribute']] = $output['attribute'];
                }
            }
        }
        
        return array_values($retAttrs);
    }
    
    public function hasCategory(){
        
        $condition = $this->getCondition();
        
        foreach($condition as $value){
            if (array_key_exists("condition", $value) &&
                array_key_exists("type", $value["condition"])){
                foreach($value["condition"]["type"] as $order => $type){
                    if ($type == Amasty_Feed_Model_Filter::$_TYPE_OTHER){
                        $code = $value["condition"]["attribute"][$order];
                         
                        if ($code == 'category'){
                            return true;
                        }
                    }
                }
                
            }
        }
       
        return false;
    }
    
    public function loadValue($productData){
        $ret = null;
        $conditionArr = $this->getCondition();
        
        foreach($conditionArr as $value){
//            print_r($value['condition']['operator']);
            
            $condition = Mage::getModel('amfeed/field_condition')->init($this);
            
            if (array_key_exists("condition", $value)){
                $condition->setData($value['condition']);
            }
            
            if ($condition->validate($productData)){
                
                $ouput = Mage::getModel('amfeed/field_output')->init($this);
                $modification = Mage::getModel('amfeed/field_modification')->init($this);
                
                if (array_key_exists("output", $value)){
                    $ouput->setData($value['output']);
                }
                
                if (array_key_exists("modification", $value)){
                    $modification->setData($value['modification']);
                }
                
                $outputVal = $ouput->getValue($productData);
                
                $ret = $modification->modify($outputVal);
                
                break;
            }
        }
        
        $ret = $this->_replace($ret);
        
        if ($ret === NULL || $ret == ""){
            $ret = $this->getDefaultValue();
        }
        
        return $ret;
    }
    
    
    protected function _replace($value){
        $configMapping = $this->getMappingConfig();
        
        if (!empty($value) && isset($configMapping['from']) && isset($configMapping['to'])){
            
            
            foreach($configMapping['from'] as $order => $from){
                if (isset($configMapping['to'][$order])) {
                    $to = $configMapping['to'][$order];
                    $value = str_replace($from, $to, $value);
                }
            }
        }
        
        return $value;
    }
    
    
    public function getCondition(){
        
        $condition = parent::getCondition();

        /**************CONDITION MIGRATION FROM 2.X VERSIONS*******************/
        if (count($condition) === 1){
            
            if (empty($condition[1]['output']['static']) && 
                    empty($condition[1]['modification']['value'])){
                
                $baseAttr = $this->getBaseAttr();
                if (!empty($baseAttr)) {
                    $condition[1]['output'] = array(
                        array('attribute' => $baseAttr)
                    );
                }
                
                $transform = $this->getTransform();
                
                if (!empty($transform)) {
                    $condition[1]['modification']['value'] = $transform;
                }
            }
        }
        
        foreach($condition as &$record){
            
            if (isset($record['output']['static']) 
                    && isset($record['output']['attribute'])){
                
                $newOutput = array();
                foreach($record['output']['attribute'] as $attr){
                    $newOutput[] = array('attribute' => $attr);
                }
                
                if (!empty($record['output']['static']))
                    $newOutput[] = array('static' => $record['output']['static']);
                
                $record['output'] = $newOutput;
            }
            
            
            
        }
        /**********************************************************************/

        return $condition;
    }
    
}