<?php
class Magebird_Popup_Block_Buttons
    extends Magebird_Popup_Block_Widget_Abstract
{     
    
    public function getColor(){
      $color = $this->getData('button_color');
      if(strpos($color,'#') === false) $color = "#".$color;
      return $color;
    } 
    
    public function buttonSize(){
      $size = $this->getData('button_size');
      if($size=="big" || $size=="normall") $size=5;
      if($size=="small" || $size=="middle") $size=3;
      if($size=="tiny") $size=2;
      if(!$size) $size = 4;
      return $size;
    }         

    public function getJsHtml(){
      $html = "<script type=\"text/javascript\">"; 
      $html.= "popupButton['".$this->getWidgetId()."'] = {};\n";
      $html.= "popupButton['".$this->getWidgetId()."'].successMsg = decodeURIComponent(('".urlencode(Mage::helper('cms')->getBlockTemplateProcessor()->filter(urldecode($this->getData('success_msg'))))."'+'').replace(/\+/g, '%20'))\n;";   
      $html.= "popupButton['".$this->getWidgetId()."'].successAction = '".$this->getData('on_click')."'\n";
      $html.= "popupButton['".$this->getWidgetId()."'].couponType = '".$this->getData('coupon_type')."'\n";
      $html.= "</script>\n"; 
      return $html; 
    }

} 