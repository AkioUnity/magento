<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */  
class Amasty_Feed_Helper_Data extends Mage_Core_Helper_Abstract
{
//    protected $_attributes  = null;
//    protected $_excludeAttr = array('custom_design_from', 'custom_design_to', 'gift_message_available', 'custom_design',
//                                    'custom_layout_update', 'options_container', 'is_recurring', 'gallery', 'links_title',
//                                    'links_purchased_separately', 'media_gallery', 'minimal_price', 'page_layout', 'price_view',
//                                    'recurring_profile', 'samples_title', 'shipment_type', /*'small_image', */'small_image_label',
//                                    'tax_class_id', /*'thumbnail',*/ 'thumbnail_label', 'url_key');
    
    const TYPE_ATTRIBUTE_PRODUCT = 'product';
    const TYPE_ATTRIBUTE_OTHER = 'other';
    
    public function getNoYes($no='No', $yes='Yes')
    {
        $res = array(
            array(
                'value' => 0,
                'label' => Mage::helper('catalog')->__($no)
            ),
            array(
                'value' => 1,
                'label' => Mage::helper('catalog')->__($yes)
        ));
        
        return $res;
    }
    
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
    
    public function getAttributes()
    {
        
        $options = array(
            self::TYPE_ATTRIBUTE_PRODUCT => array(
                'label' => $this->__("Product Attributes"),
                'options' => $this->getOptions()
            ),
//            self::TYPE_ATTRIBUTE_OTHER => array(
//                'label' => $this->__("Other"),
//                'options' => $otherAttributes->getOptions()
//            )
        );
        
        return $options;
        
        
//        if ($this->_attributes){
//            return $this->_attributes;
//        }
//        
//        $collection = Mage::getResourceModel('eav/entity_attribute_collection')
//            ->setItemObjectClass('catalog/resource_eav_attribute')
//            ->setEntityTypeFilter(Mage::getResourceModel('catalog/product')->getTypeId());
//            
//        $options = array();
//		
//        $options['entity_id']     = $this->__('Product ID');
//        $options['parent_id']     = $this->__('Parent ID');
//    	$options['is_in_stock']   = $this->__('In Stock');
//    	$options['qty']           = $this->__('Qty');
//    	$options['category_id']   = $this->__('Category ID');
//    	$options['category_name'] = $this->__('Category Name');
//        $options['categories'] = $this->__('Categories');
//    	$options['created_at']    = $this->__('Created At');
//        $options['url']           = $this->__('Url');
//        $options['min_price']     = $this->__('Minimal Price');
//        $options['max_price']     = $this->__('Maximal Price');
//    	$options['final_price']   = $this->__('Final Price');
//        $options['tier_price']    = $this->__('Tier Price');
//        $options['tax_percents']  = $this->__('Tax Percents');
//        $options['stock_availability']  = $this->__('Stock Availability');
//        $options['sale_price_effective_date']  = $this->__('Sale Price Effective Date');
//        $options['type_id']  = $this->__('Type ID');
//		
//        foreach ($collection as $attribute) {
//            $label = $attribute->getFrontendLabel();
//                if ($label && !in_array($attribute->getAttributeCode(), $this->_excludeAttr)){ // skip system and `exclude` attributes
//                    $options[$attribute->getAttributeCode()] = $label;
//                }
//        }
//
//        asort($options);
//        $this->_attributes = $options;         
//        
//        return $this->_attributes;
    }
    
    public function getOperations()
    {
        $res = array();
        $res['eq']    = $this->__('equal');
        $res['neq']   = $this->__('not equal');
        $res['gt']    = $this->__('greater than');
        $res['lt']    = $this->__('less than');
        $res['gteq']  = $this->__('equal or greater than');
        $res['lteq']  = $this->__('equal or less than');
        $res['in']    = $this->__('is one of');
        $res['nin']   = $this->__('is not one of');
        $res['like']  = $this->__('contains');
        $res['nlike'] = $this->__('not contains');

        return $res;
    }
    
    public function getDeliveryTypes($vl = false)
    {
        if ($vl) {
            $res = array(
                array(
                    'value' => Amasty_Feed_Model_Profile::DELIVERY_TYPE_DLD,
                    'label' => Mage::helper('amfeed')->__('Download')
                ),
                array(
                    'value' => Amasty_Feed_Model_Profile::DELIVERY_TYPE_FTP,
                    'label' => Mage::helper('amfeed')->__('FTP')
                ),
                array(
                    'value' => Amasty_Feed_Model_Profile::DELIVERY_TYPE_SFTP,
                    'label' => Mage::helper('amfeed')->__('SFTP')
                ),
            );
        } else {
            $res = array(
                Amasty_Feed_Model_Profile::DELIVERY_TYPE_DLD => Mage::helper('amfeed')->__('Download'),
                Amasty_Feed_Model_Profile::DELIVERY_TYPE_FTP => Mage::helper('amfeed')->__('FTP'),
                Amasty_Feed_Model_Profile::DELIVERY_TYPE_SFTP => Mage::helper('amfeed')->__('SFTP'),
            );
        }
        
        return $res;
    }
    
    public function getProductTypes()
    {
        $res = array();
        $res['simple']       = $this->__('Simple');
        $res['grouped']      = $this->__('Grouped');
        $res['configurable'] = $this->__('Configurable');
        $res['virtual']      = $this->__('Virtual');
        $res['bundle']       = $this->__('Bundle');
        $res['downloadable'] = $this->__('Downloadable');
        
        return $res;
    }
    
    public function getAttributeSets(){
        $ret = array();
        
        
        $entityType = Mage::getModel('eav/entity')->setType(Mage_Catalog_Model_Product::ENTITY)->getTypeId();
        
        $collection = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter($entityType);
        
        foreach($collection as $item){
            $ret[$item->getId()] = $item->getAttributeSetName();
        }
        
        return $ret;
    }
    
    protected function _parseXmlPlaceholder($row){
        $ret = array();
        
        $params = array(
            "type", "value", "format", "length", "optional", "parent"
        );
        
        foreach($params as $param){
            $regex = "/$param=\"(.*?)\"/";
            preg_match($regex, $row, $vars);
            if (isset($vars[1])){
                $ret[$param] = $vars[1];
            }
        }
        
        return $ret;
        }
        
    public function parseXml($xml)
    {
        $fields = array();
        $lines2fields = array();
        
        $lines = explode("\n", $xml);
        
        $fieldOrder = 0;
        
        foreach ($lines as $key => $line) {
            
//            $temp = explode("{", $line);
//            if (isset($temp[0])) {
//                $fields['before'][$key] = $temp[0];
//            }
//            $temp = explode("}", $line);
//            if (isset($temp[1])) {
//                $fields['after'][$key] = $temp[1];
//            }
            
            $regex = "#{(.*?)}#";
            
            preg_match_all($regex, $line, $vars);
            
            if (isset($vars[1])) {
                
                $lines2fields[$key] = array(
                    'tpl' => $line,
                    'vars' => $vars[1],
                    'links' => array()
                );
                
                foreach($vars[1] as $var){
                    
                    $parsed = $this->_parseXmlPlaceholder($var);
                    
                    if (isset($parsed['type'])) {
                        $fields['type'][$fieldOrder] = $parsed['type'];
                    } else {
                        throw new Exception('type: is requred');
                    }
                    
                    $fields['format'][$fieldOrder] = isset($parsed['format']) ? $parsed['format'] : 'as_is';
                    $fields['image_format'][$fieldOrder] = $fields['format'][$fieldOrder];
                    $fields['length'][$fieldOrder] = isset($parsed['length']) ? $parsed['length'] : '';
                    $fields['optional'][$fieldOrder] = isset($parsed['optional']) ? $parsed['optional'] : 'no';
                    $fields['parent'][$fieldOrder] = isset($parsed['parent']) ? $parsed['parent'] : 'no';
                    
                    $fields['before'][$fieldOrder] = '';
                    $fields['after'][$fieldOrder] = '';
            
                    switch ($parsed['type']) {
                        case 'attribute':
                            $fields['attr'][$fieldOrder] = $parsed['value'];
                        break;
                        case 'category':
                            $fields['category'][$fieldOrder] = $parsed['value'];
                        break;
                        case 'custom_field':
                            $fields['custom'][$fieldOrder] = $parsed['value'];
                        break;
                        case 'text':
                            $fields['txt'][$fieldOrder] = $parsed['value'];
                        break;
                        case 'meta_tags':
                            $fields['meta_tags'][$fieldOrder] = $parsed['value'];
                        break;
                        case 'images':
                            $fields['images'][$fieldOrder] = $parsed['value'];
                        break;
                }
                    
                    $lines2fields[$key]['links'][] = $fieldOrder;
                    $fieldOrder++;
                }
            }

//            continue;
//                
//            if (isset($vars[1])) {
//                
//                
//                $params = explode("|", $vars[1]);
//                $fields['type'][$key] = $params[0];
//                $fields['format'][$key] = $params[2];
//                $fields['image_format'][$key] = $params[2];
//                $fields['length'][$key] = $params[3];
//                $fields['optional'][$key] = $params[4];
//                switch ($params[0]) {
//                    case '0':
//                        $fields['attr'][$key] = $params[1];
//                        break;
//                    case '1':
//                        $fields['custom'][$key] = $params[1];
//                        break;
//                    case '2':
//                        $fields['txt'][$key] = $params[1];
//                        break;
//                    case '3':
//                        $fields['meta_tags'][$key] = $params[1];
//                        break;
//                    case '4':
//                        $fields['images'][$key] = $params[1];
//                        break;
//                    case '5':
//                        $fields['parent_attribute'][$key] = $params[1];
//                        break;
//                }
//            }
        }
        
        $ret = array(
            'fields' => $fields,
            'lines2fields' => $lines2fields
        );
        
        return $ret;
    }
    
    public function checkDir($path)
    {
        if(!file_exists($path)) {
            mkdir($path);
            chmod($path, 0777);
        }
        return true;
    }
    
    public function deleteFile($path)
    {
        @unlink($path);
        return true;
    }
    
    public function getDownloadPath($folder = '', $fileName = '')
    {
        // media/amfeed dir
        $path = Mage::getBaseDir('media') . DS . 'amfeed';
        $this->checkDir($path);
        // media/amfeed/[images||feeds] dir
        if ($folder) {
            $path = $path . DS . $folder;
            $this->checkDir($path);
            return Mage::getBaseDir('media') . DS . 'amfeed'. DS . $folder . DS . $fileName;
        }
        return Mage::getBaseDir('media') . DS . 'amfeed'. DS;
    }
    
    function isMetaTagsInstalled(){
        
        return (string)Mage::getConfig()->getNode('modules/Amasty_Meta/active') == 'true';
    }
    
    
    protected function getCustomFieldData($placeholder){
        $formats = array(
            0 => 'as_is',
            1 => 'strip_tags',
            2 => 'html_escape',
            3 => 'date',
            4 => 'price',
            5 => 'lowercase',
            6 => 'integer'
        );
        
        $attribute = NULL;
        $format = NULL;
        
        $arrAttr = explode(':', $placeholder);

        if (count($arrAttr) == 2){
            $attribute = $arrAttr[0];

            if (in_array($arrAttr[1], $formats)){
                $format = $arrAttr[1];//array_search($arrAttr[1], $formats);
            }
        } else {
            $attribute = $placeholder;
        }
        
        return array(
            'attribute' => $attribute,
            'format' => $format,
        );
    }
    
    function getCustomFieldAttribute($placeholder){
        $ret = $this->getCustomFieldData($placeholder);
        return $ret['attribute'];
    }
    
    function getCustomFieldFormat($placeholder){
        $ret = $this->getCustomFieldData($placeholder);
        return $ret['format'];
    }
}