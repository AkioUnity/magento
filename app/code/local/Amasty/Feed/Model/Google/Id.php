<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

class Amasty_Feed_Model_Google_Id extends Amasty_Feed_Model_Google_Abstract
{
    protected $_type = 'attribute';

    protected $_tag = 'g:id';

    protected $_limit = 50;

    protected $_format = 'html_escape';

    protected $_value = 'sku';

    protected $_required = true;

    protected $_name = 'id';

    protected $_description = 'An identifier of the item';
}