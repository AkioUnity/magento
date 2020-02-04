<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */ 
class Amasty_Feed_Block_Adminhtml_Profile_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('profileGrid');
        $this->setDefaultSort('feed_id');
    }
    
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('amfeed/profile')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        $hlp =  Mage::helper('amfeed'); 
        $this->addColumn('feed_id', array(
          'header'    => $hlp->__('ID'),
          'align'     => 'right',
          'width'     => '50px',
          'index'     => 'feed_id',
        ));
        
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => $hlp->__('Store'),
                'index'     => 'store_id',
                'type'      => 'store',
                'store_view'=> true,
            ));
        } 
        
        $this->addColumn('title', array(
            'header'    => $hlp->__('Name'),
            'index'     => 'title',
        )); 
           
        $this->addColumn('type', array(
            'header'    => $hlp->__('Type'),
            'index'     => 'type',
            'type'      => 'options',
            'width'     => '50px',
            'options'   => array(
                Amasty_Feed_Model_Profile::TYPE_CSV => $hlp->__('CSV'),
                Amasty_Feed_Model_Profile::TYPE_XML => $hlp->__('XML'),
                Amasty_Feed_Model_Profile::TYPE_TXT => $hlp->__('TXT'),
             ),
        ));       
        
        $this->addColumn('mode', array(
            'header'    => $hlp->__('Mode'),
            'index'     => 'mode',
            'type'      => 'options',
            'width'     => '90px',
            'options'   => Mage::getModel('amfeed/source_mode')->toOptionArray(false),
        ));
        
        $this->addColumn('generated_at', array(
            'header'  => $hlp->__('Last Generated At'),
            'index'   => 'generated_at',
            'type'    => 'datetime',
            'getter'  => 'getGeneratedAt',
            'default' => '',
            'gmtoffset' => true,

        ));
        
        $this->addColumn('delivery_type', array(
            'header'  => $hlp->__('Delivery Type'),
            'index'   => 'delivery_type',
            'type'    => 'options',
            'width'   => '50px',
            'options' => $hlp->getDeliveryTypes(),
        ));
        
        $this->addColumn('delivery_at', array(
            'header'  => $hlp->__('Last Delivery At'),
            'index'   => 'delivery_at',
            'type'    => 'datetime',
            'default' => '',
            'getter'  => 'getDeliveryAt',
            'gmtoffset' => true,
        ));
        
        $this->addColumn('status', array(
            'header'    => $hlp->__('Status'),
            'index'     => 'status',
            'type'      => 'options',
            'width'     => '90px',
            'options'   => array(
                Amasty_Feed_Model_Profile::STATE_READY    => $hlp->__('Ready'),
                Amasty_Feed_Model_Profile::STATE_PROGRESS => $hlp->__('In progress'),
                Amasty_Feed_Model_Profile::STATE_WAITING  => $hlp->__('Waiting'),
                Amasty_Feed_Model_Profile::STATE_ERROR    => $hlp->__('Error'),
             ),
        ));
        
        $baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
        $this->addColumn('filename', array(
            'header'    => $hlp->__('File'),
            'index'     => 'filename',
            'renderer'  => 'amfeed/adminhtml_profile_grid_renderer_file',
        ));
        
        $this->addColumn('action', array(
            'header'    => Mage::helper('catalog')->__('Action'), //its correct
            'width'     => '100px',
            'type'      => 'action',
            'actions'   => array(
                array(
                    'caption' => $hlp->__('Generate'),
                    'url'     => array('base' => '*/*/generate'),
                    'field'   => 'feed_id'
               ),
            ),
            'filter'    => false,
            'sortable'  => false,
            'index'     => 'feed_id',
            'is_system' => true,
            'renderer'  => 'amfeed/adminhtml_profile_grid_renderer_generate',
        ));    
    
        return parent::_prepareColumns();
    }
    
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
      
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('feed_id');
        $this->getMassactionBlock()->setFormFieldName('profiles');
        
        $this->getMassactionBlock()->addItem('duplicate', array(
             'label'    => Mage::helper('amfeed')->__('Duplicate'),
             'url'      => $this->getUrl('*/*/massDuplicate')
        ));

        $this->getMassactionBlock()->addItem('generate', array(
             'label'    => Mage::helper('amfeed')->__('Generate'),
             'url'      => $this->getUrl('*/*/massGenerate')
        ));

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('amfeed')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('amfeed')->__('Are you sure?')
        ));
        
        return $this; 
    }
    
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setExportVisibility('true');
        $this->setChild('import_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('amfeed')->__('Add by Template'),
                    'onclick'   => 'amFeedImportObject.load($(\'am_templates_select\').value);',
                    'class'     => 'task'
                ))
        );

        return $this;
    }
    
    
    public function getTemplatesSelectHtml()
    {
        $collection = Mage::getModel('amfeed/template')->getCollection();
        $options = array('<option></option>');
        foreach ($collection as $template){
            $options[] = strtr('<option value=":val">:title</option>', array(
               ':val' => $template->getFeedId(),
               ':title' => $template->getTitle()
            ));
            
        }
        return '<select id="am_templates_select">' . implode('', $options) . '</select>&nbsp;&nbsp;';
    }
    
    public function getImportButtonHtml()
    {
        return $this->getChildHtml('import_button').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    }
       
    public function getMainButtonsHtml()
    {
        $html = parent::getMainButtonsHtml();
        $html = $this->getTemplatesSelectHtml() . $this->getImportButtonHtml() . $html;
        return $html;
    }

}