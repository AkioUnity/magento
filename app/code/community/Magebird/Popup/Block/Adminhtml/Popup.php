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
class Magebird_Popup_Block_Adminhtml_Popup extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {    

    $this->_controller = 'adminhtml_popup';
    $this->_blockGroup = 'magebird_popup';
    $this->_headerText = Mage::helper('magebird_popup')->__('Manage Popup Items');

    
    $this->_addButton('browsetemplate', array(
        'label'     => Mage::helper('adminhtml')->__('Create popup using predefined templates'),
        'onclick'   => "setLocation('".$this->getUrl('*/*/template')."')"
    ), -100);   
        
    $this->_addButtonLabel = Mage::helper('magebird_popup')->__('Create Popup from scratch');
    
    
    
    $this->_addButton('clearcache', array(
        'label'     => Mage::helper('adminhtml')->__('Clear popup cache'),
        'onclick'   => "setLocation('".$this->getUrl('*/*/clearcache')."')",
        'class'     => "clearcache",
        'title'     => "Popup templates are cached for better performance. When you save popup, it will automatically update the cache. Anyway only if you modify any popup related file (e.g. magebird_popup.csv), you need to clear popup cache."
    ), -100);     
    
    parent::__construct();
  }    
 
}