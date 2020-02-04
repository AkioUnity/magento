<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */
class Amasty_Feed_Block_Adminhtml_Profile_Edit_Tab_Content extends Amasty_Feed_Block_Adminhtml_Widget_Edit_Tab_Dynamic
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amasty/amfeed/feed/content.phtml');
        $this->_fields = array('name', 'before', 'type', 'attr', 'custom', 'txt', 'after', 'format');
        $this->_model  = 'amfeed_profile';
    }

    public function getAttributes()
    {
        $hlr = Mage::helper('amfeed/attribute');

        return array(
            'product' => array(
                'label'   => $hlr->__("Product Attributes"),
                'options' => $hlr->getProductAttributes()
            ),
            'price'   => array(
                'label'   => $hlr->__("Price"),
                'options' => $hlr->getPriceAttributes()
            ),
            'other'   => array(
                'label'   => $hlr->__("Other Attributes"),
                'options' => $hlr->getCompoundAttributes()
            ),

        );
    }

    public function getFieldTypes()
    {
        $types = array(
            'attribute'    => $this->__('Attribute'),
            'custom_field' => $this->__('Custom Field'),
            'category'     => $this->__('Categories'),
            'text'         => $this->__('Text'),
            'images'       => $this->__('Images'),
        );

        if (Mage::helper('amfeed')->isMetaTagsInstalled()) {
            $types['meta_tags'] = $this->__('Meta Tags');
        }

        return $types;
    }

    public function getProductImageFormats()
    {
        return array(
            'base'    => $this->__('Base Image'),
            '75x75'   => $this->__('75 x 75'),
            '135x135' => $this->__('135 x 135'),
            '265x265' => $this->__('265 x 265'),
        );
    }

    public function getFormats()
    {
        return array(
            'as_is'       => $this->__('As Is'),
            'strip_tags'  => $this->__('Strip Tags'),
            'html_escape' => $this->__('HTML Escape'),
            'date'        => $this->__('Date'),
            'price'       => $this->__('Price'),
            'lowercase'   => $this->__('Lowercase'),
            'integer'     => $this->__('Integer'),
        );
    }

    public function getMetaTags()
    {
        return array(
            'meta_title'        => $this->__('Title'),
            'meta_description'  => $this->__('Description'),
            'meta_keyword'      => $this->__('Keywords'),
            'short_description' => $this->__('Short Description'),
            'description'       => $this->__('Full Description'),

        );
    }

    public function getImagesFields()
    {
        $ret = array();

        $feed = Mage::registry('amfeed_profile');

        $maxImages = $feed->getMaxImages();

        if (!$maxImages) {
            $maxImages = 5;
        }

        for ($i = 1; $i <= $maxImages; $i++) {
            $ret['image_' . $i] = 'Image ' . $i;
        }

        return $ret;
    }

    public function getCustomFields()
    {
        $fields  = Mage::getModel('amfeed/field')->getCollection();
        $options = array();
        foreach ($fields as $field) {
            $options[$field->getCode()] = $field->getTitle();
        }

        return $options;
    }

    public function getCategories()
    {
        $fields  = Mage::getModel('amfeed/category')->getCollection();
        $options = array();
        foreach ($fields as $field) {
            $options[$field->getCode()] = $field->getName();
        }

        return $options;
    }

    public function getDelimiters()
    {
        $chars = array(
            ord(',')  => $this->__('Comma (,)'),
            ord(';')  => $this->__('Semicolon (;)'),
            ord('|')  => $this->__('Pipe (|)'),
            ord("\t") => $this->__('Tab'),
        );

        return $chars;
    }

    public function getEnclosures()
    {
        $chars = array(
            ord('"') => $this->__('Double Quote (")'),
            ord("'") => $this->__("Quote (')"),
            ord(' ') => $this->__('Space'),
            ord('n') => $this->__('None'),
        );

        return $chars;
    }

    public function getImageFormats()
    {
        return array(
            '0' => $this->__('Empty Value'),
            '1' => $this->__('Default Image'),
        );
    }

    public function getDefaultImageUrl()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'amfeed/images/';
    }

    public function getCurrencyList()
    {
        $currencyModel = Mage::getModel('directory/currency');

        $currencies = $currencyModel->getConfigAllowCurrencies();

        rsort($currencies);

        return $currencies;
    }
}
