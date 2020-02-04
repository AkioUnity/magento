<?php
	
class Biztech_Fedex_Block_Adminhtml_Pickup_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
		public function __construct()
		{

				parent::__construct();
				$this->_objectId = "pickup_id";
				$this->_blockGroup = "fedex";
				$this->_controller = "adminhtml_pickup";
				$this->_updateButton("save", "label", Mage::helper("fedex")->__("Save Pickup"));
				$this->_updateButton("delete", "label", Mage::helper("fedex")->__("Delete Pickup"));

				




				$this->_addButton("saveandcontinue", array(
					"label"     => Mage::helper("fedex")->__("Save And Continue Edit"),
					"onclick"   => "saveAndContinueEdit()",
					"class"     => "save",
				), -100);



				$this->_formScripts[] = "
					function saveAndContinueEdit(){
						editForm.submit($('edit_form').action+'back/edit/');
					}
				";
		}

		public function getHeaderText()
		{
				if( Mage::registry("pickup_data") && Mage::registry("pickup_data")->getId() ){

				    return Mage::helper("fedex")->__("Edit Pickup #%s", $this->htmlEscape(Mage::registry("pickup_data")->getId()));

				} 
				else{

				     return Mage::helper("fedex")->__("Add Pickup");

				}
		}
}