<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */
class Amasty_Feed_Block_Adminhtml_Google extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_google';
        $this->_headerText = Mage::helper('amfeed')->__('Setup Google Feed');
        $this->_blockGroup = 'amfeed';
        $this->_mode = 'edit';

        parent::__construct();

        $this->_removeButton('save');
        $this->_removeButton('reset');

        $this->_addButton('save_and_continue', array(
            'label'     => Mage::helper('salesrule')->__('Save and Continue'),
            'onclick'   => 'saveAndContinueEdit()',
            'class' => 'save'
        ), 10);

        $this->_formScripts[] = "function saveAndContinueEdit(){ editForm.submit($('edit_form').action + 'continue/edit'); } ";
    }
}