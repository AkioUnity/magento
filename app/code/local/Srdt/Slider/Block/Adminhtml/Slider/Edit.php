<?php

class Srdt_Slider_Block_Adminhtml_Slider_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
    
        $this->_objectId = 'id';
        $this->_blockGroup = 'srdt_slider';
        $this->_controller = 'adminhtml_slider';
            parent::__construct();
        $this->_updateButton('save', 'label', Mage::helper('srdt_slider')->__('Save Banner'));
        $this->_updateButton('delete', 'label', Mage::helper('srdt_slider')->__('Delete Banner'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
        // return $this;
    }

    public function getHeaderText()
    {
           if( Mage::registry('banner_data') && Mage::registry('banner_data')->getId() ) {
            return Mage::helper('srdt_slider')->__("Edit Banner '%s'", $this->htmlEscape(Mage::registry('banner_data')->getBannerTitle()));
        } else {
            return Mage::helper('srdt_slider')->__('Add Banner Slider');
        }

    }

}
