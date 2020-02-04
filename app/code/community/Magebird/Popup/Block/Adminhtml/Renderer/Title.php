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
class Magebird_Popup_Block_Adminhtml_Renderer_Title extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
  public function render(Varien_Object $row){    
    return $row['title']."<br>".$row['description']; 
  }  
}