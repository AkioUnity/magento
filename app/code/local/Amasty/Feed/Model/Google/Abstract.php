<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

class Amasty_Feed_Model_Google_Abstract extends Varien_Object
{
//    const TYPE_ATTRIBUTE = 'attribute';
//    const TYPE_CUSTOM_FIELD = 'custom_field';
//    const TYPE_IMAGE = 'image';
//    const TYPE_TEXT = 'text';

    protected $_type = '';

    protected $_value = '';

    protected $_code;

    protected $_tag;

    protected $_limit = '';

    protected $_format = 'as_is';

    protected $_required = false;

    protected $_name;

    protected $_description;

    protected $_template = '<:tag>{type=":type" value=":value" format=":format" length=":length" optional=":optional" parent=":parent"}</:tag>';

    protected $_feed;

    public function getTag()
    {
        return $this->_tag;
    }

    public function getLimit()
    {
        return $this->_limit;
    }

    public function getFormat()
    {
        return $this->_format;
    }

    public function getRequired()
    {
        return $this->_required;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function getDescription()
    {
        return $this->_description;
    }

    public function getCode()
    {
        return $this->_code;
    }

    public function getFeed()
    {
        return $this->_feed;
    }

    public function init($code, $feed = null)
    {
        $this->_code = $code;
        $this->_feed = $feed;

        return $this;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function getValue()
    {
        return $this->_value;
    }

    protected function _getEvaluateData($config){
        return array(
            ":tag" => $this->getTag(),
            ":type" => $this->getType(),
            ":value" => $this->getValue(),
            ":format" => $this->getFormat(),
            ":length" => $this->getLimit(),
            ":optional" => 'yes',
            ":parent" => 'no'
        );

    }

    public function evaluate($config = array())
    {
        $ret = null;

        $this->reloadData($config);

        $value = $this->getValue();
        $type = $this->getType();

        if (!empty($value) && !empty($type)) {
            $ret = strtr($this->_template, $this->_getEvaluateData($config));
        }

        return $ret;
    }

    public function reloadData($config)
    {
        if (array_key_exists('type', $config)){
            $this->_type = $config['type'];
        }

        if (array_key_exists($this->_type, $config)){
            $this->_value = $config[$this->_type];
        }
    }
}