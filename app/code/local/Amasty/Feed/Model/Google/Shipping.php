<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

class Amasty_Feed_Model_Google_Shipping extends Amasty_Feed_Model_Google_Abstract
{
    protected $_type = 'attribute';

    protected $_value = 'shipping';

    protected $_template = '<g:shipping>
    <g:country>::country</g:country>
    <g:price>0</g:price>
</g:shipping>';

    protected function _getEvaluateData($config){
        $data = parent::_getEvaluateData($config);
        $data['::country'] = Mage::getStoreConfig('general/country/default');
        return $data;
    }
}