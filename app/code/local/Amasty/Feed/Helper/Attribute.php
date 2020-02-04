<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */  
class Amasty_Feed_Helper_Attribute extends Mage_Core_Helper_Abstract
{
    public static $_OTHER_CONDITION_CATEGORY = 'category';
    public static $_OTHER_CONDITION_PRODUCT_ID = 'entity_id';
    public static $_OTHER_CONDITION_PARENT_ID = 'parent_id';
    public static $_OTHER_CONDITION_IN_STOCK = 'is_in_stock';
    public static $_OTHER_CONDITION_ENABLE_QTY_INCREMENTS = 'enable_qty_increments';
    public static $_OTHER_CONDITION_QTY_INCREMENTS = 'qty_increments';
    public static $_OTHER_CONDITION_QTY = 'qty';
    public static $_OTHER_CONDITION_CATEGORY_ID = 'category_id';
    public static $_OTHER_CONDITION_CATEGORY_NAME = 'category_name';
    public static $_OTHER_CONDITION_CATEGORIES = 'categories';
    public static $_OTHER_CONDITION_CREATED_AT = 'created_at';
    public static $_OTHER_CONDITION_URL = 'url';
    public static $_OTHER_CONDITION_CONFIGURABLE_URL = 'configurableurl';
    public static $_OTHER_CONDITION_TAX_PERCENTS = 'tax_percents';
    public static $_OTHER_CONDITION_TAX_AMOUNT = 'tax_amount';
    public static $_OTHER_CONDITION_STOCK_AVAILABILITY = 'stock_availability';
    public static $_OTHER_CONDITION_STOCK_AVAILABILITY_BASED_QTY = 'stock_availability_based_qty';
    public static $_OTHER_CONDITION_EFFECTIVE_DATE = 'sale_price_effective_date';
    public static $_OTHER_CONDITION_IDENTIFIER_EXISTS = 'identifier_exists';

    public static $_OTHER_CONDITION_TYPE_ID = 'type_id';
    
    public static $_PRICE_CONDITION_PRICE = 'default_price';
    public static $_PRICE_CONDITION_MIN_PRICE = 'min_price';
    public static $_PRICE_CONDITION_MAX_PRICE = 'max_price';
    public static $_PRICE_CONDITION_FINAL_PRICE = 'final_price';
    
    protected $_compaundAttributes;
    protected $_priceAttributes;


    public function getProductAttributes($withEmpty = FALSE, $emptyTitle = "None"){

        $attributes = array();

        if ($withEmpty){
            $attributes = array_merge(
                    $attributes, 
                    array("" => $emptyTitle)
                );
        }

        $collection = $collection = Mage::getResourceModel('eav/entity_attribute_collection')
            ->setItemObjectClass('catalog/resource_eav_attribute')
            ->setEntityTypeFilter(Mage::getResourceModel('catalog/product')->getTypeId());

        foreach($collection as $attribute){
            $label = $attribute->getFrontendLabel();
            if (!empty($label))
                $attributes[$attribute->getAttributeCode()] = $label;
        }

        return $attributes;
    }
    
    public function getCategories(){
        return Mage::helper('amfeed/category')->getOptionsForFilter();
    }

    public function getCompoundAttributes(){
        $hlr = Mage::helper("amfeed");

        if (!$this->_compaundAttributes) {
            $this->_compaundAttributes = array(
                self::$_OTHER_CONDITION_CATEGORY => $hlr->__('Category'),
                self::$_OTHER_CONDITION_PRODUCT_ID => $hlr->__('Product ID'),
                self::$_OTHER_CONDITION_PARENT_ID => $hlr->__('Parent ID'),
                self::$_OTHER_CONDITION_IN_STOCK => $hlr->__('In Stock'),
                self::$_OTHER_CONDITION_ENABLE_QTY_INCREMENTS => $hlr->__('Enable Qty Increments'),
                self::$_OTHER_CONDITION_QTY_INCREMENTS => $hlr->__('Qty Increments'),
                self::$_OTHER_CONDITION_QTY => $hlr->__('Qty'),
                self::$_OTHER_CONDITION_CATEGORY_ID => $hlr->__('Category ID'),
                self::$_OTHER_CONDITION_CATEGORY_NAME => $hlr->__('Category Name'),
                self::$_OTHER_CONDITION_CATEGORIES => $hlr->__('Categories'),
                self::$_OTHER_CONDITION_CREATED_AT => $hlr->__('Created At'),
                self::$_OTHER_CONDITION_URL => $hlr->__('Url'),
                self::$_OTHER_CONDITION_CONFIGURABLE_URL => $hlr->__('Url with predefined simple product options'),
                self::$_OTHER_CONDITION_TAX_PERCENTS => $hlr->__('Tax Percents'),
                self::$_OTHER_CONDITION_TAX_AMOUNT => $hlr->__('Tax Amount'),
                self::$_OTHER_CONDITION_STOCK_AVAILABILITY => $hlr->__('Stock Availability'),
                self::$_OTHER_CONDITION_STOCK_AVAILABILITY_BASED_QTY => $hlr->__('Stock Availability Based On Child Product Qty'),
                
                self::$_OTHER_CONDITION_EFFECTIVE_DATE => $hlr->__('Sale Price Effective Date'),
                self::$_OTHER_CONDITION_IDENTIFIER_EXISTS => $hlr->__('Identifier Exists'),
                self::$_OTHER_CONDITION_TYPE_ID => $hlr->__('Type ID'),

            );

            asort($this->_compaundAttributes);
        }
        
        return $this->_compaundAttributes;
    }
    
    public function getPriceAttributes(){
        $hlr = Mage::helper("amfeed");

        if (!$this->_priceAttributes) {
            $this->_priceAttributes = array(
                self::$_PRICE_CONDITION_PRICE => $hlr->__('Price'),
                self::$_PRICE_CONDITION_FINAL_PRICE => $hlr->__('Final Price'),
                self::$_PRICE_CONDITION_MIN_PRICE => $hlr->__('Minimal Price'),
                self::$_PRICE_CONDITION_MAX_PRICE => $hlr->__('Maximal Price'),
            );

//            asort($this->_priceAttributes);
        }

        return $this->_priceAttributes;
    }
    
    function getCompoundAttribute($code){
        return Mage::getModel("amfeed/attribute_compound_" . str_replace("_", "", $code));
    }
    
    public function isCompoundAttribute($code)
    {
        $attributes = array_merge($this->getPriceAttributes(), $this->getCompoundAttributes());
        return isset($attributes[$code]);
    }
}