<?php
 
class Srdt_Slider_Block_Adminhtml_Slider_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {

        parent::__construct();
        $this->setId('Srdt_Slider_grid');
        $this->setDefaultSort('custommodule_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
 
    protected function _prepareCollection()
    {

      $collection = Mage::getModel('slider/slider')->getCollection();
    
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    
    protected function _prepareColumns()
  {

      $this->addColumn('banner_id', array(
          'header'    => Mage::helper('srdt_slider')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'banner_id',
      ));
      
      $this->addColumn('banner_title', array(
          'header'    => Mage::helper('srdt_slider')->__('Banner Title'),
          'align'     =>'right',
          'width'     => '150px',
          'index'     => 'banner_title',
      ));     

      $this->addColumn('banner_caption', array(
          'header'    => Mage::helper('srdt_slider')->__('Caption'),
          'align'     =>'right',
           'width'     => '150px',
          'index'     => 'banner_caption',
         
      ));   
       $this->addColumn('banner_image', array(
          'header'    => Mage::helper('srdt_slider')->__('Image'),
          'align'     =>'right',
           'width'     => '150px',
          'index'     => 'banner_image',
      
      )); 
       $this->addColumn('banner_status', array(
          'header'    => Mage::helper('srdt_slider')->__('Status'),
          'align'     =>'right',
           'width'     => '25px',
           'index'     => 'banner_status',
            
      )); 
       $this->addColumn('banner_url', array(
          'header'    => Mage::helper('srdt_slider')->__('Banner OnCLick URL'),
          'align'     =>'left',
          'index'     => 'banner_url',
         
      )); 


       $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('srdt_slider')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getBannerId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('srdt_slider')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));        
      
           
        $this->addExportType('*/*/exportCsv', Mage::helper('srdt_slider')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('srdt_slider')->__('XML'));
      
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('banner_id');
        $this->getMassactionBlock()->setFormFieldName('Banner Slider');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('srdt_slider')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('srdt_slider')->__('Are you sure?')
        ));

        

        return $this;
    }

  public function getRowUrl($row)
  {

      return $this->getUrl('*/*/edit', array('id' => $row->getBannerId()));
  }

 
}