<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

class Amasty_Feed_Model_Google extends Varien_Object
{
    const TYPE_ATTRIBUTE = 'attribute';
    const TYPE_CUSTOM_FIELD = 'custom_field';
    const TYPE_CATEGORY = 'category';
    const TYPE_IMAGE = 'image';
    const TYPE_TEXT = 'text';

    const VAR_CATEGORY_MAPPER = 'amfeed_category_mapper';
    const VAR_IDENTIFIER_EXISTS = 'amfeed_identifier_exists';
    const VAR_STEP = 'amfeed_step';
    const VAR_FEED_ID = 'amfeed_id';

    const MAX_ADDITIONAL_IMAGES = 10;

    protected $_attributes;

    protected function _createCategoryMapper()
    {
        $codePrefix = 'google_category_';
        $idx = 1;

        $categoryMapper = Mage::getModel('amfeed/category')->load($codePrefix . $idx, 'code');

        while(
            $categoryMapper->getId() !== null
        ){
            $idx++;
            $categoryMapper = Mage::getModel('amfeed/category')->load($codePrefix . $idx, 'code');
        }

        $categoryMapper = Mage::getModel('amfeed/category')
            ->setData(array(
                'code' => $codePrefix . $idx,
                'name' => 'Google Category #'.$idx,
            ));

        $categoryMapper->save();

        return $categoryMapper;
    }

    protected function _saveCategories($categoryMapper, $mapping)
    {
        $categoryMapper->setMapping($mapping);
        $categoryMapper->saveCategoriesMapping();
        return $categoryMapper;
    }

    protected function _setupCategories($requestData)
    {
        $categoryMapper = null;

        if (isset($requestData['feed_category_id'])) {
            $categoryMapper = Mage::getModel('amfeed/category')->load($requestData['feed_category_id']);
        }

        if (isset($requestData['mapping'])) {
            if (!isset($requestData['feed_category_id'])) {
                $categoryMapper = $this->_createCategoryMapper();
            }
            $this->_saveCategories($categoryMapper, $requestData['mapping']);
        } else if (!isset($requestData['feed_category_id'])) {
            Mage::throwException(Mage::helper('amfeed')->__('Please associate at least one category according to Google taxonomy'));
        }

        return $categoryMapper;
    }

    protected function _createIdentifierExists()
    {
        $codePrefix = 'google_identifier_exists_';
        $idx = 1;

        $identifierExists = Mage::getModel('amfeed/field')->load($codePrefix . $idx, 'code');

        while(
            $identifierExists->getId() !== null
        ){
            $idx++;
            $identifierExists = Mage::getModel('amfeed/field')->load($codePrefix . $idx, 'code');
        }

        $identifierExists = Mage::getModel('amfeed/field')
            ->setData(array(
                'code' => $codePrefix . $idx,
                'title' => 'Google Identifier Exists #'.$idx,
            ));

        $identifierExists->save();

        return $identifierExists;
    }

    protected function _setupIdentifierExists($requestData)
    {
        $identifierExists = Mage::getModel('amfeed/field');

        $attributesCodes = array();

        if (isset($requestData['optional']))
        {
            foreach($requestData['optional'] as $attribute){
                if ($attribute['code'] == 'mpn' || $attribute['code'] == 'gtin'){
                    if (!empty($attribute['attribute'])) {
                        $attributesCodes[] = $attribute['attribute'];
                    }
                }
            }
        }

        if (count($attributesCodes) > 0){

            if (isset($requestData['identifier_exists_id'])) {
                $identifierExists = Mage::getModel('amfeed/field')->load($requestData['identifier_exists_id']);
            }

            if (!isset($requestData['identifier_exists_id'])) {
                $identifierExists = $this->_createIdentifierExists();
            }

            $this->_saveIdentifierExistsCondition($identifierExists, $attributesCodes);
        }

        return $identifierExists;
    }

    protected function _saveIdentifierExistsCondition($identifierExists, $attributes = array())
    {
        $condition = array();

        foreach($attributes as $attribute){
            $condition[] = array(
                'condition' => array(
                    'type' => array('attribute'),
                    'attribute' => array($attribute),
                    'operator' => array('isnotempty'),
                    'value' => array('')
                ),
                'output' => array(
                    array(
                        'static' => 'TRUE',
                    )
                ),
                'modification' => array(
                    'value' => ''
                )
            );
        }

        $condition[] = array(
            'output' => array(
                array(
                    'static' => 'FALSE',
                )
            ),
            'modification' => array(
                'value' => ''
            )
        );

        $identifierExists->setConditionSerialized(serialize($condition));
        $identifierExists->save();
    }


    protected function _getXml($requestData, $feed, $categoryMapper, $identifierExists)
    {
        $xmlData = array();

        if (isset($requestData['basic'])){
            foreach($requestData['basic'] as $config){
                $attribute = $this->_loadAttribute($config['code'], $feed);
                $xmlData[] = $attribute->evaluate($config);
            }
        }

        $xmlData[] = $this->_loadAttribute('shipping')->evaluate();
        $xmlData[] = $this->_loadAttribute('availability')->evaluate();

        if ($categoryMapper->getId())
        {
            $categoryAttr = $this->_loadAttribute('category');
            $categoryAttr->setValue($categoryMapper->getCode());
            $xmlData[] = $categoryAttr->evaluate();
        }

        if ($identifierExists->getId())
        {
            $identifierexistsAttr = $this->_loadAttribute('identifierexists');
            $identifierexistsAttr->setValue($identifierExists->getCode());
            $xmlData[] = $identifierexistsAttr->evaluate();
        } else {
            $xmlData[] = $this->_loadAttribute('noidentifierexists')->evaluate();
        }

        if (isset($requestData['optional'])){
            foreach($requestData['optional'] as $config){
                $attribute = $this->_loadAttribute($config['code'], $feed);
                $row = $attribute->evaluate($config);
                if ($row) {
                    $xmlData[] = $row;
                }
            }
        }

        $attribute = $this->_loadAttribute('image_additional');

        for ($idx = 1; $idx <= self::MAX_ADDITIONAL_IMAGES; $idx ++){
            $attribute->setImageIdx($idx);
            $xmlData[] = $attribute->evaluate();
        }

        return implode("\n", $xmlData);
    }


    protected function _createFeed($requestData)
    {
        $feed = Mage::getModel('amfeed/profile')
            ->setData(array(
                'type' => Amasty_Feed_Model_Profile::TYPE_XML,
                'title' => 'Google Feed',
//                'filename' => 'google',
                'cond_stock' => '1',
                'cond_disabled' => '1',
                'cond_type' => 'simple,configurable,virtual,downloadable',
                'xml_header' => '<?xml version="1.0"?> <rss version="2.0" xmlns:g="http://base.google.com/ns/1.0"> <channel>',
                'xml_footer' => '</channel> </rss>',
                'xml_item' => 'item',
                'frm_date' => 'y.m.d',
                'frm_price' => 2,
                'frm_price_dec_point' => '.',
                'frm_price_thousands_sep' => ',',
                'frm_url' => '0',
                'frm_image_url' => '0',
                'frm_dont_use_category_in_url' => '1',
                'frm_use_parent' => '0',
                'max_images' => self::MAX_ADDITIONAL_IMAGES,
                'currency' => $requestData['currency']
            ));

        $feed->save();

        return $feed;
    }

    protected function _checkAndPasteVars($feed, $requestData, $vars)
    {
        foreach($vars as $var) {
            if (array_key_exists($var, $requestData)){
                $feed->setData($var, $requestData[$var]);
            }
        }
    }

    protected function _saveFeed($feed, $requestData, $categoryMapper, $identifierExists)
    {
        $this->_checkAndPasteVars($feed, $requestData, array(
            'store_id', 'currency',
            'ftp_host', 'ftp_user', 'ftp_pass',
            'ftp_is_passive', 'mode',
            'delivery_type', 'filename'
        ));

        $feed->setData('ftp_path', '/');

        $ftpHost = $feed->getData('ftp_host');

        if (empty($ftpHost)){
            $feed->setData('delivery_type',  Amasty_Feed_Model_Profile::DELIVERY_TYPE_DLD);
        }

        $feed->save();

        $feed->addData(array(
            'xml_body' => $this->_getXml($requestData, $feed, $categoryMapper, $identifierExists),
        ));

        $feed->save();
    }

    protected function _setupFeed($requestData, $categoryMapper, $identifierExists)
    {
        $feed = null;

        if (isset($requestData['feed_id'])) {
            $feed = Mage::getModel('amfeed/profile')->load($requestData['feed_id']);
        }

        if (isset($requestData['optional']) && isset($requestData['basic'])) {
            if (!isset($requestData['feed_id'])) {
                $feed = $this->_createFeed($requestData);
            }
            $this->_saveFeed($feed, $requestData, $categoryMapper, $identifierExists);
        }

        return $feed;

    }

    protected function _setSessionData($requestData)
    {
        Mage::getSingleton('admin/session')->setAmastyFeedGoogleRequestData(serialize($requestData));
    }

    protected function _getSessionData()
    {
        return @unserialize(Mage::getSingleton('admin/session')->getAmastyFeedGoogleRequestData());
    }

    public function setup($requestData)
    {
        $config = array(
            self::VAR_STEP => ++$requestData['step']
        );

        if (isset($requestData['feed_category_id'])) {
            $this->_setSessionData($requestData);
        } else {
            $this->_setSessionData(null);
        }

        $categoryMapper = $this->_setupCategories($requestData);
        $identifierExists = $this->_setupIdentifierExists($requestData);
        $feed = $this->_setupFeed($requestData, $categoryMapper, $identifierExists);

        if ($categoryMapper && $categoryMapper->getId()){
            $config[self::VAR_CATEGORY_MAPPER] =  $categoryMapper->getId();
        }

        if ($identifierExists && $identifierExists->getId()){
            $config[self::VAR_IDENTIFIER_EXISTS] =  $identifierExists->getId();
        }

        if ($feed && $feed->getId()){
            $config[self::VAR_FEED_ID] =  $feed->getId();
        }

        return $config;
    }

    protected function _loadAttribute($element, $feed = null)
    {
        return Mage::getModel('amfeed/google_' . $element)->init($element, $feed);
    }

    protected function _getAttributes()
    {
        $config = array(
            'id',
            'title',
            'description',
            'type',
            'link',
            'image',
            'condition',
//            'availability',
            'price',
            'price_sale',
            'price_effectivedate',
            'brand',
            'color',
            'size',
            'gender',
            'tax',
            'gtin',
            'mpn',
//            'identifierexists'
        );

        if (!$this->_attributes){
            $this->_attributes = array();

            foreach($config as $element){
                $this->_attributes[$element] = $this->_loadAttribute($element);
            }

            $this->_setAttributesData();
        }

        return $this->_attributes;
    }

    protected function _setAttributesData()
    {
        $sessionData = $this->_getSessionData();
        if (is_array($sessionData)){
            $attributesData = array();

            if (array_key_exists('basic', $sessionData)){
                $attributesData = array_merge($attributesData, $sessionData['basic']);
            }

            if (array_key_exists('optional', $sessionData)){
                $attributesData = array_merge($attributesData, $sessionData['optional']);
            }

            foreach($attributesData as $attributeElement){
                $code = $attributeElement['code'];
                if (isset($this->_attributes[$code])){
                    $this->_attributes[$code]->reloadData($attributeElement);
                }
            }
        }
    }

    public function getBasicAttributes()
    {
        $attributes = array();

        foreach($this->_getAttributes() as $idx => $attribute){
            if ($attribute->getRequired()){
                $attributes[$idx] = $attribute;
            }
        }

        return $attributes;
    }

    public function getOptionalAttributes()
    {
        $attributes = array();

        foreach($this->_getAttributes() as $idx => $attribute){
            if (!$attribute->getRequired()){
                $attributes[$idx] = $attribute;
            }
        }

        return $attributes;
    }

    public function getCurrency()
    {
        $currency = null;
        $sessionData = $this->_getSessionData();
        if (is_array($sessionData) && array_key_exists('currency', $sessionData)){
            $currency = $sessionData['currency'];
        }
        return $currency;
    }

    public function getStoreId()
    {
        $storeId = Mage::app()->getStore(true)->getId();
        $sessionData = $this->_getSessionData();
        if (is_array($sessionData) && array_key_exists('store_id', $sessionData)){
            $storeId = $sessionData['store_id'];
        }
        return $storeId;
    }
}