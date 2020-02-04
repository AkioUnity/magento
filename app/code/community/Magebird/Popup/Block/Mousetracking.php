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
class Magebird_Popup_Block_Mousetracking extends Mage_Core_Block_Template
{
    /**
     * Set template
     */
    public function __construct()
    {
        parent::__construct();
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
    
    protected function getTrackingData() {
      $mousetrackingpopup = Mage::getModel('magebird_popup/mousetrackingpopup')->load($this->getId());
      
      $mousetracking = Mage::getModel('magebird_popup/mousetracking')->load($mousetrackingpopup->getData('mousetracking_id'));

      $popup = Mage::getModel('magebird_popup/popup')->load($mousetrackingpopup->getData('popup_id'));
      
      return array("mousetrackingpopup"=>$mousetrackingpopup,"mousetracking"=>$mousetracking,"popup"=>$popup);
     
    }    
  
    public function getWindow(){ 
      $mousetrackingpopup = Mage::getModel('magebird_popup/mousetrackingpopup')->load($this->getId());      
      $mousetracking = Mage::getModel('magebird_popup/mousetracking')->load($mousetrackingpopup->getData('mousetracking_id'));
      return array('width'=>$mousetracking->getData('window_width'),'height'=>$mousetracking->getData('window_height')); 
    } 
            
    public function getId(){ 
      return $this->getRequest()->getParam('id'); 
    }   

    public function getPrefixedCss($css,$prefix){
        $parts = explode('}', $css); 
        foreach ($parts as &$part) {                    
            $checkPart = trim($part);
            if (empty($checkPart)) {
                continue;
            }    
            
            $prefix2 = substr(str_shuffle("dpqzsjhiunbhfcjseepudpn"), 0, 6);        
            $partDetails = explode('{',$part);
            if(substr_count($part,"{")==2){
              $mediaQuery = $partDetails[0]."{";
              $partDetails[0] = $partDetails[1];
              $mediaQueryStarted = true;
            }
            //old version had class .dialog, new has .mbdialog           
            $subParts = explode(',', $partDetails[0]);
            foreach ($subParts as &$subPart) {    
                if(trim($subPart)=="@font-face"                                               
                  || strpos($subPart,".dialog ")!==false 
                  || strpos($subPart," .dialog")!==false
                  || strpos($subPart,".dialog#")!==false
                  || strpos($subPart,".dialog.")!==false
                  || strpos($subPart,"dialogBg")!==false
                  || (strpos($subPart,".dialog")!==false && strlen($subPart)==7)) continue;
                                      
                if(strpos($subPart,$prefix)!==false){
                  $subPart = trim($subPart);                                
                }elseif(strpos($subPart,".mbdialog")!==false){
                  $subPart = str_replace(".mbdialog", $prefix, $subPart);
                }else{
                  $subPart = $prefix . ' ' . trim($subPart);
                } 
            }       
        
            if(substr_count($part,"{")==2){
              $part = $mediaQuery."\n".implode(', ', $subParts)."{".$partDetails[2];
            }elseif(empty($part[0]) && $mediaQueryStarted){
              $mediaQueryStarted = false;
              $part = implode(', ', $subParts)."{".$partDetails[2]."}\n"; //finish media query
            }else{
              $part = implode(', ', $subParts)."{".$partDetails[1];
            }  
        }
                        
        $prefixedCss = implode("}\n", $parts);
        
        return $prefixedCss;   
    }     
}