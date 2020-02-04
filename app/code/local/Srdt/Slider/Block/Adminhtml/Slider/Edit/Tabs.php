<?php

class Srdt_Slider_Block_Adminhtml_Slider_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {

    
      parent::__construct();
      $this->setId('enquiry_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('srdt_slider')->__('Banner Slider Manager'));
  }

  protected function _beforeToHtml()
  {

  $this->addTab('form_section', array(
          'label'     => Mage::helper('srdt_slider')->__('Banner Information'),
          'title'     => Mage::helper('srdt_slider')->__('Banner Information'),
          'content'   => $this->getLayout()->createBlock('srdt_slider/adminhtml_slider_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();

  }
}
