<?php
/**
 * Magebird.com
 *
 * @category   Magebird
 * @package    Magebird_Popup
 * @copyright  Copyright (c) 2018 Magebird (http://www.Magebird.com)
 * @license    http://www.magebird.com/licence
 * Any form of ditribution, sell, transfer forbidden see licence above 
 */
class Magebird_Popup_Block_Adminhtml_Popup_Mousetracking extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();    
      $this->setDefaultSort('mousetracking_id');  
      $this->setDefaultLimit('60'); 
      $this->setDefaultDir('DESC');      
      $this->setSaveParametersInSession(false);
  } 
  
  protected function _prepareLayout() {        
      if(Mage::getStoreConfig('magebird_popup/statistics/mousetracking')){
        $this->getMessagesBlock()->addNotice(Mage::helper('magebird_popup')->__("Mousetracking is in experiment state and it will provide just basic functionality. For advance mousetracking we recommend you to use one of professional mousetracking tools."));
      }else{
        $this->getMessagesBlock()->addNotice(Mage::helper('magebird_popup')->__("Mousetracking is currently not enabled. Go to System->MAGEBIRD EXTENSIONS->Popup->Statistics settings and set 'Track user mouse movements?' to 'Yes'."));
      }      
              
      return parent::_prepareLayout();
  }         
    
  protected function _prepareCollection()
  {
      $collection = Mage::getModel('magebird_popup/mousetrackingpopup')->getCollection();
      $collection->getSelect()->join(Mage::getConfig()->getTablePrefix().'magebird_mousetracking', 
        'main_table.mousetracking_id ='.Mage::getConfig()->getTablePrefix().'magebird_mousetracking.mousetracking_id',
        array('date_created','user_ip','device')
      );
      $collection->addFieldToFilter('popup_id',$this->getRequest()->getParam('id'));  
      $this->setCollection($collection); 
      return parent::_prepareCollection();
  }      
  
  protected function _prepareColumns()
  {
    
      $this->addColumn('mousetracking_id', array(
          'header'    => Mage::helper('magebird_popup')->__('id'),
          'align'     =>'left',
          'width'     =>'40px',
          'index'     => 'mousetracking_id',
      ));  
      
	    $this->addColumn('date_created', array(
          'header'    => Mage::helper('magebird_popup')->__('Date'),
          'type'      => 'datetime',
          'align'     =>'left',
          'index'     => 'date_created',
      ));  
      
	    $this->addColumn('behaviour', array(
          'header'    => Mage::helper('magebird_popup')->__('Behaviour'),
          'align'     =>'left',
          'index'     => 'behaviour',
          'width'     =>'250px',
          'type'      => 'options',
          'options' => $this->_getAttributeOptions('behaviour'),
      ));       
      
      $this->addColumn('duration', array(
          'header'    => Mage::helper('magebird_popup')->__('Duration'),
          'align'     =>'left',
          'index'     => 'duration',
          'sortable'  => false,
          'filter'    => false,
          'renderer'  => 'Magebird_Popup_Block_Adminhtml_Renderer_Duration',
      ));        
      
	    $this->addColumn('user_ip', array(
          'header'    => Mage::helper('magebird_popup')->__('Country'),
          'align'     =>'left',
          'index'     => 'user_ip',
          'sortable'  => false,
          'filter'    => false,
          'renderer'  => 'Magebird_Popup_Block_Adminhtml_Renderer_Country',
      ));   
      
	    $this->addColumn('device', array(
          'header'    => Mage::helper('magebird_popup')->__('Device'),
          'align'     =>'left',
          'index'     => 'device',
          'renderer'  => 'Magebird_Popup_Block_Adminhtml_Renderer_Device',
      ));           
      
      $this->addColumn('action',
       array(
          'header'    =>  Mage::helper('magebird_popup')->__('Play'),
          'type'      => 'text',
          'filter'    => false,
          'sortable'  => false,
          'is_system' => true,
          'renderer'  => 'Magebird_Popup_Block_Adminhtml_Renderer_Mouselink',
      )  
      );      
                        
    
		
      return parent::_prepareColumns();
  } 
  
  public function getRowUrl($row)
  {
      $websites = Mage::app()->getWebsites(false);
      $websiteId = reset($websites)->getDefaultStore()->getId();
      return Mage::app()->getStore($websiteId)->getUrl('popup/mousetracking/index',array('id'=>$row->getId()));
  }
  
  protected function _getAttributeOptions($attribute_code)
  {
      $options[1] = Mage::helper('magebird_popup')->__('Goal completed');
      $options[2] = Mage::helper('magebird_popup')->__('Popup closed without action');
      $options[3] = Mage::helper('magebird_popup')->__('Window refreshed or closed');
      $options[4] = Mage::helper('magebird_popup')->__('Clicked inside popup');
      return $options;
  }  
      
}