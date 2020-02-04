<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */ 
class Amasty_Feed_Block_Adminhtml_Category_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('categoryGrid');
        $this->setDefaultSort('feed_id');
    }
    
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('amfeed/category')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        $hlp =  Mage::helper('amfeed'); 
        $this->addColumn('feed_category_id', array(
          'header'    => $hlp->__('ID'),
          'align'     => 'right',
          'width'     => '50px',
          'index'     => 'feed_category_id',
        ));

        $this->addColumn('code', array(
          'header'    => $hlp->__('Code'),
          'index'     => 'code',
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
}