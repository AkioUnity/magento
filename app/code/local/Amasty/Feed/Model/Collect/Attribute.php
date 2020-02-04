<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */  
class Amasty_Feed_Model_Collect_Attribute extends Amasty_Feed_Model_Collect_Abstract
{
    protected static $_attributes = array(
        'sku' => true
    );
    
    function getOptions(){
        $options = array();
        $collection = Mage::getResourceModel('eav/entity_attribute_collection')
            ->setItemObjectClass('catalog/resource_eav_attribute')
            ->setEntityTypeFilter(Mage::getResourceModel('catalog/product')->getTypeId());

//        $collection->addFieldToFilter('main_table.is_user_defined', array('eq' => 1));
        $collection->setOrder('main_table.frontend_label', Mage_Core_Model_Resource_Db_Collection_Abstract::SORT_ORDER_ASC);
                
        foreach ($collection as $attribute) {
            $options[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
            
        }  
        
        return $options;
        
    }
    
    function collect($feed, $collection, $code)
    {
        $collection->addAttribute($code, $feed->getStoreId());
    }
    
}