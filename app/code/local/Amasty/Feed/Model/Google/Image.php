<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

class Amasty_Feed_Model_Google_Image extends Amasty_Feed_Model_Google_Abstract
{
    protected $_type = Amasty_Feed_Model_Google::TYPE_ATTRIBUTE;

    protected $_tag = 'g:image_link';

    protected $_limit = 2000;

    protected $_format = 'as_is';

    protected $_value = 'thumbnail';

    protected $_name = 'image link';

    protected $_description = 'URL of an image of the item';

    protected $_required = true;
}