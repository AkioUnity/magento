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
class Magebird_Popup_Block_Adminhtml_Popup_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'magebird_popup';
        $this->_controller = 'adminhtml_popup';
        
        $this->_updateButton('save', 'label', Mage::helper('magebird_popup')->__('Save Item'));
        //$this->_updateButton('save', 'onclick', 'save(ee)');
        $this->_updateButton('delete', 'label', Mage::helper('magebird_popup')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit(this)',
            'class'     => 'save',
        ), -100);
         		
		$this->_formScripts[] = "
            function toggleEditor() { 
                if (tinyMCE.getInstanceById('static_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'static_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'static_content');
                }
            }

            function save(){
                jQuery('#popup_content_parsed').val(parseContent());            
                editForm.submit();
            }  
            
            function saveAndContinueEdit(el){
                //jQuery(el).text('Working...')
                //jQuery('#popup_content_parsed').val(parseContent());
                editForm.submit($('edit_form').action+'back/edit/');
            }  
            
            //we may need this later if file_get_contents wont work for everyone
            function parseContent(){            
              if(typeof(tinyMCE) !== 'undefined'){
                tinyMCE.triggerSave();
              }
              if (location.protocol === 'https:') {
                  var ajaxUrl =  document.location.origin+'magebird_popup/index/parsePopup';
              }else{
                  var ajaxUrl = document.location.origin+'magebird_popup/index/parsePopup';
              }    
              var parsed = '';          
              jQuery.ajax({
                type: 'POST',
                url: ajaxUrl,
                data:'content='+jQuery('#popup_content').val(),
                async:false,
                success: function(response){  
                  parsed = response;              
                }, 
                error: function(response){  
                  //alert(response)            
                }         
              });  
               
              return parsed;          
            }
        ";
    }    
    
    protected function _prepareLayout()
    {
        // added this code 
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);    
        }   
        parent::_prepareLayout();
    }    

    public function getHeaderText()
    {
        if( Mage::registry('popup_data') && Mage::registry('popup_data')->getId() ) {
            return Mage::helper('magebird_popup')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('popup_data')->getTitle()));
        } else {
            return Mage::helper('magebird_popup')->__('Add Item');
        }
    }
}