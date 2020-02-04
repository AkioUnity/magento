<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

class Amasty_Feed_Model_Google_Noidentifierexists extends Amasty_Feed_Model_Google_Abstract
{
    protected $_type = Amasty_Feed_Model_Google::TYPE_CUSTOM_FIELD;

    protected $_tag = 'g:identifier_exists';

    protected $_format = 'as_is';

    protected $_value = 'identifier_exists';

    protected $_template = '<:tag>FALSE</:tag>';
}