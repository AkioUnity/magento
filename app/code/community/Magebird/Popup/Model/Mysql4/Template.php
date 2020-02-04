<?php

class Magebird_Popup_Model_Mysql4_Template extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        // Note that the blog_id refers to the key field in your database table.
        $this->_init('magebird_popup/template', 'template_id');
    }

    public function load(Mage_Core_Model_Abstract $object, $value, $field = null)
    {
        if (strcmp($value, (int)$value) !== 0) {
            $field = 'popup_id';
        }
        return parent::load($object, $value, $field);
    }
}