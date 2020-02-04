<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

class Amasty_Feed_Model_Google_Price_Sale extends Amasty_Feed_Model_Google_Abstract
{
    protected $_type = 'attribute';

    protected $_tag = 'g:sale_price';

    protected $_format = 'price';

    protected $_value = 'special_price';

    protected $_name = 'sale price';

    protected $_description = 'Advertised sale price of the item';
}