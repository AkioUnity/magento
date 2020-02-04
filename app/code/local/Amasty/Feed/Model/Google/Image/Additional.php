<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

class Amasty_Feed_Model_Google_Image_Additional extends Amasty_Feed_Model_Google_Abstract
{
    protected $_type = 'images';

    protected $_format = 'base';

    protected $_tag = 'g:additional_image_link';

    protected $_name = 'additional image link';

    protected $_description = 'Additional URLs of images of the item';

    protected $_required = false;

    public function setImageIdx($idx)
    {
        $this->_value = 'image_' . $idx;
        return $this;
    }
}