<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */ 
class Amasty_Acart_Block_Adminhtml_Rule_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('ruleGrid');
        $this->setDefaultSort('rule_id');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('amacart/rule')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $hlp =  Mage::helper('amacart');
        $this->addColumn('rule_id', array(
          'header'    => $hlp->__('ID'),
          'align'     => 'right',
          'width'     => '50px',
          'index'     => 'rule_id',
        ));

        $this->addColumn('is_active', array(
            'header'    => Mage::helper('salesrule')->__('Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'is_active',
            'type'      => 'options',
            'options'   => $hlp->getStatuses()
        ));

        $this->addColumn('name', array(
            'header'    => $hlp->__('Name'),
            'index'     => 'name',
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('rule_id');
        $this->getMassactionBlock()->setFormFieldName('rule');

        $this->getMassactionBlock()->addItem('enable', array(
            'label'=> Mage::helper('amacart')->__('Enable'),
            'url'  => $this->getUrl('*/*/massEnable')
        ));

        $this->getMassactionBlock()->addItem('disable', array(
            'label'=> Mage::helper('amacart')->__('Disable'),
            'url'  => $this->getUrl('*/*/massDisable')
        ));
        
        $this->getMassactionBlock()->addItem('delete', array(
            'label'=> Mage::helper('amacart')->__('Delete'),
            'url'  => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('amacart')->__('Are you sure?')
        ));

        return $this;
    }
}