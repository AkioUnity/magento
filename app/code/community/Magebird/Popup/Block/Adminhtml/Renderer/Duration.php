<?php
class Magebird_Popup_Block_Adminhtml_Renderer_Duration extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
  public function render(Varien_Object $row) {   
    return gmdate("H:i:s", $row->getTotalMs()/1000);                   
  }
}