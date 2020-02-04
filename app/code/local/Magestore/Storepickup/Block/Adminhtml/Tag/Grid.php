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
 * Class Magestore_Storepickup_Block_Adminhtml_Tag_Grid
 */
class Magestore_Storepickup_Block_Adminhtml_Tag_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    /**
     * Magestore_Storepickup_Block_Adminhtml_Tag_Grid constructor.
     */
    public function __construct() {
		parent::__construct();
		$this->setId('tagGrid');
		$this->setDefaultSort('tag_id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}

    /**
     * @return mixed
     */
    protected function _prepareCollection() {
		$collection = Mage::getModel('storepickup/tag')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

    /**
     * @return mixed
     */
    protected function _prepareColumns() {
		$this->addColumn('tag_id', array(
			'header' => Mage::helper('storepickup')->__('ID'),
			'align' => 'right',
			'width' => '50px',
			'index' => 'tag_id',
		));

		$this->addColumn('title', array(
			'header' => Mage::helper('storepickup')->__('Name'),
			'align' => 'left',
			'index' => 'title',
		));

		$this->addColumn('status', array(
			'header' => Mage::helper('storepickup')->__('Status'),
			'align' => 'left',
			'width' => '80px',
			'index' => 'status',
			'type' => 'options',
			'options' => array(
				1 => 'Enabled',
				2 => 'Disabled',
			),
		));

		$this->addColumn('action',
			array(
				'header' => Mage::helper('storepickup')->__('Action'),
				'width' => '100',
				'type' => 'action',
				'getter' => 'getId',
				'actions' => array(
					array(
						'caption' => Mage::helper('storepickup')->__('Edit'),
						'url' => array('base' => '*/*/edit'),
						'field' => 'id',
					)),
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
		$this->setMassactionIdField('tag_id');
		$this->getMassactionBlock()->setFormFieldName('tag_id');

		$this->getMassactionBlock()->addItem('delete', array(
			'label' => Mage::helper('storepickup')->__('Delete'),
			'url' => $this->getUrl('*/*/massDelete'),
			'confirm' => Mage::helper('storepickup')->__('Are you sure?'),
		));

		$statuses = Mage::getSingleton('storepickup/status')->getOptionArray();

		array_unshift($statuses, array('label' => '', 'value' => ''));
		$this->getMassactionBlock()->addItem('status', array(
			'label' => Mage::helper('storepickup')->__('Change status'),
			'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
			'additional' => array(
				'visibility' => array(
					'name' => 'status',
					'type' => 'select',
					'class' => 'required-entry',
					'label' => Mage::helper('storepickup')->__('Status'),
					'values' => $statuses,
				)),
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