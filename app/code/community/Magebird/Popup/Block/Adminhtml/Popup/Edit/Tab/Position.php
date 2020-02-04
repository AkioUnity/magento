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
class Magebird_Popup_Block_Adminhtml_Popup_Edit_Tab_Position extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('position_form', array('legend' => Mage::helper('magebird_popup')->__('Position')));        
        
        $afterElementHtml = "<p class='nm'><small>".Mage::helper('magebird_popup')->__("If you selected percentage for 'Popup width unit' you can only use Center.")."</small></p>";              
        $fieldset->addField('horizontal_position','select',array(
                    'label' => Mage::helper('magebird_popup')->__('Horizontal position'),
                    'name' =>  'horizontal_position',
                    'required'=>true,
              		  'values'    => array(
              			  array(
              				  'value'     => 1,
              				  'label'     => Mage::helper('magebird_popup')->__('Center'),
              			  ),            
              			  array(
              				  'value'     => 2,
              				  'label'     => Mage::helper('magebird_popup')->__('Define px from left of the screen'),
              			  ),
              			  array(
              				  'value'     => 3,
              				  'label'     => Mage::helper('magebird_popup')->__('Define px from right of the screen'),
              			  ),  
              			  array(
              				  'value'     => 4,
              				  'label'     => Mage::helper('magebird_popup')->__('Define px from center to left'),
              			  ), 
              			  array(
              				  'value'     => 5,
              				  'label'     => Mage::helper('magebird_popup')->__('Define px from center to right'),
              			  ),       
              			  array(
              				  'value'     => 6,
              				  'label'     => Mage::helper('magebird_popup')->__('Left px absolute to defined element'),
              			  ),                                                                                                                                 
              		  ),
                    'after_element_html' => $afterElementHtml            
        ));
              
        
        $afterElementHtml = '<p class="nm"><small>' .  Mage::helper('magebird_popup')->__("If you selected horizontal position other than 'Center', define how many px from defined position you want popup to appear.") . '</small></p>';
        $fieldset->addField('horizontal_position_px','text',array(
                    'label' => Mage::helper('magebird_popup')->__('Px from defined position'),
                    'name' =>  'horizontal_position_px',
                    'required'=>true,
                    'after_element_html' => $afterElementHtml,                                                                                                      
        ));
        
        
        $fieldset->addField('vertical_position','select',array(
                    'label' => Mage::helper('magebird_popup')->__('Vertical position'),
                    'name' =>  'vertical_position',
                    'required'=>true, 
              		  'values'    => array(
              			  array(
              				  'value'     => 1,
              				  'label'     => Mage::helper('magebird_popup')->__('Define px from top'),
              			  ),            
              			  array(
              				  'value'     => 2,
              				  'label'     => Mage::helper('magebird_popup')->__('Define px from bottom'),
              			  ),     
              			  array(
              				  'value'     => 3,
              				  'label'     => Mage::helper('magebird_popup')->__('Top px absolute to defined element'),
              			  ),  
              			  array(
              				  'value'     => 4,
              				  'label'     => Mage::helper('magebird_popup')->__('Show on top and push page content down'),
              			  ),                                                                                                                                       
              		  ),            
        ));
        
        $fieldset->addField('vertical_position_px','text',array(
                    'label' => Mage::helper('magebird_popup')->__('How many px'),
                    'name' =>  'vertical_position_px',
                    'required'=>true,                                                                                                
        ));   
        
        $afterElementHtml = '<p class="nm"><small>' .  Mage::helper('magebird_popup')->__("If you selected 'Left px absolute to defined element' and 'Top px absolute to defined element', you need to define from which element should be calculated left position. Write element selector. Otherwise leave empty. e.g. #idName, .className, div input#idName. Read more about selectors <a href='http://www.w3schools.com/jquery/jquery_ref_selectors.asp' target='_blank'>here</a>") . '</small></p>';
        $fieldset->addField('element_id_position','text',array(
                    'label' => Mage::helper('magebird_popup')->__('Define element id'),
                    'name' =>  'element_id_position',
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
