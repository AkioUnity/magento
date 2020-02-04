<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */ 
class Amasty_Feed_Block_Adminhtml_Profile extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_profile';
        $this->_headerText = Mage::helper('amfeed')->__('Manage Feeds');
        $this->_blockGroup = 'amfeed';

        $this->_addButton('google', array(
            'label'     => Mage::helper('amfeed')->__('Setup Google Feed'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('*/amfeed_google/index') .'\')',
            'class'     => 'add',
        ));

        $this->_addButtonLabel = $this->__('Create Custom Feed');

        parent::__construct();

    }
}