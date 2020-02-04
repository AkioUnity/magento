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
class Magebird_Popup_Block_Adminhtml_Renderer_Avgtime extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
  public function render(Varien_Object $row){
    $value =  $row->getData($this->getColumn()->getIndex());
    $row = $row->getData();
    if($row['background_color']=="3" || $row['background_color']=="4"){
      return "<span class='popupTooltip' title='".Mage::helper('magebird_popup')->__('Not available for popups with Background Overlay set to None.')."'>?</span>";
    }        
    if($row['views']==0){
      $seconds = '0 s';
    }else{
      $seconds = round(($row['total_time']/1000/$row['views']),1).' s';
    }       
    return $seconds;
  }  
}