<?php
class Magebird_Popup_Block_Adminhtml_Renderer_Mouselink extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
  public function render(Varien_Object $row) {  
    $url =$this->getUrl('*/magebird_mousetracking', array('id'=>$row->getId()));            
    return sprintf("<style>.popupAction:hover{color:black;}</style><a class='popupAction' target='_blank' href='%s'>%s</a>", 
                    $url, Mage::helper('magebird_popup')->__('Play'));
  }
}