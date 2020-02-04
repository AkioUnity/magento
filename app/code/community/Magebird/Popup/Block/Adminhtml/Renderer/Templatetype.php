<?php
class Magebird_Popup_Block_Adminhtml_Renderer_Templatetype extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
  public function render(Varien_Object $row) {  
    if($row->getTemplateType()==1){
      return 'Free (included in package)';
    }
    return 'Premium';   
  }
}