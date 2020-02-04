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
class Magebird_Popup_Block_Adminhtml_Renderer_Sales extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
  public function render(Varien_Object $row){    
    $value =  $row->getData($this->getColumn()->getIndex());
    $row = $row->getData();
    $notAvailable = false;
    if($row['show_when']!="1" && ($row['background_color']=="3" || $row['background_color']=="4")){
      $notAvailable = "<span class='popupTooltip' title='".Mage::helper('magebird_popup')->__('Not available for popups with Background Overlay set to None and Show when other than "After page loads".')."'>?</span>";
    }     
        
    if($row['popupSalesCount']>0 && $row['views']>0){
      $conversionPopup = round($row['popupSalesCount']/$row['popupVisitors']*100,2)."%";
    }else{
      $conversionPopup = "/";
    }
  
    if($row['popupSalesCount']>0 && $row['popupCarts']>0){      
      $abondedPopup = round(($row['popupCarts']-$row['popupSalesCount'])/$row['popupCarts']*100,2)."%";
    }else{
      $abondedPopup = "/";
    }   
       
    $sales = $row['popupRevenue'] ? $row['currency'].$row['popupRevenue'] : "/";   
      
    $html = "<span style='font-size:11px;'>";      
    $html .= "<span style='min-width:71px;display:inline-block;'>Cpn Sales:</span> ".$sales."<br>";
    $html .= "<span style='min-width:71px;display:inline-block;'>Cpn Orders:</span> ".$row['couponSalesCount']."<br>";    
    if($notAvailable){
      $html .= "<span style='min-width:71px;display:inline-block;'>Conversion:</span> $notAvailable<br>";
      $html .= "<span style='min-width:71px;display:inline-block;'>Abonded cart:</span> $notAvailable<br>";    
    }else{
      $html .= "<span style='min-width:71px;display:inline-block;'>Conversion:</span> $conversionPopup<br>";
      $html .= "<span style='min-width:71px;display:inline-block;'>Abonded cart:</span> $abondedPopup<br>";
    }
    $html .= "</span>";
    return $html;
  }  
}