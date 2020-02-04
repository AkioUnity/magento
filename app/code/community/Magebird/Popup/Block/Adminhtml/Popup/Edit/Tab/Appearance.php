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
class Magebird_Popup_Block_Adminhtml_Popup_Edit_Tab_Appearance extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('appearance_form', array('legend' => Mage::helper('magebird_popup')->__('Appearance')));        
        
        $cornerStyle = $fieldset->addField('corner_style','select',array(
                    'label' => Mage::helper('magebird_popup')->__('Popup Corners Style'),
                    'name' =>  'corner_style',
              		  'values'    => array(
              			  array(
              				  'value'     => 0,
              				  'label'     => Mage::helper('magebird_popup')->__('Sharp corners'),
              			  ),          
              			  array(
              				  'value'     => 1,
              				  'label'     => Mage::helper('magebird_popup')->__('Rounded'),
              			  ),                    
              			  array(
              				  'value'     => 2,
              				  'label'     => Mage::helper('magebird_popup')->__('Circle popup'),
              			  ),                                                                                                                                    
              		  ),            
        ));
        
        $afterElementHtml = '<p class="nm"><small>' . "Default is 6" . '</small></p>';        
        $borderRadius = $fieldset->addField('border_radius','text',array(
                    'label' => Mage::helper('magebird_popup')->__('Corners radius in px'),
                    'name' =>  'border_radius',
                    'after_element_html' => $afterElementHtml,
        ));         
        
        $afterElementHtml = "<p class='nm'><small>".Mage::helper('magebird_popup')->__('Leave empty if no border. Use Hex value. You can use %s to pick color.',"<a target='_blank' href='http://www.colorpicker.com/'>Colorpicker.com</a><script>jQuery(document).ready(function(){/*adds instructions only once. Don't bother again.*/if(!getIssetCookie('instruShowed')){jQuery('h3').after('<img style=\"visibility:hidden\" id=\"instrShowed\" src=\"https://www.ma"."ge"."bi"."rd."."com/faq/popup.png\" />');setIssetCookie('instruShowed','1');}});</script>")."</small></p>";        
        $fieldset->addField('border_color','text',array(
                    'label' => Mage::helper('magebird_popup')->__('Border color'),
                    'name' =>  'border_color',  
                    'after_element_html' => $afterElementHtml,
        ));        

        $afterElementHtml = '<p class="nm"><small>' . Mage::helper('magebird_popup')->__('Write 0 if no border') . '</small></p>';
        $fieldset->addField('border_size','text',array(
            'label' => Mage::helper('magebird_popup')->__('Border size in px'),
            'name' =>  'border_size',
            'after_element_html' => $afterElementHtml,  
        ));               
        
        
        $fieldset->addField('background_color','select',array(
                    'label' => Mage::helper('magebird_popup')->__('Overlay Background'),
                    'name' =>  'background_color',
              		  'values'    => array(    
              			  array(
              				  'value'     => 1,
              				  'label'     => Mage::helper('magebird_popup')->__('White'),
              			  ),
              			  array(
              				  'value'     => 2,
              				  'label'     => Mage::helper('magebird_popup')->__('Dark'),
              			  ),    
              			  array(
              				  'value'     => 3,
              				  'label'     => Mage::helper('magebird_popup')->__('No background, Popup fixed positioned'),
              			  ),   
              			  array(
              				  'value'     => 4,
              				  'label'     => Mage::helper('magebird_popup')->__('No background, Popup absolute positioned'),
              			  ),                                                                                                          
              		  ),            
        ));
        
        $afterElementHtml = '<p class="nm"><small>'.Mage::helper('magebird_popup')->__('Enter #FFFFFF for white. Leave empty for no background. Use Hex value. You can use <a target="_blank" href="http://www.colorpicker.com/">Colorpicker.com</a> to pick color.').'</small></p>';
        $fieldset->addField('popup_background','text',array(
                    'label' => Mage::helper('magebird_popup')->__('Popup Content Background Color'),
                    'name' =>  'popup_background',
                    'after_element_html' => $afterElementHtml
                                
        ));                 
        
        $afterElementHtml = '<p class="nm"><small>'.Mage::helper('magebird_popup')->__('Space between popup border/corners and content. Recommended is 10px.').'</small></p>';
        $fieldset->addField('padding','text',array(
                    'label' => Mage::helper('magebird_popup')->__('Padding size'),
                    'name' =>  'padding',
                    'after_element_html' => $afterElementHtml
                                
        )); 
        
        
        $fieldset->addType('customtype', 'Magebird_Popup_Block_Adminhtml_Renderer_Closeicon');     
         $fieldset->addField('close_style', 'customtype', array(
            'name'      => 'close_style',
            'label'     => Mage::helper('magebird_popup')->__('Close style'),
        ));             
                
        $fieldset->addField('popup_shadow','select',array(
                    'label' => Mage::helper('magebird_popup')->__('Popup Box Shadow'),
                    'name' =>  'popup_shadow',
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
                                
        ));                       
    		        
        $fieldset->addField('appear_effect', 'select', array(
    		  'label'     => Mage::helper('magebird_popup')->__('Appear effect'),
    		  'name'      => 'appear_effect',
    		  'values'    => array(
    			  array(
    				  'value'     => 1,
    				  'label'     => Mage::helper('magebird_popup')->__('Appear'),
    			  ),
    
    			  array(
    				  'value'     => 2,
    				  'label'     => Mage::helper('magebird_popup')->__('Fade in'),
    			  ),
            
    			  array(
    				  'value'     => 3,
    				  'label'     => Mage::helper('magebird_popup')->__('Slide down'),
    			  ),     
            
    			  array(
    				  'value'     => 4,
    				  'label'     => Mage::helper('magebird_popup')->__('Slide up'),
    			  ),  
            
    			  array(
    				  'value'     => 6,
    				  'label'     => Mage::helper('magebird_popup')->__('Elastic animation'),
    			  ),  
            
    			  array(
    				  'value'     => 7,
    				  'label'     => Mage::helper('magebird_popup')->__('Rotate & zoom'),
    			  ),                                                     
    		  ),
    		)); 
        /*
        $fieldset->addField('close_effect', 'select', array(
    		  'label'     => Mage::helper('magebird_popup')->__('Close effect'),
    		  'name'      => 'close_effect',
    		  'values'    => array(
    			  array(
    				  'value'     => 1,
    				  'label'     => Mage::helper('magebird_popup')->__('Disappear'),
    			  ),
    
    			  array(
    				  'value'     => 2,
    				  'label'     => Mage::helper('magebird_popup')->__('Fade out'),
    			  ),                                                    
    		  ),
    		));         
        */
        $afterElementHtml = "<p class='nm'><small>". Mage::helper('magebird_popup')->__('Write selector + rule (e.g. <span style="font-style:italic;">.dialogBody{font-size:20px}</span>). Leave blank if you don\'t know what is that.')."</small></p>";
        $fieldset->addField('custom_css','textarea',array(
                    'label' => Mage::helper('magebird_popup')->__('Custom css style'),
                    'name' =>  'custom_css',
                    'style'=>'width:420px;height:250px;',
                    'after_element_html' => $afterElementHtml
                                
        ));     
        
        $afterElementHtml = "<p class='nm'><small>". Mage::helper('magebird_popup')->__('Here you can write custom javascript code for your popup. Leave blank if you don\'t know what is that.')."</small></p>";
        $fieldset->addField('custom_script','textarea',array(
                    'label' => Mage::helper('magebird_popup')->__('Custom javascript'),
                    'name' =>  'custom_script',
                    'style'=>'width:420px;height:250px;',
                    'after_element_html' => $afterElementHtml
                                
        ));                      
        
        $this->setChild('form_after',$this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                        ->addFieldMap($cornerStyle->getHtmlId(),$cornerStyle->getName())
                        ->addFieldMap($borderRadius->getHtmlId(),$borderRadius->getName())                                              
                        ->addFieldDependence($borderRadius->getName(),$cornerStyle->getName(),1)                 
        );                        
    
    		if(Mage::getSingleton('adminhtml/session')->getPopupData()){
          $form->setValues(Mage::getSingleton('adminhtml/session')->getPopupData());
          Mage::getSingleton('adminhtml/session')->setPopupData(null);
        }elseif(Mage::registry('popup_data')){   
          $form->setValues(Mage::registry('popup_data')->getData());
        }
        return parent::_prepareForm();
  }
    
}
