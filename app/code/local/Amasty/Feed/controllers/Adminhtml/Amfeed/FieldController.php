<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */     
class Amasty_Feed_Adminhtml_Amfeed_FieldController extends Amasty_Feed_Controller_Abstract
{
    public function preDispatch()
    {
        parent::preDispatch();
        $this->_tabs      =  false;
        $this->_modelName = 'field';
        $this->_title     = 'Field';
        $this->_dynamic   = array('mapping');
    } 
    
    protected function prepareForSave($model)
    {
        $advanced = Mage::app()->getRequest()->getParam('advanced', array());
        $model->setConditionSerialized(serialize($advanced));
        return parent::prepareForSave($model);
    }
    
}