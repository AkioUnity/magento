<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

class Amasty_Feed_Model_Google_Price_EffectiveDate extends Amasty_Feed_Model_Google_Abstract
{
    protected $_type = 'attribute';

    protected $_tag = 'g:sale_price_effective_date';

    protected $_format = 'as_is';

    protected $_value = 'sale_price_effective_date';

    protected $_name = 'sale price effective date';

    protected $_description = 'Date range during which the item is on sale';
}