<?php

class Potato_FullPageCache_Model_System_Config_Backend_Generation extends Mage_Core_Model_Config_Data
{
    protected function _beforeSave()
    {
        if ($this->_isCanEnabled()) {
            return parent::_beforeSave();
        }
        $this->setValue(0);
        return $this;
    }

    protected function _isCanEnabled()
    {
        $options = Mage::app()->getRequest()->getParam('groups', array());
        if (!array_key_exists('general', $options) ||
            !array_key_exists('fields', $options['general']) ||
            !array_key_exists('use_user_agent', $options['general']['fields']) ||
            !array_key_exists('value', $options['general']['fields']['use_user_agent'])
        ) {
            return true;
        }
        return !(bool)$options['general']['fields']['use_user_agent']['value'];
    }
}