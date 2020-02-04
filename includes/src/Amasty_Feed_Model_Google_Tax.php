<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

class Amasty_Feed_Model_Google_Tax extends Amasty_Feed_Model_Google_Abstract
{
    protected $_type = 'attribute';

    protected $_tag = 'g:rate';

    protected $_format = 'as_is';

    protected $_value = 'tax_percents';

    protected $_name = 'tax';

    protected $_description = 'The tax rate as a percent of the item price, i.e., a number as a percentage';

    protected $_required = true;

    protected $_template = '<g:tax>
    <g:country>::country</g:country>
    <:tag>{type=":type" value=":value" format=":format" length=":length" optional=":optional" parent=":parent"}</:tag>
    <g:tax_ship>y</g:tax_ship>
</g:tax>';

    protected function _getEvaluateData($config){
        $data = parent::_getEvaluateData($config);
        $data['::country'] = Mage::getStoreConfig('general/country/default');
        return $data;
    }
}