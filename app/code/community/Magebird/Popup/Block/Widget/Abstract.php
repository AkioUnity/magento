<?php
class Magebird_Popup_Block_Widget_Abstract
    extends Mage_Core_Block_Template
    implements Mage_Widget_Block_Interface
{
    /**
     * A model to serialize attributes
     * @var Varien_Object
     */
    protected $_serializer = null;

    /**
     * Initialization
     */
    protected function _construct()
    {
        $this->_serializer = new Varien_Object();
        parent::_construct();
    }

    /**
     * Produce links list rendered as html
     *
     * @return string
     */
    protected function _toHtml()
    {
        return parent::_toHtml();
    }  
    
    public function brightness($colourstr, $steps)
    {
      $colourstr = str_replace('#','',$colourstr);
      $rhex = substr($colourstr,0,2);
      $ghex = substr($colourstr,2,2);
      $bhex = substr($colourstr,4,2);
    
      $r = hexdec($rhex);
      $g = hexdec($ghex);
      $b = hexdec($bhex);
    
      $r = max(0,min(255,$r + $steps));
      $g = max(0,min(255,$g + $steps));  
      $b = max(0,min(255,$b + $steps));
    
      $decheyr = dechex($r);     
        
      if(strlen($decheyr)==1) $decheyr = "0".$decheyr;
    
      $decheyg = dechex($g);
      if(strlen($decheyg)==1) $decheyg = "0".$decheyg;
    
      $decheyb = dechex($b); 
      if(strlen($decheyb)==1) $decheyb = "0".$decheyb;
          
      return '#'.$decheyr.$decheyg.$decheyb;

    }     
    
    public function getButtonTextColor(){
      $buttonTextColor = $this->getData('button_text_color');
      if(!$buttonTextColor) $buttonTextColor = $this->getData('buttontext_color');
      if(!$buttonTextColor) $buttonTextColor = "#FFFFFF";
      if(strpos($buttonTextColor,'#') === false) $buttonTextColor = "#".$buttonTextColor;   
      return $buttonTextColor;  
    } 
    
    public function getButtonColor(){
      $buttonColor = $this->getData('button_color');
      if(!$buttonColor) $buttonColor = '#d83c3c';
      if(strpos($buttonColor,'#') === false) $buttonColor = "#".$buttonColor;     
      return $buttonColor;
    }
    
    public function getDelay(){
      $delay = 0;
      if($this->getData('on_success')==2){
        $delay = $this->getData('close_delay'); 
      }elseif($this->getData('on_success')==3){
        $delay = $this->getData('redirect_delay');
      }  
      return $delay;  
    }    
     

} 