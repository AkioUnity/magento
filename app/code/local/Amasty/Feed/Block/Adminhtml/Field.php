<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */ 
class Amasty_Feed_Block_Adminhtml_Field extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_field';
        $this->_headerText = Mage::helper('amfeed')->__('Manage Custom Fields');
        $this->_blockGroup = 'amfeed';
        parent::__construct();
    }
}