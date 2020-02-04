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
 * Class Magestore_Storepickup_Block_Adminhtml_Storepickup_Grid
 */
class Magestore_Storepickup_Block_Adminhtml_Storepickup_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Magestore_Storepickup_Block_Adminhtml_Storepickup_Grid constructor.
     */
    public function __construct()
  {
      parent::__construct();
      $this->setId('storepickupGrid');
      $this->setDefaultSort('storepickup_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

    /**
     * @return mixed
     */
    protected function _prepareCollection()
  {
      $collection = Mage::getModel('storepickup/storepickup')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

    /**
     * @return mixed
     */
    protected function _prepareColumns()
  {
      $this->addColumn('storepickup_id', array(
          'header'    => Mage::helper('storepickup')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'storepickup_id',
      ));

      $this->addColumn('title', array(
          'header'    => Mage::helper('storepickup')->__('Title'),
          'align'     =>'left',
          'index'     => 'title',
      ));

	  /*
      $this->addColumn('content', array(
			'header'    => Mage::helper('storepickup')->__('Item Content'),
			'width'     => '150px',
			'index'     => 'content',
      ));
	  */

      $this->addColumn('status', array(
          'header'    => Mage::helper('storepickup')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('storepickup')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('storepickup')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('storepickup')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('storepickup')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('storepickup_id');
        $this->getMassactionBlock()->setFormFieldName('storepickup');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('storepickup')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('storepickup')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('storepickup/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('storepickup')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('storepickup')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

    /**
     * @param $row
     * @return mixed
     */
    public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}