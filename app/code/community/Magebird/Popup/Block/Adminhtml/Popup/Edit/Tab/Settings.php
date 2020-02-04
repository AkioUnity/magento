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
class Magebird_Popup_Block_Adminhtml_Popup_Edit_Tab_Settings extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('appearance_form', array('legend' => Mage::helper('magebird_popup')->__('Settings')));    		      
        
        $afterElementHtml = "<p class='nm'><small>" . Mage::helper('magebird_popup')->__("When popup should be shown again to the same user? If 'Show only once', popup with the same id won't be shown again until cookie lifetime expires.") . "</small></p>";
    		$fieldset->addField('showing_frequency', 'select', array(
    		  'label'     => Mage::helper('magebird_popup')->__("Show again?"),
    		  'name'      => 'showing_frequency',
    		  'values'    => array(
    			  array(
    				  'value'     => 1,
    				  'label'     => Mage::helper('magebird_popup')->__("Show until the popup is closed"),
    			  ),
            
    			  array(
    				  'value'     => 2,
    				  'label'     => Mage::helper('magebird_popup')->__("Show only once"),
    			  ),            
    
    			  array(
    				  'value'     => 3,
    				  'label'     => Mage::helper('magebird_popup')->__('Show every time'),
    			  ),
            
    			  array(
    				  'value'     => 4,
    				  'label'     => Mage::helper('magebird_popup')->__("Show until user clicks inside popup"),
    			  ),
            
    			  array(
    				  'value'     => 5,
    				  'label'     => Mage::helper('magebird_popup')->__("Show until user close it or clicks inside popup"),
    			  ),  
    			  array(
    				  'value'     => 6,
    				  'label'     => Mage::helper('magebird_popup')->__("Show until goal completed (e.g.: Subscribed newsletter)"),
    			  ), 
    			  array(
    				  'value'     => 7,
    				  'label'     => Mage::helper('magebird_popup')->__("Show once per session"),
    			  ),                                                
    		  ),
          'after_element_html' => $afterElementHtml,
    		));                                      
        
        $afterElementHtml = "<p class='nm'><small>" . Mage::helper('magebird_popup')->__("You can use also decimal dotted number (e.g.: To expire cookie in 1 hour put 0.04 which means 1 day divided with 24 hours.)") . "</small></p>";		
        $fieldset->addField('cookie_time', 'text', array(
    		  'label'     => Mage::helper('magebird_popup')->__('Cookie lifetime in days'),
    		  'name'      => 'cookie_time',
    		  'class'	  => 'validate-number',
    		  'required'  => true,
          'after_element_html' => $afterElementHtml,
    		));   
        
        $afterElementHtml = "<p class='nm'><small>" . Mage::helper('magebird_popup')->__("Only alphabet and numbers are allowed. Recommended to leave auto generated value. If you are doing A B testing with duplicate popups with similar content, it is recommended to use the same cookie id. So once user close pop up, it wont show neither this popup or neither any duplicate again") . "</small></p>";
    		$fieldset->addField('cookie_id', 'text', array(
    		  'label'     => Mage::helper('magebird_popup')->__('Cookie/popup id'),
    		  'class'     => 'required-entry',
    		  'required'  => true,
    		  'name'      => 'cookie_id',
          'after_element_html' => $afterElementHtml,
    		));   
        
        $afterElementHtml = "<p class='nm'><small>".Mage::helper('magebird_popup')->__("Leave empty to skip this condition. When you are doing split testing, it is recommended to test different versions the same day/period to see realistic data. To show popup A only to 20% of visitors, simply select two options (e.g. Second and Forth visitor). To show popup B to another 30%, select inside popup B another 3 options. This means 50% of visitors won't see popup, 20% will see popup A and 30% will see popup B.")."</small></p>";
        $fieldset->addField('show_every_n', 'multiselect', array(
    		  'label'     => Mage::helper('magebird_popup')->__('Among every 10 visitors, show to'),
    		  'name'      => 'show_every_n',
          'style'=>'height:160px;',
    		  'values'    => array(
    			  array(
    				  'value'     => 1,
    				  'label'     => Mage::helper('magebird_popup')->__("First visitor"),
    			  ), 
    			  array(
    				  'value'     => 2,
    				  'label'     => Mage::helper('magebird_popup')->__("Second visitor"),
    			  ),             
    			  array(
    				  'value'     => 3,
    				  'label'     => Mage::helper('magebird_popup')->__("Third visitor"),
    			  ),            
    			  array(
    				  'value'     => 4,
    				  'label'     => Mage::helper('magebird_popup')->__('Forth visitor'),
    			  ),        
    			  array(
    				  'value'     => 5,
    				  'label'     => Mage::helper('magebird_popup')->__('Fifth visitor'),
    			  ), 
    			  array(
    				  'value'     => 6,
    				  'label'     => Mage::helper('magebird_popup')->__('Sixth visitor'),
    			  ), 
    			  array(
    				  'value'     => 7,
    				  'label'     => Mage::helper('magebird_popup')->__('Seventh visitor'),
    			  ), 
    			  array(
    				  'value'     => 8,
    				  'label'     => Mage::helper('magebird_popup')->__('Eighth visitor'),
    			  ),        
    			  array(
    				  'value'     => 9,
    				  'label'     => Mage::helper('magebird_popup')->__('Nineth visitor'),
    			  ),                                                                                                                    
    		  ),          
          'after_element_html' => $afterElementHtml,   
    		));         
        
        $afterElementHtml = "<p class='nm'><small>" . Mage::helper('magebird_popup')->__("If visitor leaves window open without beeing active (for example if the user have a phone call), this can confuse the statistic. That is why it is recommended to set max time per view.") . "</small></p>";
        $fieldset->addField('max_count_time', 'text', array(
    		  'label'     => Mage::helper('magebird_popup')->__('Max time per view to track statistics (in seconds)'),
    		  'name'      => 'max_count_time',
    		  'class'	  => 'validate-number',
    		  'required'  => true,
          'after_element_html' => $afterElementHtml,
    		));             
        
        $afterElementHtml = "<p class='nm'><small>" . Mage::helper('magebird_popup')->__("Available for popups with background overlay.") . "</small></p>";
        $fieldset->addField('close_on_overlayclick','select',array(
                    'label' => Mage::helper('magebird_popup')->__('Close when click outside popup'),
                    'name' =>  'close_on_overlayclick',
              		  'values'    => array(
              			  array(
              				  'value'     => 0,
              				  'label'     => Mage::helper('magebird_popup')->__('No'),
              			  ),
              
              			  array(
              				  'value'     => 1,
              				  'label'     => Mage::helper('magebird_popup')->__('Yes'),
              			  ),
              		  ),
                    'after_element_html' => $afterElementHtml,                          
        )); 
        
        $afterElementHtml = "<p class='nm'><small>".Mage::helper('magebird_popup')->__("Leave 0 or empty if you don't want popup to be closed automatically")."</small></p>";
        $fieldset->addField('close_on_timeout', 'text', array(
    		  'label'     => Mage::helper('magebird_popup')->__('Close automatically after x seconds'),
    		  'name'      => 'close_on_timeout',
          'after_element_html' => $afterElementHtml,
    		)); 
        
        $afterElementHtml = "<p class='nm'><small>".Mage::helper('magebird_popup')->__("If you added more popups for the same pages, you can stop further popups with less priority to be shown on the same page.")."</small></p>";
        $fieldset->addField('stop_further', 'select', array(
    		  'label'     => Mage::helper('magebird_popup')->__('Stop further popups'),
    		  'name'      => 'stop_further',
    		  'values'    => array(
    			  array(
    				  'value'     => 1,
    				  'label'     => Mage::helper('magebird_popup')->__('Yes'),
    			  ),
    
    			  array(
    				  'value'     => 2,
    				  'label'     => Mage::helper('magebird_popup')->__('No'),
    			  ),
    		  ),
          'after_element_html' => $afterElementHtml,   
    		));                
        
        $afterElementHtml = "<p class='nm'><small>".Mage::helper('magebird_popup')->__("If you added more popups for the same pages, select display priority")."</small></p>";
        $fieldset->addField('priority', 'text', array(
    		  'label'     => Mage::helper('magebird_popup')->__('Priority'),
    		  'name'      => 'priority',
          'after_element_html' => $afterElementHtml,
    		));                                                            
    
    		if(Mage::getSingleton('adminhtml/session')->getPopupData()){
          $form->setValues(Mage::getSingleton('adminhtml/session')->getPopupData());
          Mage::getSingleton('adminhtml/session')->setPopupData(null);
        }elseif(Mage::registry('popup_data')){
          $form->setValues(Mage::registry('popup_data')->getData());
        }
        return parent::_prepareForm();
  }
    
}
