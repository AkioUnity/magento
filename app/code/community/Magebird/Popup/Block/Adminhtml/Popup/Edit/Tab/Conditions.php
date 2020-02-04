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
class Magebird_Popup_Block_Adminhtml_Popup_Edit_Tab_Conditions extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        if(!Mage::registry('popup_data')->getData('to_hour')){
          Mage::registry('popup_data')->setData('to_hour','24');            
        }         
        
        $fieldset = $form->addFieldset('appearance_form', array('legend' => Mage::helper('magebird_popup')->__('Extra show conditions')));    		         

        $fieldset->addField('day', 'multiselect', array(
            'name' => 'day',
            'label' => Mage::helper('magebird_popup')->__('Day of the week'),
            'required' => false,
            'values' => array(1=>array('label'=>Mage::helper('magebird_popup')->__('Monday'),'value'=>1),
                              2=>array('label'=>Mage::helper('magebird_popup')->__('Tuesday'),'value'=>2),
                              3=>array('label'=>Mage::helper('magebird_popup')->__('Wednesday'),'value'=>3),
                              4=>array('label'=>Mage::helper('magebird_popup')->__('Thursday'),'value'=>4),
                              5=>array('label'=>Mage::helper('magebird_popup')->__('Friday'),'value'=>5),
                              6=>array('label'=>Mage::helper('magebird_popup')->__('Saturday'),'value'=>6),
                              7=>array('label'=>Mage::helper('magebird_popup')->__('Sunday'),'value'=>0),
                              ),
            'style' => 'height:150px',
        ));
        
        $hours = array();
        for ($n = 0; $n <= 24; $n++) {
          $hours[$n] = array('label'=>$n,'value'=>$n); 	 
        }
        $afterElementHtml = "<p class='nm'><small>".Mage::helper('magebird_popup')->__('Use if you want to display popup only in specific hours (e.g.: If you want to show popup between 1pm and 8pm, choose as From hour value 13 and 20 as To hour). Website timezone will be used.'). "</small></p>";
        $fieldset->addField('from_hour', 'select', array(
    		  'label'     => Mage::helper('magebird_popup')->__('From hour'),
    		  'name'      => 'from_hour',
          'values'    => $hours,          
          'after_element_html' => $afterElementHtml 
    		)); 
                
        $fieldset->addField('to_hour', 'select', array(
    		  'label'     => Mage::helper('magebird_popup')->__('To hour'),
    		  'name'      => 'to_hour',
          'values'    => array_reverse($hours),
          'default'   => 24 
    		));          
          
        $afterElementHtml = "<p class='nm'><small>".Mage::helper('magebird_popup')->__('You can show popup only on product pages if product attribute matches your value (e.g. if product price is higher than 100$, if color is green, ...). See instructions <a target="_blank" href="http://www.magebird.com/magento-extensions/popup.html?tab=faq#productAttributeCond">here</a>.')."</small></p>";
        $fieldset->addField('product_attribute', 'text', array(
    		  'label'     => Mage::helper('magebird_popup')->__('Product Attribute'),
    		  'name'      => 'product_attribute',
          'after_element_html' => $afterElementHtml 
    		)); 
        
        $afterElementHtml = "<p class='nm'><small>".Mage::helper('magebird_popup')->__('Use if you want to show popup only on product pages and if product belongs to selected categories. Write categories ids separated with comma (e.g.:1,12,31)')."</small></p>";
        $fieldset->addField('product_categories', 'text', array(
    		  'label'     => Mage::helper('magebird_popup')->__('Product Categories'),
    		  'name'      => 'product_categories',
          'after_element_html' => $afterElementHtml 
    		));         
      
        $afterElementHtml = "<p class='nm'><small>".Mage::helper('magebird_popup')->__('You can show popup only to visitors from specific countries. Use <a href="http://data.okfn.org/data/core/country-list" target="_blank">iso codes</a>. You can add more countries by separating codes with comma (e.g. US, DE, IT). This product includes GeoLite2 data created by MaxMind, available from <a target="_blank" href="http://www.maxmind.com">http://www.maxmind.com</a>. To download the latest IP database, please follow instructions from <a target="_blank" href="http://www.magebird.com/magento-extensions/popup.html?tab=faq#geoIP">our FAQ</a>.')."</small></p>";
        $fieldset->addField('country_ids', 'text', array(
    		  'label'     => Mage::helper('magebird_popup')->__('Country'),
    		  'name'      => 'country_ids',
          'after_element_html' => $afterElementHtml 
    		)); 
        
        $afterElementHtml = "<p class='nm'><small>".Mage::helper('magebird_popup')->__('You can exclude popup to visitors from specific countries. Use <a href="http://data.okfn.org/data/core/country-list" target="_blank">iso codes</a>. You can add more countries by separating codes with comma (e.g. US, DE, IT). This product includes GeoLite2 data created by MaxMind, available from <a target="_blank" href="http://www.maxmind.com">http://www.maxmind.com</a>. To download the latest IP database, please follow instructions from <a target="_blank" href="http://www.magebird.com/magento-extensions/popup.html?tab=faq#geoIP">our FAQ</a>.')."</small></p>";
        $fieldset->addField('not_country_ids', 'text', array(
    		  'label'     => Mage::helper('magebird_popup')->__('Exclude country'),
    		  'name'      => 'not_country_ids',
          'after_element_html' => $afterElementHtml 
    		));         
                
        $fieldset->addField('devices', 'select', array(
    		  'label'     => Mage::helper('magebird_popup')->__('Devices'),
    		  'name'      => 'devices',
    		  'values'    => array(
    			  array(
    				  'value'     => 1,
    				  'label'     => Mage::helper('magebird_popup')->__('All devices'),
    			  ),
    			  array(
    				  'value'     => 2,
    				  'label'     => Mage::helper('magebird_popup')->__('Desktop'),
    			  ),    
    			  array(
    				  'value'     => 3,
    				  'label'     => Mage::helper('magebird_popup')->__('Mobile'),
    			  ),
    			  array(
    				  'value'     => 4,
    				  'label'     => Mage::helper('magebird_popup')->__('Tablet'),
    			  ),
    			  array(
    				  'value'     => 5,
    				  'label'     => Mage::helper('magebird_popup')->__('Mobile & Tablet'),
    			  ),
    			  array(
    				  'value'     => 6,
    				  'label'     => Mage::helper('magebird_popup')->__('Desktop & Tablet'),
    			  ),
    			  array(
    				  'value'     => 7,
    				  'label'     => Mage::helper('magebird_popup')->__('Desktop & Mobile'),
    			  )                                                        
    		  )  
    		));  

        $fieldset->addField('cookies_enabled', 'select', array(
    		  'label'     => Mage::helper('magebird_popup')->__('Cookies enabled'),
    		  'name'      => 'cookies_enabled',
    		  'values'    => array(
    			  array(
    				  'value'     => 1,
    				  'label'     => Mage::helper('magebird_popup')->__('Show to All users'),
    			  ),
    			  array(
    				  'value'     => 2,
    				  'label'     => Mage::helper('magebird_popup')->__('Show only if user has cookies enabled'),
    			  ),    
    			  array(
    				  'value'     => 3,
    				  'label'     => Mage::helper('magebird_popup')->__('Show only if user has cookies disabled'),
    			  )                                                     
    		  )  
    		));
                
        $fieldset->addField('user_login', 'select', array(
    		  'label'     => Mage::helper('magebird_popup')->__('User Login'),
    		  'name'      => 'user_login',
    		  'values'    => array(
    			  array(
    				  'value'     => 1,
    				  'label'     => Mage::helper('magebird_popup')->__('Show to All users'),
    			  ),
    			  array(
    				  'value'     => 2,
    				  'label'     => Mage::helper('magebird_popup')->__('Show to logged in/registered users'),
    			  ),    
    			  array(
    				  'value'     => 3,
    				  'label'     => Mage::helper('magebird_popup')->__('Show to unlogged/unregistered users'),
    			  )                                                     
    		  )  
    		));
            
        $afterElementHtml = "<p class='nm'><small>".Mage::helper('magebird_popup')->__('Works only for logged in users. For guest visitors works only if they subscribed after you installed the Popup extension version 1.5.8+.')."</small></p>";
        
        $fieldset->addField('user_not_subscribed', 'select', array(
    		  'label'     => Mage::helper('magebird_popup')->__('If not subscribed yet'),
    		  'name'      => 'user_not_subscribed',
          	  'after_element_html' => $afterElementHtml,
    		  'values'    => array(
    			  array(
    				  'value'     => 2,
    				  'label'     => Mage::helper('magebird_popup')->__('Skip this condition'),
    			  ),    			  
            array(
    				  'value'     => 1,
    				  'label'     => Mage::helper('magebird_popup')->__('Show only if user is not subscribed yet'),
    			  )                                                  
    		  )  
    		));
    		
    		 $afterElementHtml = "<p class='nm'><small>".Mage::helper('magebird_popup')->__('If you use this option, popup will be shown only if user came to your page from specific site. Referral url <strong>persists</strong> for entire session. Use "%" if you need to use a pattern (e.g. <span style="color:#747474; font-style:italic;">%domainname%</span> to show popup if user came to your page from any page having \'domainname\' in url). Use double comma (e.g. <span style="color:#747474; font-style:italic;">%domainname%,,%another-url%</span>) to separate multiple urls. Leave empty if you want to skip this condition. IMPORTANT: This won\'t work if user came from https site and your site doesn\'t use https connection.')."</small></p>";
    		
        
        $fieldset->addField('if_referral', 'text', array(
    		  'label'     => Mage::helper('magebird_popup')->__('Show if referral url (write referral url)'),
    		  'name'      => 'if_referral',
          'after_element_html' => $afterElementHtml 
    		)); 
        
        $afterElementHtml = "<p class='nm'><small>".Mage::helper('magebird_popup')->__('Show popup only if user DIDN\'T come from specific site. Referal url persists for entire session. Use the same structure as for field "If referral url" (see previous field comment).')."</small></p>";
        $fieldset->addField('if_not_referral', 'text', array(
    		  'label'     => Mage::helper('magebird_popup')->__('If not referral url (write referral url)'),
    		  'name'      => 'if_not_referral',
          'after_element_html' => $afterElementHtml 
    		));         
        
        $afterElementHtml = "<p class='nm'><small>".Mage::helper('magebird_popup')->__("Any visitor came to your site with expired session in the past will be recognized as returning. Returning visitors can be recognized from the day you have installed this extension. Any visitor that came to your site before you installed this extension will be recognized as new visitor until he/she doesn't visit your site again.")."</small></p>";
        $fieldset->addField('if_returning', 'select', array(
    		  'label'     => Mage::helper('magebird_popup')->__('Returning or new visitor'),
    		  'name'      => 'if_returning',
    		  'values'    => array(
    			  array(
    				  'value'     => 1,
    				  'label'     => Mage::helper('magebird_popup')->__('Show to all visitors'),
    			  ),
    			  array(
    				  'value'     => 2,
    				  'label'     => Mage::helper('magebird_popup')->__('Show only to returning visitors'),
    			  ),    
    			  array(
    				  'value'     => 3,
    				  'label'     => Mage::helper('magebird_popup')->__('Show only to new visitors'),
    			  )
           ),           
          'after_element_html' => $afterElementHtml 
    		));   
        
        $afterElementHtml = "<p class='nm'><small>".Mage::helper('magebird_popup')->__('You can choose to display popup only if visitor opened at least defined number of pages. Leave empty or 1 to skip this condition.')."</small></p>";
        $fieldset->addField('num_visited_pages', 'text', array(
    		  'label'     => Mage::helper('magebird_popup')->__('Number of visited pages'),
    		  'name'      => 'num_visited_pages',
          'after_element_html' => $afterElementHtml
    		));       
        /*
        $afterElementHtml = "<p class='nm'><small>".Mage::helper('magebird_popup')->__('IMPORTANT: This works only if user is logged in. Unlogged user will be recognized as unsubscribed.')."</small></p>";
        $fieldset->addField('if_is_subscribed', 'text', array(
    		  'label'     => Mage::helper('magebird_popup')->__('Show only if user is not subscribed'),
    		  'name'      => 'if_is_subscribed',
          'after_element_html' => $afterElementHtml
    		));
        */                      
        
        $afterElementHtml = "<p class='nm'><small>".Mage::helper('magebird_popup')->__('Leave unselected (empty) to skip this condition and show to all groups.')."</small></p>";
        $fieldset->addField('customer_group', 'multiselect', array(
    		  'label'     => Mage::helper('magebird_popup')->__('Customer groups'),
    		  'name'      => 'customer_group',
          'values'    => $this->getAllCustomerGroupOptions(),
          'after_element_html' => $afterElementHtml
    		)); 
                 
        $afterElementHtml = "<p class='nm'><small>".Mage::helper('magebird_popup')->__('Use if your popup is set to be shown in search page and you want to show it if search does not return enough results. Leave empty to skip this condition.')."</small></p>";
        $fieldset->addField('search_results', 'text', array(
    		  'label'     => Mage::helper('magebird_popup')->__('If search returns less than x results'),
    		  'name'      => 'search_results',
          'after_element_html' => $afterElementHtml 
    		)); 
        
        $afterElementHtml = "<p class='nm'><small>".Mage::helper('magebird_popup')->__('For testing purposes. Leave empty to show to all users. Write ip address if you want to show popup to user with only specific ip address')."</small></p>";
        $fieldset->addField('user_ip', 'text', array(
    		  'label'     => Mage::helper('magebird_popup')->__('User ip address'),
    		  'name'      => 'user_ip',
          'after_element_html' => $afterElementHtml 
    		));                  

    		if(Mage::getSingleton('adminhtml/session')->getPopupData()){
          $form->setValues(Mage::getSingleton('adminhtml/session')->getPopupData());
          Mage::getSingleton('adminhtml/session')->setPopupData(null);
        }elseif(Mage::registry('popup_data')){
          $form->setValues(Mage::registry('popup_data')->getData());
        }
        return parent::_prepareForm();
  }
  
  function getAllCustomerGroupOptions(){
      $all = Mage::getModel('customer/group')->getCollection();
      $options = array();
      foreach($all as $group){  
          $options[] = array('value'=>$group->getId(),'label'=>$group->getData('customer_group_code'));    
      }

      return $options;
  }  
    
}
