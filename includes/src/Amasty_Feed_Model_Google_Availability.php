<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

class Amasty_Feed_Model_Google_Availability extends Amasty_Feed_Model_Google_Abstract
{
    protected $_type = 'attribute';

    protected $_tag = 'g:availability';

    protected $_format = 'as_is';

    protected $_value = 'is_in_stock';

    protected $_name = 'availability';

    protected $_description = 'Availability status of the item';

    protected $_required = true;
}