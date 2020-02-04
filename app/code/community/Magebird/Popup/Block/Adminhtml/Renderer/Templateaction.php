<?php
class Magebird_Popup_Block_Adminhtml_Renderer_Templateaction extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
  public function render(Varien_Object $row) {  
    $websites = Mage::app()->getWebsites(false);   
    $websiteId = 1; 
    foreach($websites as $website){             
      $defaultStore = $website->getDefaultStore(); 
      if(!$defaultStore) continue;
      $websiteId = $defaultStore->getId();
      if(Mage::app()->getStore($websiteId)->getData('is_active')!=0) break;   
    }     
    $url1 = Mage::app()->getStore($websiteId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK)."magebird_popup/index/template/templateId/".$row->getId();
    $url2=$this->getUrl('*/*/copy', array('copyid'=>$row->getId(), 'storeId' => $row->getStoreId()));
    return sprintf("<a class='popupAction' target='_blank' href='%s'>%s</a> <a class='popupAction' href='%s'>%s</a>", 
                    $url1, Mage::helper('magebird_popup')->__('Preview'),
                    $url2, Mage::helper('magebird_popup')->__('Copy & Edit'));  
  }
}