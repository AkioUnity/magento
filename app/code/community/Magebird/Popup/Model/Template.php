<?php

class Magebird_Popup_Model_Template extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('magebird_popup/template');
    }

    public function load($id, $field = null)
    {
        return parent::load($id, $field);
    }
}