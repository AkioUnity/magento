<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

class Amasty_Feed_Block_Adminhtml_Control_Field extends Amasty_Feed_Block_Adminhtml_Control
{
    public function __construct(){
        $this->_templates = 'field';
        
        return parent::__construct();
    }
}