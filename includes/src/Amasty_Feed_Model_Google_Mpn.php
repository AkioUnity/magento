<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

class Amasty_Feed_Model_Google_Mpn extends Amasty_Feed_Model_Google_Abstract
{
    protected $_type = 'attribute';

    protected $_tag = 'g:mpn';

    protected $_format = 'html_escape';

    protected $_value = 'mpn';

    protected $_name = 'mpn';

    protected $_description = 'Manufacturer Part Number (MPN) of the item<br/>Please check <a target="_blank" href="https://support.google.com/merchants/answer/6219078?hl=en">here</a> for details on GTIN and MPN';

    protected $_limit = 70;
}