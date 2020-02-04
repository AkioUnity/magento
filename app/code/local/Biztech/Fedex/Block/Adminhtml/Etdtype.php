<?php


class Biztech_Fedex_Block_Adminhtml_Etdtype extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{

	$this->_controller = "adminhtml_etdtype";
	$this->_blockGroup = "fedex";
	$this->_headerText = Mage::helper("fedex")->__("Etd Type Manager");
	$this->_addButtonLabel = Mage::helper("fedex")->__("Add ETD Type ");
	

	if (Mage::helper('fedex')->isEnable()) {
            parent::__construct();
    } else {
        
            Mage::getSingleton('adminhtml/session')->addError("Biztech Fedex is not active");
        
    }
	
	}

}