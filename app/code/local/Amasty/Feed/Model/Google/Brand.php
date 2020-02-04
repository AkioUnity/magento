<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

class Amasty_Feed_Model_Google_Brand extends Amasty_Feed_Model_Google_Abstract
{
    protected $_type = 'attribute';

    protected $_tag = 'g:brand';

    protected $_format = 'html_escape';

    protected $_value = 'brand';

    protected $_name = 'brand';

    protected $_description = 'Brand of the item';
}