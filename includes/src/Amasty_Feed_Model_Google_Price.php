<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

class Amasty_Feed_Model_Google_Price extends Amasty_Feed_Model_Google_Abstract
{
    protected $_type = 'attribute';

    protected $_tag = 'g:price';

    protected $_format = 'price';

    protected $_value = 'default_price';

    protected $_name = 'price';

    protected $_description = 'Price of the item';

    protected $_required = true;

    protected $_template = '<:tag>
    {type=":type" value=":value" format=":format" length=":length" optional=":optional" parent=":parent"} ::currency
</:tag>';

    protected function _getEvaluateData($config){
        $data = parent::_getEvaluateData($config);
        $data['::currency'] = $this->getFeed()->getCurrency();
        return $data;
    }
}