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
class Magebird_Popup_Block_Adminhtml_Template extends Mage_Adminhtml_Block_Template
{
    /**
     * Set template
     */
    public function __construct()
    {
        parent::__construct();
//        $this->setTemplate('popup/buttons.phtml');
    }

    /**
     * Prepare button and grid
     *
     * @return Mage_Adminhtml_Block_Catalog_Product
     */
    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    
    protected function brightness($colourstr, $steps) {
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
}