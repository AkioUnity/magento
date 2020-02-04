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
class Magebird_Popup_Block_Adminhtml_Popup_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
  
  protected function _prepareLayout() {   
      $configModel = Mage::getModel('core/config_data'); //use model to prevent cache
      $extensionKey = $configModel->load('magebird_popup/general/extension_key','path')->getData('value');      
      if(empty($extensionKey)){
        $configModel = Mage::getModel('core/config_data');
        $trialStart = $configModel->load('magebird_popup/general/trial_start','path')->getData('value');              
        if($trialStart>strtotime('-7 days')){
          $days = ceil((($trialStart+60*60*24*7)-time())/60/60/24);
          $this->getMessagesBlock()->addError(Mage::helper('magebird_popup')->__("You "."are "."curr"."ently using "."fr"."ee tr"."ial mode which will ex"."pire in %s days. If you purcha"."sed the exte"."nsion go to Sys"."tem->Config"."uration->MAGE"."BIRD EXTENS"."IONS->Popup to acti"."vate your licence (if you get 40"."4 error, logo"."ut from admin and login again). After the tri"."al per"."iod is over your popups won't be displayed any"."more until you submit your licence.",$days));                   
        }else{
          $this->getMessagesBlock()->addError(Mage::helper('magebird_popup')->__("You haven't subm"."ited your exten"."sion licence yet. Your popups won't be displ"."ayed any"."more. Go to Sys"."tem->Configu"."ration->MAGE"."BIRD EXTENS"."IONS->Popup to acti"."vate your lic"."ence."));                   
        }
      } 
      return parent::_prepareLayout();
  }
  
  
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form(array(
                                      'id' => 'edit_form',
                                      'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                                      'method' => 'post',
        							  'enctype' => 'multipart/form-data'
                                   )
      );

      $form->setUseContainer(true);
      $this->setForm($form);
      return parent::_prepareForm();
  }
}