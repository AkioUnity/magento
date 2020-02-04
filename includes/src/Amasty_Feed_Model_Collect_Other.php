<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */  
class Amasty_Feed_Model_Attribute_Other extends Amasty_Feed_Model_Attribute_Abstract
{
    function getOptions(){
        $hlr = Mage::helper("amfeed");
       
	$options = array();
        
        $options['entity_id']     = $hlr->__('Product ID');
        $options['parent_id']     = $hlr->__('Product Parent ID');
    	$options['is_in_stock']   = $hlr->__('In Stock');
    	$options['qty']           = $hlr->__('Qty');
    	$options['category_id']   = $hlr->__('Category ID');
    	$options['category_name'] = $hlr->__('Category Name');
        $options['categories'] = $hlr->__('Categories');
    	$options['created_at']    = $hlr->__('Created At');
        $options['url']           = $hlr->__('Url');
        $options['min_price']     = $hlr->__('Minimal Price');
        $options['max_price']     = $hlr->__('Maximal Price');
    	$options['final_price']   = $hlr->__('Final Price');
        $options['tier_price']    = $hlr->__('Tier Price');
        $options['tax_percents']  = $hlr->__('Tax Percents');
        $options['stock_availability']  = $hlr->__('Stock Availability');
        $options['sale_price_effective_date']  = $hlr->__('Sale Price Effective Date');
        $options['type_id']  = $hlr->__('Type ID');
        
        asort($options);
        
        return $options;
        
    }
    
}