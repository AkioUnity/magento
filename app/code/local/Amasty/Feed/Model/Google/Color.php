<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

class Amasty_Feed_Model_Google_Color extends Amasty_Feed_Model_Google_Abstract
{
    protected $_type = 'attribute';

    protected $_tag = 'g:color';

    protected $_format = 'html_escape';

    protected $_value = 'color';

    protected $_name = 'color';

    protected $_description = 'Color of the item';
}