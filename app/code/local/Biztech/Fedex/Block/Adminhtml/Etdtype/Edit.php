<?php
	
class Biztech_Fedex_Block_Adminhtml_Etdtype_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
		public function __construct()
		{



				
				parent::__construct();
				$this->_objectId = "etdtype_id";
				$this->_blockGroup = "fedex";
				$this->_controller = "adminhtml_etdtype";
				$this->_updateButton("save", "label", Mage::helper("fedex")->__("Save ETD"));
				$this->_updateButton("delete", "label", Mage::helper("fedex")->__("Delete ETD"));
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
				if( Mage::registry("etdtype_data") && Mage::registry("etdtype_data")->getId() ){

				    return Mage::helper("fedex")->__("Edit ETD '%s'", $this->htmlEscape(Mage::registry("etdtype_data")->getId()));

				} 
				else{

				     return Mage::helper("fedex")->__("Add ETD");

				}
		}
}