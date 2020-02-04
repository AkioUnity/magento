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
class Magebird_Popup_Block_Adminhtml_Renderer_Percent extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
  public function render(Varien_Object $row){    
    $value =  $row->getData($this->getColumn()->getIndex());
    $row = $row->getData();
    $index = $this->getColumn()->getIndex();
    if(($index == "window_closed" || $index == "page_reloaded")  && ($row['background_color']=="3" || $row['background_color']=="4")){
      return "<span class='popupTooltip' title='".Mage::helper('magebird_popup')->__('Not available for popups with Background Overlay set to None.')."'>?</span>";
    }elseif(($index == "popup_closed" || $index == "click_inside") && ($row['background_color']=="3" || $row['background_color']=="4")){
      return "<span style='min-width:20px; display:inline-block;'>".$row[$this->getColumn()->getIndex()]."</span>";
    }    
    if($row['views']==0){
      $percent = '0 %';
    }else{
      $percent = round(($row[$this->getColumn()->getIndex()]/$row['views']*100),1).'%';
    }     
    return "<span style='min-width:20px; display:inline-block;'>".$row[$this->getColumn()->getIndex()]."</span> (".$percent.")";
  }  
}