<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */ 
class Amasty_Feed_Block_Adminhtml_Profile_Edit_Tab_Condition extends Amasty_Feed_Block_Adminhtml_Widget_Edit_Tab_Dynamic
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amasty/amfeed/feed/condition.phtml');
        $this->_fields  = array('attr', 'op', 'val');
        $this->_model   = 'amfeed_profile';        
    } 

    public function getOperations()
    {
        return Mage::helper('amfeed')->getOperations();
    } 
    
    public function getAttributes()
    {
        return Mage::helper('amfeed')->getAttributes();
    }
    
    public function getProductTypes()
    {
        return Mage::helper('amfeed')->getProductTypes();
    }
    
    public function getAttributeSets()
    {
        return Mage::helper('amfeed')->getAttributeSets();
    }
    
    function getCustomBlocksHTML(){
        $ret = array();
        
        $customBlocks = Mage::helper("amfeed/profile")->getCustomBlocks();
        foreach($customBlocks as $key => $path){
            $ret[$key] = $this->getChildHTML($key);
        }
        
        $ret['conditions'] = $this->_getPreloadedConditions();

        return $ret;
    }
    
    protected function _getPreloadedConditions(){
        $ret = array();
        
        $layout = $this->getLayout();
        
        $condition = $this->getModel()->getCondition();
        
        foreach ($condition as $value) {
            
            if (array_key_exists('condition', $value) && 
                    array_key_exists('attribute', $value['condition'])){

                $types = $value['condition']['type'];

                foreach ($types as $order => $type) {

                    $code = $value['condition']['attribute'][$order];
                    
                    $html = "";
                    switch ($type){
                        case Amasty_Feed_Model_Filter::$_TYPE_OTHER:
                            $block = $layout
                                ->createBlock('amfeed/adminhtml_control_profile')
                                ->initOtherConditionTemplate($code);

                            $html = $block->toHtml();

                        break;
                        case Amasty_Feed_Model_Filter::$_TYPE_ATTRIBUTE:
                    $attribute = Mage::getSingleton('eav/config')
                                    ->getAttribute('catalog_product', $code);

                    $block = $layout
                                ->createBlock('amfeed/adminhtml_control_profile')
                        ->initTemplate($attribute);

                            $html = $block->toHtml();
                        break;

                    }

                    $ret[$type][$code] = $html;
                }
            }
        }
        
        return $ret;
    }
}