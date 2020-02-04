<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

class Amasty_Feed_Model_Google_Category extends Amasty_Feed_Model_Google_Abstract
{
    protected $_type = Amasty_Feed_Model_Google::TYPE_CATEGORY;

    protected $_tag = 'g:google_product_category';

    protected $_format = 'html_escape';

    public function setValue($value)
    {
        $this->_value = $value;
    }
}