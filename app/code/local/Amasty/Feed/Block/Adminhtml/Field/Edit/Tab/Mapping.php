<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */ 
class Amasty_Feed_Block_Adminhtml_Field_Edit_Tab_Mapping extends Amasty_Feed_Block_Adminhtml_Widget_Edit_Tab_Dynamic
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amasty/amfeed/field/mapping.phtml');
        $this->_fields  = array('from', 'to');
        $this->_model   = 'amfeed_field';     
    } 
}