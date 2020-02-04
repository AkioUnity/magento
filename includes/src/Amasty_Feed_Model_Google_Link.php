<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

class Amasty_Feed_Model_Google_Link extends Amasty_Feed_Model_Google_Abstract
{
    protected $_type = 'attribute';

    protected $_tag = 'link';

    protected $_limit = 2000;

    protected $_format = 'html_escape';

    protected $_value = 'configurableurl';

    protected $_name = 'link';

    protected $_description = "URL directly linking to your item's page on your website";

    protected $_required = true;
}