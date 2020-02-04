<?php
class Biztech_Fedex_Block_Adminhtml_Pickup_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
		public function __construct()
		{
				parent::__construct();
				$this->setId("pickup_tabs");
				$this->setDestElementId("edit_form");
				$this->setTitle(Mage::helper("fedex")->__("Pickup Information"));
		}
		protected function _beforeToHtml()
		{
				$this->addTab("pickup_availability", array(
				"label" => Mage::helper("fedex")->__("Pickup Availability"),
				"title" => Mage::helper("fedex")->__("Pickup Availability"),
				"content" => $this->getLayout()->createBlock("fedex/adminhtml_pickup_edit_tab_form")->setTemplate('fedex/pickup/availability.phtml')->toHtml(),
				));

				$this->addTab("form_section", array(
				"label" => Mage::helper("fedex")->__("Pickup Information"),
				"title" => Mage::helper("fedex")->__("Pickup Information"),
				"content" => $this->getLayout()->createBlock("fedex/adminhtml_pickup_edit_tab_form")->toHtml(),
				));

				

				return parent::_beforeToHtml();
		}

}
