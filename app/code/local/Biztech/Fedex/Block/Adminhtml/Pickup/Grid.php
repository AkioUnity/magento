<?php

class Biztech_Fedex_Block_Adminhtml_Pickup_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

		public function __construct()
		{
				parent::__construct();
				$this->setId("pickupGrid");
				$this->setDefaultSort("pickup_id");
				$this->setDefaultDir("DESC");
				$this->setSaveParametersInSession(true);
		}

		protected function _prepareCollection()
		{
				$collection = Mage::getModel("fedex/pickup")->getCollection();
				$this->setCollection($collection);
				return parent::_prepareCollection();
		}
		protected function _prepareColumns()
		{
				$this->addColumn("pickup_id", array(
					"header" => Mage::helper("fedex")->__("ID"),
					"align" =>"right",
					"width" => "50px",
				    "type" => "number",
					"index" => "pickup_id",
				));
                
				$this->addColumn("person_name", array(
					"header" => Mage::helper("fedex")->__("Person Name"),
					"index" => "person_name",
				));

				$this->addColumn("phone_no", array(
					"header" => Mage::helper("fedex")->__("Phone No."),
					"index" => "phone_no",
				));
				$this->addColumn("pickup_city", array(
					"header" => Mage::helper("fedex")->__("City"),
					"index" => "pickup_city",
				));
				$this->addColumn("pickup_postcode", array(
					"header" => Mage::helper("fedex")->__("Postocode"),
					"index" => "pickup_postcode",
				));
				$this->addColumn("package_location", array(
					"header" => Mage::helper("fedex")->__("Location"),
					"index" => "package_location",
				));
				$this->addColumn("ready_timestamp", array(
					"header" => Mage::helper("fedex")->__("Ready Time Stamp"),
					"index" => "ready_timestamp",
				));
				$this->addColumn("shipment_id", array(
					"header" => Mage::helper("fedex")->__("Shipment No"),
					"index" => "shipment_id",
				));
				$this->addColumn("confirmation_no", array(
					"header" => Mage::helper("fedex")->__("Confirmation No"),
					"index" => "confirmation_no",
				));
				/*$this->addColumn("status", array(
					"header" => Mage::helper("fedex")->__("Status"),
					"index" => "status",
				));*/

$this->addColumn('status', array( 'header' => Mage::helper('fedex')->__('Status'), 'align' => 'left', 'width' => '80px', 'index' => 'status', 'type' => 'options', 'options' => array( 1 => 'Enable', 0 => 'Disable', ) ));





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
			$this->setMassactionIdField('pickup_id');
			$this->getMassactionBlock()->setFormFieldName('pickup_ids');
			$this->getMassactionBlock()->setUseSelectAll(true);
			$this->getMassactionBlock()->addItem('remove_pickup', array(
					 'label'=> Mage::helper('fedex')->__('Remove Pickup'),
					 'url'  => $this->getUrl('adminhtml/pickup/massRemove'),
					 'confirm' => Mage::helper('fedex')->__('Are you sure?')
			));
			$this->getMassactionBlock()->addItem('cancel_pickup', array(
					 'label'=> Mage::helper('fedex')->__('Cancel Pickup'),
					 'url'  => $this->getUrl('adminhtml/pickup/massCancel'),
					 'confirm' => Mage::helper('fedex')->__('Are you sure?')
			));

			return $this;
		}
			

}