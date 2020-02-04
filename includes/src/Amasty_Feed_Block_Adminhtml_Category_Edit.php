<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */
class Amasty_Feed_Block_Adminhtml_Category_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id'; 
        $this->_blockGroup = 'amfeed';
        $this->_controller = 'adminhtml_category';
        
        $this->_removeButton('reset'); 
        
        $this->_addButton('save_and_continue', array(
                'label'     => Mage::helper('amfeed')->__('Save and Continue Edit'),
                'onclick'   => 'saveAndContinueEdit()',
                'class' => 'save'
            ), 10);

        $this->_formScripts[] = "function saveAndContinueEdit(){ editForm.submit($('edit_form').action + 'continue/edit') }";
    }

    public function getHeaderText()
    {
        $model = Mage::registry('amfeed_category');
        if ($model->getId()) {
            return Mage::helper('amfeed')->__("Edit Category Mapping `%s`", $this->escapeHtml($model->getName()));
        }
        else {
            return Mage::helper('amfeed')->__('New Category Mapping');
        }
    }     
    
}