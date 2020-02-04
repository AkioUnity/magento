<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */ 
class Amasty_Feed_Block_Adminhtml_Field_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('fieldGrid');
      $this->setDefaultSort('field_id');
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('amfeed/field')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
    $hlp =  Mage::helper('amfeed'); 
    $this->addColumn('field_id', array(
      'header'    => $hlp->__('ID'),
      'align'     => 'right',
      'width'     => '50px',
      'index'     => 'field_id',
    ));
    
    $this->addColumn('code', array(
        'header'    => $hlp->__('Code'),
        'index'     => 'code',
    ));
    
    $this->addColumn('title', array(
        'header'    => $hlp->__('Name'),
        'index'     => 'title',
    ));

    return parent::_prepareColumns();
  }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }
  
  protected function _prepareMassaction()
  {
    $this->setMassactionIdField('field_id');
    $this->getMassactionBlock()->setFormFieldName('fields');
    
    $this->getMassactionBlock()->addItem('delete', array(
         'label'    => Mage::helper('amfeed')->__('Delete'),
         'url'      => $this->getUrl('*/*/massDelete'),
         'confirm'  => Mage::helper('amfeed')->__('Are you sure?')
    ));
    
    return $this; 
  }

}