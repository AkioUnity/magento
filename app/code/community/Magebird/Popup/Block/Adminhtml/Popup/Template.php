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
class Magebird_Popup_Block_Adminhtml_Popup_Template extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();      
      $this->setDefaultLimit('100');      
      $this->setSaveParametersInSession(false);
  }
  
  protected function _prepareLayout() {       
      $templateProcessor = $this->parseAllTemplates();
      if(isset($templateProcessor['errorMsg']) && $templateProcessor['errorMsg']){
        $this->getMessagesBlock()->addError($templateProcessor['errorMsg']);
      }       
              
      return parent::_prepareLayout();
  }   
    
  protected function _prepareCollection()
  {
      $collection = Mage::getModel('magebird_popup/template')->getCollection();
      $collection->setOrder('template_id');
      $collection->getSelect()->order('position DESC');
      $collection->getSelect()->order('template_id ASC');      
      //$collection->setDefaultDir('ASC');             
      $this->setCollection($collection);      
      return parent::_prepareCollection();
  }
  
  protected function _prepareColumns()
  {    
      
      $this->addColumn('template', array(
          'header'    => Mage::helper('magebird_popup')->__('Template preview image'),
          'align'     =>'left',
          'width'     => '420px',
          'index'     => 'comment_id',
          'filter'    => false,
          'sortable'  => false,          
          'renderer'  => 'Magebird_Popup_Block_Adminhtml_Renderer_Image',
      ));                        	                  

	    $this->addColumn('title', array(
          'header'    => Mage::helper('magebird_popup')->__('Title'),
          'align'     =>'left',
          'index'     => 'title',
          'renderer'  => 'Magebird_Popup_Block_Adminhtml_Renderer_Title',
      ));  
      
      $this->addColumn('action',
       array(
          'header'    =>  Mage::helper('magebird_popup')->__('Action'),
          'width'     => '200px',
          'type'      => 'text',
          'filter'    => false,
          'sortable'  => false,
          'is_system' => true,
          'renderer'  => 'Magebird_Popup_Block_Adminhtml_Renderer_Templateaction',
      ));      
                        
    
		
      return parent::_prepareColumns();
  } 
  
  public function getRowUrl($row)
  {                       
      return $this->getUrl('*/*/copy', array('copyid' => $row->getId()));           
  }
  
  public function parseAllTemplates(){
      //we use template collection because magebird_popup_content model doesn't exists
      $collection = Mage::getModel('magebird_popup/template')->getCollection();
      $table = Mage::getSingleton('core/resource')->getTableName('magebird_popup_content');
      $collection->getSelect()->join(
                      array('content' => $table),
                      'main_table.template_id = content.popup_id AND content.is_template=1',
                      array()
              )->group('main_table.template_id');
      
      if(count($collection)>0) return; //already parsed
      Mage::getModel('magebird_popup/popup')->parsePopupTemplateContent();
  }
      
}