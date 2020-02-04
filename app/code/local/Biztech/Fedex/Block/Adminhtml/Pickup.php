<?php


class Biztech_Fedex_Block_Adminhtml_Pickup extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{

	$this->_controller = "adminhtml_pickup";
	$this->_blockGroup = "fedex";
	$this->_headerText = Mage::helper("fedex")->__("Pickup Manager");
	$this->_addButtonLabel = Mage::helper("fedex")->__("Add Pickup Item");
	/*parent::__construct();*/


	if (Mage::helper('fedex')->isEnable()) {
            parent::__construct();
    } else {
        
            Mage::getSingleton('adminhtml/session')->addError("Biztech Fedex is not active");
        
    }

	
	}

}