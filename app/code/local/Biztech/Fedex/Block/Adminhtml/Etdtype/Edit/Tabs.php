<?php
class Biztech_Fedex_Block_Adminhtml_Etdtype_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
		public function __construct()
		{
			parent::__construct();
			$this->setId("etdtype_tabs");
			$this->setDestElementId("edit_form");
			$this->setTitle(Mage::helper("fedex")->__("ETD Information"));
		}
		protected function _beforeToHtml()
		{
			$this->addTab("form_section", array(
			"label" => Mage::helper("fedex")->__("ETD Information"),
			"title" => Mage::helper("fedex")->__("ETD Information"),
			"content" => $this->getLayout()->createBlock("fedex/adminhtml_etdtype_edit_tab_form")->toHtml(),
			));
			return parent::_beforeToHtml();
		}
}
