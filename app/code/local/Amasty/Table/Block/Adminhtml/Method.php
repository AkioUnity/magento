<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Table
 */ 
class Amasty_Table_Block_Adminhtml_Method extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_method';
        $this->_blockGroup = 'amtable';
        $this->_headerText = Mage::helper('amtable')->__('Methods');
        $this->_addButtonLabel = Mage::helper('amtable')->__('Add Method');
        parent::__construct();
    }
}