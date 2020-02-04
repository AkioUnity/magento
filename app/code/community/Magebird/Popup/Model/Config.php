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
class Magebird_Popup_Model_Config extends Mage_Core_Model_Config_Data
{
    public function save()
    {        
        $extensionKey = Mage::getStoreConfig($this->getPath());
        $submitedVal = $this->getValue();
        if(!$submitedVal) return;
        $licenceName = $this->getData('field');
        $label = $this->getData('field_config')->label;
        if(!empty($submitedVal)){
          if(empty($extensionKey)){
            $resp=null;   
            $data=http_build_query(array("licence_name"=>"popup","extension"=>"popup","licence_key"=>$this->getValue(),"domain"=>$_SERVER['HTTP_HOST'],"affId"=>0));
            if(function_exists('curl_version')){
              $ch = @curl_init();  
              @curl_setopt($ch, CURLOPT_URL, "https://www.magebird.com/licence/check.php?".$data); 
              @curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
              @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
              $resp = @curl_exec($ch); 
              @curl_close($ch);               
            }     
            if($resp==null){
              $headers  = "Content-type: application/x-www-form-urlencoded\r\nContent-Length: ".strlen($data)."\r\n";
              $options = array("http" => array("method"=>"POST","header"=>$headers,"content"=>$data));
              $context = stream_context_create($options); 
              $resp=@file_get_contents("https://www.magebird.com/licence/check.php",false,$context,0,100);              
            }   
            
            if($resp==null){
              Mage::throwException(Mage::helper('magebird_popup')->__('Can not validate the licence key. Please <a href="http://www.magebird.com/contacts">contact us</a>.'));
            }elseif($resp!=1){
              Mage::throwException($resp);
            }else{
              Mage::getModel('core/config')->saveConfig('magebird_popup/general/extension_key', $this->getValue());
              if(Mage::getStoreConfig('magebird_popup/settings/requesttype')==3){
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('magebird_popup')->__("The licence key has been submited, but it seems that script magebirdpopup.php is not web accessible. Please read instructions <a href='%s' target='_blank'>here</a>.","https://www.magebird.com/magento-extensions/popup.html?tab=faq#requestType"));
              }
              //make as request was switched to prevent automatic switch request after licence has been submited
              //we don't want that because after licence is submited user may not see the message about depreciated request type
              Mage::getModel('core/config')->saveConfig('magebird_popup/settings/requestswitched', 1);                
            }
          }
        }             
        return parent::save();
    }            
}