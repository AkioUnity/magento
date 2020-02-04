<?php
class Magebird_Popup_Block_Timer
    extends Magebird_Popup_Block_Widget_Abstract
{
    
    public function getCookieInheritId(){
        $_popup     = Mage::getModel('magebird_popup/popup')->load($this->getPopupInheritId());        
        //$widgetData = Mage::helper('magebird_popup')->getWidgetData($_popup->getPopupContent(),$this->getRequest()->getParam('widgetId'));
        return $_popup->getCookieId();    
    }
    
    public function getTimerType(){
      if($this->getPopupInheritId()){                
          $widgetData = $this->getWidgetData($this->getPopupInheritId());
          if(isset($widgetData['to_date'])){          
            return 'static';
          }else{
            return 'dynamic';
          }              
      }else{
          if($this->getToDate()){
            return 'static';
          }else{
            return 'dynamic';
          }
      }    
    
    }
    public function getWidgetData($popupId){
      $_popup     = Mage::getModel('magebird_popup/popup')->load($popupId);
      $content = $_popup->getPopupContent();
      $widget = explode('widget_id="',$content);
      $widget = explode('"',$widget[1]);
      $widgetId = $widget[0];                 
      $widgetData = Mage::helper('magebird_popup')->getWidgetData($content,$widgetId);          
      return $widgetData;    
    }
    
    public function getTimer(){
      if($this->getPopupInheritId()){                
          $widgetData = $this->getWidgetData($this->getPopupInheritId());          
          if(isset($widgetData['to_date'])){          
            $popupTimer = strtotime($widgetData['to_date']);
          }else{
            $popupTimer = $widgetData['minutes']*60;
          }          
      }else{
        if($this->getToDate()){
          //$popupTimer = (strtotime($this->getToDate())-Mage::getModel('core/date')->timestamp(time()));
          $popupTimer = strtotime($this->getToDate());
        }else{
          $popupTimer = $this->getData('minutes')*60;
        }        
      }
      //var_dump($popupTimer); exit;
      return $popupTimer;  
    }  
    
    public function getFontSize(){
      return $this->getTimerSize();
    }   
    
    public function getLabelFontSize(){
      $fontSize = intval($this->getTimerSize()/2);
      if($fontSize<10) $fontSize=10;
      return $fontSize;      
    }        


} 