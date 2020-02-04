<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

class Amasty_Feed_Model_Google_Type extends Amasty_Feed_Model_Google_Abstract
{
    protected $_type = 'attribute';

    protected $_tag = 'g:product_type';

    protected $_limit = 750;

    protected $_format = 'html_escape';

    protected $_value = 'category';

    protected $_name = 'product type';

    protected $_description = 'Your category of the item';
}