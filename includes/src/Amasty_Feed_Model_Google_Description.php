<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

class Amasty_Feed_Model_Google_Description extends Amasty_Feed_Model_Google_Abstract
{
    protected $_type = 'attribute';

    protected $_tag = 'description';

    protected $_limit = 500;

    protected $_format = 'html_escape';

    protected $_value = 'description';

    protected $_required = true;

    protected $_name = 'description';

    protected $_description = 'Description of the item';
}