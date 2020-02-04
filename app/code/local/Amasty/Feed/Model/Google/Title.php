<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

class Amasty_Feed_Model_Google_Title extends Amasty_Feed_Model_Google_Abstract
{
    protected $_type = 'attribute';

    protected $_tag = 'title';

    protected $_limit = 150;

    protected $_format = 'html_escape';

    protected $_value = 'name';

    protected $_required = true;

    protected $_name = 'title';

    protected $_description = 'Title of the item';
}