<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Table
 */

/**
 * @method Amasty_Table_Model_Method_Store setStoreId($storeId)
 * @method int getStoreId()
 * @method Amasty_Table_Model_Method_Store setComment($comment)
 * @method string getComment()
 * @method Amasty_Table_Model_Method_Store setLabel($label)
 * @method string getLabel()
 */
class Amasty_Table_Model_Method_Store extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('amtable/method_store');
    }
}
