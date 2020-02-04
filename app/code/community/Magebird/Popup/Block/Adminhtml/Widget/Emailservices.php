<?php 
class Magebird_Popup_Block_Adminhtml_Widget_Emailservices extends Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element
{
    public function render(Varien_Data_Form_Element_Abstract $element){   
      $hideServices = '';
      $services = array();
      if(!Mage::getStoreConfig('magebird_popup/services/enableactivecampaign')){
        $hideServices .= "jQuery(\"#widget_options input[name='parameters\[ac_list_id\]']\").parent().parent().hide();";
        $services[] = "<strong>ActiveCampaign</strong>";  
      }
      if(!Mage::getStoreConfig('magebird_popup/services/enablecampaignmonitor')){
        $hideServices .= "jQuery(\"#widget_options input[name='parameters\[cm_list_id\]']\").parent().parent().hide();";
        $services[] = "<strong>Campaignmonitor</strong>";  
      }
      if(!Mage::getStoreConfig('magebird_popup/services/enablegetresponse')){
        $hideServices .= "jQuery(\"#widget_options input[name='parameters\[gr_campaign_token\]']\").parent().parent().hide();";
        $services[] = "<strong>GetResponse</strong>";  
      }
      if(!Mage::getStoreConfig('magebird_popup/services/enablesendy')){
        $hideServices .= "jQuery(\"#widget_options input[name='parameters\[sendy_list_id\]']\").parent().parent().hide();";
        $services[] = "<strong>Sendy</strong>";  
      }
      if(!Mage::getStoreConfig('magebird_popup/services/enable_phplist')){
        $hideServices .= "jQuery(\"#widget_options input[name='parameters\[phplist_list_id\]']\").parent().parent().hide();";
        $services[] = "<strong>phpList</strong>";  
      } 
      if(!Mage::getStoreConfig('magebird_popup/services/enable_klaviyo')){
        $hideServices .= "jQuery(\"#widget_options input[name='parameters\[klaviyo_list_id\]']\").parent().parent().hide();";
        $services[] = "<strong>Klaviyo</strong>";  
      }   
      if(!Mage::getStoreConfig('magebird_popup/services/enable_mailjet')){
        $hideServices .= "jQuery(\"#widget_options input[name='parameters\[mailjet_list_id\]']\").parent().parent().hide();";
        $services[] = "<strong>Mailjet</strong>";  
      }    
      if(!Mage::getStoreConfig('magebird_popup/services/enable_emma')){
        $hideServices .= "jQuery(\"#widget_options input[name='parameters\[emma_group_ids\]']\").parent().parent().hide();";
        $services[] = "<strong>Emma</strong>";  
      }   
      if(!Mage::getStoreConfig('magebird_popup/services/enable_iconneqt')){
        $hideServices .= "jQuery(\"#widget_options input[name='parameters\[iconneqt_list_id\]']\").parent().parent().hide();";
        $services[] = "<strong>iConneqt</strong>";  
      }           
      if(!Mage::getStoreConfig('magebird_popup/services/enable_nuevomailer')){
        $hideServices .= "jQuery(\"#widget_options input[name='parameters\[nuevomailer_list_ids\]']\").parent().parent().hide();";
        $hideServices .= "jQuery(\"#widget_options input[name='parameters\[nuevomailer_newsletter\]']\").parent().parent().hide();";
        $services[] = "<strong>Nuevomailer</strong>";  
      }                   
      
      if(!Mage::getStoreConfig('magebird_popup/services/enable_dotmailer')){
        $hideServices .= "jQuery(\"#widget_options input[name='parameters\[dotmailer_list_id\]']\").parent().parent().hide();";
        $services[] = "<strong>Dotmailer</strong>";
      }   
      if(!Mage::getStoreConfig('magebird_popup/services/enable_cc')){
        $hideServices .= "jQuery(\"#widget_options input[name='parameters\[cc_list_id\]']\").parent().parent().hide();";
        $services[] = "<strong>Constant Contact</strong>";
      }         
      
      if(!Mage::getStoreConfig('magebird_popup/services/enable_mailerlite')){
        $hideServices .= "jQuery(\"#widget_options input[name='parameters\[mailerlite_list_id\]']\").parent().parent().hide();";
        $services[] = "<strong>MailerLite</strong>";
      }   
      
      if(!Mage::getStoreConfig('magebird_popup/services/enable_aweber')){
        $hideServices .= "jQuery(\"#widget_options input[name='parameters\[aweber_list_id\]']\").parent().parent().hide();";
        $services[] = "<strong>AWeber</strong>";
      }                   
                
      $msg = '';      
      if($services){
        $msg = Mage::helper('magebird_popup')->__("To insert also ".implode($services,", ")." list id, enable it first inside System->Configuration->MAGEBIRD EXTENSIONS->Popup->Email services. After you enabled it new options will show up here.");
        $msg = "<p style=\"padding-left:5px;margin-top:15px;margin-bottom:30px;\">Other e-mail services:<br>$msg</p>";
      }          
              
      //$hideServices = "jQuery(\"#widget_options input[name='parameters\[sendy_list_id\]']\").parent().parent().hide();";
      $html =  "<script>jQuery('#widget_options .hor-scroll').append('$msg');$hideServices</script>";         
                
      return $html;
    }     
}