<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

class Amasty_Feed_Model_Google_Size extends Amasty_Feed_Model_Google_Abstract
{
    protected $_type = 'attribute';

    protected $_tag = 'g:size';

    protected $_format = 'html_escape';

    protected $_value = 'size';

    protected $_name = 'size';

    protected $_description = 'Size of the item';
}