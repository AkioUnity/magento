<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

class Amasty_Feed_Model_Google_Gtin extends Amasty_Feed_Model_Google_Abstract
{
    protected $_type = 'attribute';

    protected $_tag = 'g:gtin';

    protected $_format = 'html_escape';

    protected $_value = 'gtin';

    protected $_name = 'gtin';

    protected $_description = 'Global Trade Item Number (GTIN) of the item';

    protected $_limit = 50;
}