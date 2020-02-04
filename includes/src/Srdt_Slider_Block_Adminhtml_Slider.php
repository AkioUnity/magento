<?php
 
class Srdt_Slider_Block_Adminhtml_Slider extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
  
        $this->_blockGroup = 'srdt_slider';
        $this->_controller = 'adminhtml_slider';
        $this->_headerText = Mage::helper('srdt_slider')->__('Srdt Banner Slider');
 
        parent::__construct();
   
    }
}
