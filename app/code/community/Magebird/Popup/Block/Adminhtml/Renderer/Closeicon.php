<?php
class Magebird_Popup_Block_Adminhtml_Renderer_Closeicon extends Varien_Data_Form_Element_Abstract {
    protected $_element;

    public function getElementHtml()
    {   
        $action = Mage::app()->getRequest()->getActionName();
        if($action=="copy"){
          $id = Mage::app()->getRequest()->getParam('copyid');
          $closeStyle = Mage::getModel('magebird_popup/template')->load($id)->getData('close_style');        
        }elseif($action=="duplicate"){
          $popupId = Mage::app()->getRequest()->getParam('copyid');
          $closeStyle = Mage::getModel('magebird_popup/popup')->load($popupId)->getData('close_style');        
        }else{
          $popupId = Mage::app()->getRequest()->getParam('id');
          $closeStyle = Mage::getModel('magebird_popup/popup')->load($popupId)->getData('close_style');        
        }
  
        $folder = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN)."/frontend/base/default/images/magebird_popup/";        
        $html = "<div class='closeIcons'>
                 <input name='close_style' type='radio' value='5' /><span style='margin-right:10px;'>Don't show close icon</span>
                 <input name='close_style' type='radio' value='2' /><img src='".$folder."close_big_preview.png' />
                 <input name='close_style' type='radio' value='3' /><img src='".$folder."close_simple_dark_preview.png' />
                 <input name='close_style' type='radio' value='4' /><img src='".$folder."close_simple_white_preview.png' />
                 <input name='close_style' type='radio' value='8' /><img src='".$folder."close_big_x.png' /><br>
                 <input name='close_style' type='radio' value='6' /><img src='".$folder."close_big_x_d.png' />
                 <input name='close_style' type='radio' value='9' /><img src='".$folder."close_big_x_bold.png' />
                 <input name='close_style' type='radio' value='10' /><img src='".$folder."close_big_x_bold_d.png' />
                 <input name='close_style' type='radio' value='11' /><img src='".$folder."white_circle.png' />
                 <input name='close_style' type='radio' value='1' /><img src='".$folder."close_dark.png' />
                 <input name='close_style' type='radio' value='7' /><img src='".$folder."close_transparent.png' />
                 </div>
                  ";
        if($closeStyle){
          $html = str_replace("value='$closeStyle'", "value='$closeStyle' checked='checked'", $html);
        }        
        return $html;
    }
}