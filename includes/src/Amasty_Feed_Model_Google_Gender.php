<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

class Amasty_Feed_Model_Google_Gender extends Amasty_Feed_Model_Google_Abstract
{
    protected $_type = 'attribute';

    protected $_tag = 'g:gender';

    protected $_format = 'html_escape';

//    protected $_value = 'gender';

    protected $_name = 'gender';

    protected $_description = 'Gender of the item';
}