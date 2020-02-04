<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Storepickup
 * @module      Storepickup
 * @author      Magestore Developer
 *
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */

/**
 * Class Magestore_Storepickup_Block_Adminhtml_Holiday_Grid
 */
class Magestore_Storepickup_Block_Adminhtml_Holiday_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    /**
     * Magestore_Storepickup_Block_Adminhtml_Holiday_Grid constructor.
     */
    public function __construct() {
		parent::__construct();
		$this->setId('holidayGrid');
		$this->setDefaultSort('holiday_id');
		$this->setDefaultDir('DESC');
		$this->setSaveParametersInSession(true);
	}

    /**
     * @return mixed
     */
    protected function _prepareCollection() {
		$collection = Mage::getModel('storepickup/holiday')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

    /**
     * @return mixed
     */
    protected function _prepareColumns() {
		$this->addColumn('holiday_id', array(
			'header' => Mage::helper('storepickup')->__('ID'),
			'align' => 'right',
			'width' => '50px',
			'index' => 'holiday_id',
		));

		$this->addColumn('holiday_name', array(
			'header' => Mage::helper('storepickup')->__('Holiday Name'),
			'align' => 'left',
			'width' => '300',
			'index' => 'holiday_name',

		));

		$this->addColumn('store_id', array(
			'header' => Mage::helper('storepickup')->__('Store'),
			'align' => 'left',
			'width' => '300',
			'index' => 'store_id',
			'renderer' => 'Magestore_Storepickup_Block_Adminhtml_Holiday_Renderer_Store',
		));

		$this->addColumn('date', array(
			'header' => Mage::helper('storepickup')->__('Starting Date'),
			'align' => 'left',
			'width' => '200',
			'type' => 'date',
			'format' => 'F',
			'index' => 'date',
		));

		$this->addColumn('holiday_date_to ', array(
			'header' => Mage::helper('storepickup')->__('End Date'),
			'align' => 'left',
			'width' => '200',
			'type' => 'date',
			'format' => 'F',
			'index' => 'holiday_date_to',
		));

		$this->addColumn('comment', array(
			'header' => Mage::helper('storepickup')->__('Comment'),
			'index' => 'comment',
		));

		$this->addColumn('action', array(
			'header' => Mage::helper('storepickup')->__('Action'),
			'width' => '100',
			'type' => 'action',
			'getter' => 'getId',
			'actions' => array(
				array(
					'caption' => Mage::helper('storepickup')->__('Edit'),
					'url' => array('base' => '*/*/edit'),
					'field' => 'id',
				),
			),
			'filter' => false,
			'sortable' => false,
			'index' => 'stores',
			'is_system' => true,
		));

		$this->addExportType('*/*/exportCsv', Mage::helper('storepickup')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('storepickup')->__('XML'));

		return parent::_prepareColumns();
	}

    /**
     * @return $this
     */
    protected function _prepareMassaction() {
		$this->setMassactionIdField('holiday_id');
		$this->getMassactionBlock()->setFormFieldName('holiday');

		$this->getMassactionBlock()->addItem('delete', array(
			'label' => Mage::helper('storepickup')->__('Delete'),
			'url' => $this->getUrl('*/*/massDelete'),
			'confirm' => Mage::helper('storepickup')->__('Are you sure?'),
		));

		return $this;
	}

    /**
     * @param $row
     * @return mixed
     */
    public function getRowUrl($row) {
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}

}
