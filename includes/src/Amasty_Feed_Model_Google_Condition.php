<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

class Amasty_Feed_Model_Google_Condition extends Amasty_Feed_Model_Google_Abstract
{
    protected $_type = 'text';

    protected $_tag = 'g:condition';

    protected $_format = 'as_is';

    protected $_required = true;

    protected $_name = 'condition';

    protected $_description = 'Condition or state of the item (allowed values: new, refubrished, used)';

    protected $_value = 'new';
}