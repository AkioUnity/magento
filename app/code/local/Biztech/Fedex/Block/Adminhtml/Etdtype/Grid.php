<?php

class Biztech_Fedex_Block_Adminhtml_Etdtype_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

		public function __construct()
		{
				parent::__construct();
				$this->setId("etdtypeGrid");
				$this->setDefaultSort("etdtype_id");
				$this->setDefaultDir("DESC");
				$this->setSaveParametersInSession(true);
		}

		protected function _prepareCollection()
		{
				$collection = Mage::getModel("fedex/etdtype")->getCollection();
				$this->setCollection($collection);
				return parent::_prepareCollection();
		}
		protected function _prepareColumns()
		{
				$this->addColumn("etdtype_id", array(
					"header" => Mage::helper("fedex")->__("ID"),
					"align" =>"right",
					"width" => "50px",
				    "type" => "number",
					"index" => "etdtype_id",
				));
                
				$this->addColumn("name", array(
					"header" => Mage::helper("fedex")->__("Name"),
					"index" => "name",
				));

				$this->addColumn("title", array(
					"header" => Mage::helper("fedex")->__("Title"),
					"index" => "title",
				));
				$this->addColumn("content", array(
					"header" => Mage::helper("fedex")->__("Content"),
					"index" => "content",
				));
				


			$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV')); 
			$this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

				return parent::_prepareColumns();
		}

		public function getRowUrl($row)
		{
			   return $this->getUrl("*/*/edit", array("id" => $row->getId()));
		}


		
		protected function _prepareMassaction()
		{
			$this->setMassactionIdField('etdtype_id');
			$this->getMassactionBlock()->setFormFieldName('etdtype_ids');
			$this->getMassactionBlock()->setUseSelectAll(true);
			$this->getMassactionBlock()->addItem('remove_etdtype', array(
					 'label'=> Mage::helper('fedex')->__('Remove ETD'),
					 'url'  => $this->getUrl('adminhtml/etdtype/massRemove'),
					 'confirm' => Mage::helper('fedex')->__('Are you sure?')
			));
			

			return $this;
		}
			

}