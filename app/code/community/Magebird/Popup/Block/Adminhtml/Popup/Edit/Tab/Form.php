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
class Magebird_Popup_Block_Adminhtml_Popup_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{

  protected function _prepareForm(){
      $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post', 'enctype' => 'multipart/form-data'));  
  		$this->setForm($form);   
      //zend_debug::dump(Mage::getSingleton('cms/wysiwyg_config')->getConfig()->getData()); exit;     
      $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')
      ->getConfig(array('add_variables' => false, 
                        'add_widgets' => true,
                        'files_browser_window_url'=>Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index'),
                        'theme' => 'advanced',
                        'force_br_newlines' => 'false',
                        'valid_elements' => '*[*]',
                        'fontsize_formats' => '8px 10px 12px 14px 18px 24px 36px'
                        ));		
      $fieldset = $form->addFieldset('popup_form', array('legend'=>Mage::helper('magebird_popup')->__('Item information')));
  
  		$fieldset->addField('title', 'text', array(
  		  'label'     => Mage::helper('magebird_popup')->__('Title'),
  		  'class'     => 'required-entry',
  		  'required'  => true,
  		  'name'      => 'title',
  		));             
  		
  		$popupType = $fieldset->addField('popup_type', 'select', array(
  		  'label'     => Mage::helper('magebird_popup')->__('Popup Content Type'),
  		  'name'      => 'popup_type',
  		  'values'    => array(
  			  array(
  				  'value'     => 2,
  				  'label'     => Mage::helper('magebird_popup')->__('Custom Content (editor)'),
  			  ),
  			  array(
  				  'value'     => 1,
  				  'label'     => Mage::helper('magebird_popup')->__('Image'),
  			  ),                                                                       
  		  ),

  		));
  
      $image=$fieldset->addField('image','image',array(
                  'label' => Mage::helper('magebird_popup')->__('Image'),
                  'name' =>  'image',
                  'class'     => 'required-entry required-file',
                  'required'=>true,
      ));  
      
      $afterElementHtml = "<p class='nm'><small>".Mage::helper('magebird_popup')->__('Tip: If you see widget icon %s inside editor, double click to the icon to get extra configuration options. You can also display dynamic product data inside popup (<a target="_blank" href="%s">see instructions</a>).','<img style="vertical-align:middle;margin-top:3px; margin-bottom:-5px;" src="'.$this->getSkinUrl('magebird_popup/images/widget.png').'" />','http://www.magebird.com/magento-extensions/popup.html?tab=faq#productInsidePopup').'<br>'.Mage::helper('magebird_popup')->__('<a target="_blank" href="%s">For design instructions click here.</a>','http://www.magebird.com/magento-extensions/popup.html?tab=faq#designTips').'</small></p>';
      $popupContent=$fieldset->addField('popup_content','editor',array(
                  'label' => Mage::helper('magebird_popup')->__('Custom content'),
                  'name' =>  'popup_content',
                  'wysiwyg' => true, 
                  'config'    => $wysiwygConfig,
                  'required'=>true,
                  'style'=>'width:500px;height:350px;',
                  'after_element_html' => $afterElementHtml
      ));       
      
      $fieldset->addField('popup_content_parsed','hidden',array(
                  'name' =>  'popup_content_parsed',
                  'wysiwyg' => false, 
                  'required'=>false,
                  'visible' => false
      ));           
      
      $afterElementHtml = '<p class="nm"><small>' . Mage::helper('magebird_popup')->__('Leave empty if no link') . '</small></p>';
      $url=$fieldset->addField('url','text',array(
                  'label' => Mage::helper('magebird_popup')->__('Url of image link'),
                  'name' =>  'url',
                  'after_element_html' => $afterElementHtml,
      ));
      
      $afterElementHtml = '<p class="nm"><small> ' . Mage::helper('magebird_popup')->__('How many px or %. Use just number and select unit in the next field. Border and padding size will be added to total width.') . '</small></p>';
      $fieldset->addField('width','text',array(
                  'label' => Mage::helper('magebird_popup')->__('Popup content width'),
                  'name' =>  'width',
                  'required'=>true,
                  'after_element_html' => $afterElementHtml
      ));  
       
      $afterElementHtml = '<p class="nm"><small>'.Mage::helper('magebird_popup')->__('Use px if you want fixed width. Use % if you want dynamic (responsive design for mobile).').'</small></p>';
      $widthUnit = $fieldset->addField('width_unit','select',array(
                  'label' => Mage::helper('magebird_popup')->__('Popup width unit'),
                  'name' =>  'width_unit',
                  'required'=>true,
            		  'values'    => array(
            			  array(
            				  'value'     => 1,
            				  'label'     => Mage::helper('magebird_popup')->__('Px'),
            			  ),
            
            			  array(
            				  'value'     => 2,
            				  'label'     => Mage::helper('magebird_popup')->__('Percentage (%)'),
            			  ),
                  ),
                  'after_element_html' => $afterElementHtml                                    
      ));  
      
      $afterElementHtml = '<p class="nm"><small>'.Mage::helper('magebird_popup')->__('You can limit width to max width px if % width is wider than max px.').'</small></p>';
      $maxWidth = $fieldset->addField('max_width','text',array(
                  'label' => Mage::helper('magebird_popup')->__('Popup content max width (in px)'),
                  'name' =>  'max_width',
                  'required'=>false,
                  'after_element_html' => $afterElementHtml                                   
      ));                                                                            
                		
  		$fieldset->addField('status', 'select', array(
  		  'label'     => Mage::helper('magebird_popup')->__('Status'),
  		  'name'      => 'status',
  		  'values'    => array(
  			  array(
  				  'value'     => 1,
  				  'label'     => Mage::helper('magebird_popup')->__('Enabled'),
  			  ),
  
  			  array(
  				  'value'     => 2,
  				  'label'     => Mage::helper('magebird_popup')->__('Disabled'),
  			  ),
  		  ),
  		));
  		
  		if (!Mage::app()->isSingleStoreMode()) {
          $fieldset->addField('store_id', 'multiselect', array(
              'name' => 'stores[]',
              'label' => Mage::helper('magebird_popup')->__('Store View'),
              'title' => Mage::helper('magebird_popup')->__('Store View'),
              'required' => true,
              'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
          'style' => 'height:150px',
          ));
      }
  		
      $afterElementHtml = "<p class='nm'><small>".Mage::helper('magebird_popup')->__("Leave unselected (empty) if you want to show popup at all pages. Hold 'ctrl' to select more options or to unselect.")."  </small></p>";
  		$targetPage = $fieldset->addField('page_id', 'multiselect', array(
  		  'label'     => Mage::helper('magebird_popup')->__('Show at'),
  		  'name'      => 'pages',
        'style'=>'height:175px;',
  		  'values'    => array(
  			  array(
  				  'value'     => 1,
  				  'label'     => Mage::helper('magebird_popup')->__('Home page'),
  			  ),
  			  array(
  				  'value'     => 2,
  				  'label'     => Mage::helper('magebird_popup')->__('Product pages'),
  			  ),
  			  array(
  				  'value'     => 3,
  				  'label'     => Mage::helper('magebird_popup')->__('Category pages'),
  			  ),
  			  array(
  				  'value'     => 4,
  				  'label'     => Mage::helper('magebird_popup')->__('Checkout Onepage'),
  			  ),
  			  array(
  				  'value'     => 5,
  				  'label'     => Mage::helper('magebird_popup')->__('Cart'),
  			  ),   
  			  array(
  				  'value'     => 6,
  				  'label'     => Mage::helper('magebird_popup')->__('Specified Url (Define custom url)'),
  			  ),
  			  array(
  				  'value'     => 7,
  				  'label'     => Mage::helper('magebird_popup')->__('Other pages (any other page)'),
  			  )                                     
  		  ),
        'after_element_html' => $afterElementHtml,
  		));
      
      $afterElementHtml = '<p class="nm"><small>' . Mage::helper('magebird_popup')->__("Leave empty if it applies to all categories. Write category id if you want to show popup only for specific category. Write category ids separated with comma if you want to apply to more categories.") . '</small></p>';
      $fieldset->addField('category_ids','text',array(
                  'label' => Mage::helper('magebird_popup')->__('Category ids'),
                  'name' =>  'category_ids',
                  'required'=>false,
                  'after_element_html' => $afterElementHtml,                  
      ));  
      $afterElementHtml = "<p class='nm'><small>".Mage::helper('magebird_popup')->__('Write page url %s. Use \'&#37;\' if you need to use a pattern (e.g. <span style="color:#747474; font-style:italic;">&#37;contact&#37;</span> to show at every page that has \'contact\' in url). Use double comma %s to separate multiple urls.',"(e.g. <span style='color:#747474; font-style:italic;'>".$_SERVER['HTTP_HOST']."/contacts/</span>)","(e.g. <span style='color:#747474; font-style:italic;'>&#37;domainname&#37;,,&#37;another-url&#37;</span>)")."</small></p>";
      $fieldset->addField('specified_url','text',array(
                  'label' => Mage::helper('magebird_popup')->__('Specified Url'),
                  'name' =>  'specified_url',
                  'required'=>false,
                  'after_element_html' => $afterElementHtml,                  
      ));          

      $afterElementHtml = "<p class='nm'><small>".Mage::helper('magebird_popup')->__("Use if you want to exclude page or url pattern. Write page url %s. Use '&#37;' if you need to use a pattern (e.g. %s to exclude all pages that have 'quickview' in url). Use double comma %s to separate multiple urls.","(e.g. (<span style='color:#747474; font-style:italic;'>".$_SERVER["HTTP_HOST"]."/contacts/</span>)","<span style='color:#747474; font-style:italic;'>%quickview%</span>","(e.g. <span style='color:#747474; font-style:italic;'>&#37;domainname&#37;,,&#37;another-url&#37;</span>)")."</small></p>";                                                                                                                                             
      $fieldset->addField('specified_not_url','text',array(
                  'label' => Mage::helper('magebird_popup')->__('Exclude Url'),
                  'name' =>  'specified_not_url',
                  'required'=>false,
                  'after_element_html' => $afterElementHtml,                  
      ));    
            
      $afterElementHtml = '<p class="nm"><small>' . Mage::helper('magebird_popup')->__("Leave empty if it applies to all products. Write product id if you want to show popup only for specific product. Write product ids separated with comma if you want to apply to more products.") . '</small></p>';
      $fieldset->addField('product_ids','text',array(
                  'label' => Mage::helper('magebird_popup')->__('Product ids'),
                  'name' =>  'product_ids',
                  'required'=>false,
                  'after_element_html' => $afterElementHtml,
      )); 
      
  		$showWhen = $fieldset->addField('show_when', 'select', array(
  		  'label'     => Mage::helper('magebird_popup')->__('Show when'),
  		  'name'      => 'show_when',
  		  'values'    => array(
  			  array(
  				  'value'     => 1,
  				  'label'     => Mage::helper('magebird_popup')->__('After page is loaded'),
  			  ),
  			  array(
  				  'value'     => 2,
  				  'label'     => Mage::helper('magebird_popup')->__('Define seconds after page is loaded'),
  			  ),
  			  array(
  				  'value'     => 7,
  				  'label'     => Mage::helper('magebird_popup')->__('Define seconds user spent on entire site'),
  			  ),     
  			  array(
  				  'value'     => 8,
  				  'label'     => Mage::helper('magebird_popup')->__('Define seconds after 1st item added to cart'),
  			  ),                   
  			  array(
  				  'value'     => 3,
  				  'label'     => Mage::helper('magebird_popup')->__('After user uses scroller'),
  			  ),
  			  array(
  				  'value'     => 4,
  				  'label'     => Mage::helper('magebird_popup')->__('On click'),
  			  ),
  			  array(
  				  'value'     => 5,
  				  'label'     => Mage::helper('magebird_popup')->__('On hover'),
  			  ),
  			  array(
  				  'value'     => 6,
  				  'label'     => Mage::helper('magebird_popup')->__('Exit intent (When mouse leaves browser window)'),
  			  )                                                                      
  		  )
  		));                

      $afterElementHtml = '<p class="nm"><small>' . Mage::helper('magebird_popup')->__("Close popup automatically when hover out.") . '</small></p>';
      $hoverOut = $fieldset->addField('close_on_hoverout','select',array(
                  'label' => Mage::helper('magebird_popup')->__('Close on hover out'),
                  'name' =>  'close_on_hoverout',
                  'required'=>false,
            		  'values'    => array(
            			  array(
            				  'value'     => 1,
            				  'label'     => Mage::helper('magebird_popup')->__('Yes'),
            			  ),
            
            			  array(
            				  'value'     => 2,
            				  'label'     => Mage::helper('magebird_popup')->__('No'),
            			  )
                   ),                  
                  'after_element_html' => $afterElementHtml                
      )); 
            
      $afterElementHtml = '<p class="nm"><small>' . Mage::helper('magebird_popup')->__("Write how many seconds after page loads the popup should appear.") . '</small></p>';
      $secondsDelay = $fieldset->addField('seconds_delay','text',array(
                  'label' => Mage::helper('magebird_popup')->__('Seconds'),
                  'name' =>  'seconds_delay',
                  'required'=>false,
                  'after_element_html' => $afterElementHtml,                
      )); 
      
      $afterElementHtml = '<p class="nm"><small>' . Mage::helper('magebird_popup')->__("Max 7200. After 2 hours timer automatically resets to 0 again.") . '</small></p>';
      $totalSecondsDelay = $fieldset->addField('total_seconds_delay','text',array(
                  'label' => Mage::helper('magebird_popup')->__('Seconds'),
                  'name' =>  'total_seconds_delay',
                  'required'=>false,
                  'after_element_html' => $afterElementHtml,                
      ));    
      
      $afterElementHtml = '<p class="nm"><small>' . Mage::helper('magebird_popup')->__("Popup will show up only if user adds product to cart. You can define how many seconds after that popup should show up.") . '</small></p>';
      $cartSecondsDelay = $fieldset->addField('cart_seconds_delay','text',array(
                  'label' => Mage::helper('magebird_popup')->__('Seconds'),
                  'name' =>  'cart_seconds_delay',
                  'required'=>false,
                  'after_element_html' => $afterElementHtml,                
      ));          
      
      $afterElementHtml = '<p class="nm"><small>' . Mage::helper('magebird_popup')->__("Show popup after scrolling how many px from the top of the page.") . '</small></p>';
      $scrollPx = $fieldset->addField('scroll_px','text',array(
                  'label' => Mage::helper('magebird_popup')->__('Scrolling px'),
                  'name' =>  'scroll_px',
                  'required'=>false,
                  'after_element_html' => $afterElementHtml,                 
      ));  
      
      $afterElementHtml = '<p class="nm"><small>' . Mage::helper('magebird_popup')->__('Write click selector e.g. #idName, .className, div input#idName. Read more about selectors <a href="http://www.w3schools.com/jquery/jquery_ref_selectors.asp" target="_blank">here</a>.') . '</small></p>';
      $clickSelector = $fieldset->addField('click_selector','text',array(
                  'label' => Mage::helper('magebird_popup')->__('Click selector'),
                  'name' =>  'click_selector',
                  'required'=>false,
                  'after_element_html' => $afterElementHtml,                 
      )); 
      
      $afterElementHtml = '<p class="nm"><small>' . Mage::helper('magebird_popup')->__('Write hover selector e.g. #idName, .className, div input#idName. Read more about selectors <a href="http://www.w3schools.com/jquery/jquery_ref_selectors.asp" target="_blank">here</a>.') . '</small></p>';
      $hoverSelector = $fieldset->addField('hover_selector','text',array(
                  'label' => Mage::helper('magebird_popup')->__('Hover selector'),
                  'name' =>  'hover_selector',
                  'required'=>false,
                  'after_element_html' => $afterElementHtml,                 
      ));                                   
      
      $this->setChild('form_after',$this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                      ->addFieldMap($popupType->getHtmlId(),$popupType->getName())
                      ->addFieldMap($image->getHtmlId(),$image->getName())
                      ->addFieldMap($popupContent->getHtmlId(),$popupContent->getName())                
                      ->addFieldMap($url->getHtmlId(),$url->getName())
                      ->addFieldMap($targetPage->getHtmlId(),$targetPage->getName())
                      ->addFieldMap($showWhen->getHtmlId(),$showWhen->getName())
                      ->addFieldMap($secondsDelay->getHtmlId(),$secondsDelay->getName())
                      ->addFieldMap($totalSecondsDelay->getHtmlId(),$totalSecondsDelay->getName())
                      ->addFieldMap($cartSecondsDelay->getHtmlId(),$cartSecondsDelay->getName())                      
                      ->addFieldMap($scrollPx->getHtmlId(),$scrollPx->getName())
                      ->addFieldMap($clickSelector->getHtmlId(),$clickSelector->getName())
                      ->addFieldMap($hoverSelector->getHtmlId(),$hoverSelector->getName())
                      ->addFieldMap($hoverOut->getHtmlId(),$hoverOut->getName())
                      ->addFieldMap($maxWidth->getHtmlId(),$maxWidth->getName())
                      ->addFieldMap($widthUnit->getHtmlId(),$widthUnit->getName())                                               
                      ->addFieldDependence($image->getName(),$popupType->getName(),1)
                      ->addFieldDependence($popupContent->getName(),$popupType->getName(),2)
                      ->addFieldDependence($url->getName(),$popupType->getName(),1)
                      ->addFieldDependence($totalSecondsDelay->getName(),$showWhen->getName(),7)
                      ->addFieldDependence($cartSecondsDelay->getName(),$showWhen->getName(),8)                      
                      ->addFieldDependence($secondsDelay->getName(),$showWhen->getName(),2)
                      ->addFieldDependence($scrollPx->getName(),$showWhen->getName(),3)
                      ->addFieldDependence($clickSelector->getName(),$showWhen->getName(),4)
                      ->addFieldDependence($hoverSelector->getName(),$showWhen->getName(),5)
                      ->addFieldDependence($hoverOut->getName(),$showWhen->getName(),5)
                      ->addFieldDependence($maxWidth->getName(),$widthUnit->getName(),2)                                        
      );                         
                      
  		$afterElementHtml = '<p class="nm"><small>' . Mage::helper('magebird_popup')->__('Date is in local store view time.') . '</small></p>';
      $dateFormatIso = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
      $fieldset->addField('from_date', 'date', array(
          'name' => 'from_date',
          'time' => true,
          'label' => Mage::helper('magebird_popup')->__('From Date'),
          'title' => Mage::helper('magebird_popup')->__('From Date'),
          'image' => $this->getSkinUrl('images/grid-cal.gif'), 
          'input_format' => Varien_Date::DATETIME_INTERNAL_FORMAT,
          'format'       => $dateFormatIso,
          'after_element_html' => $afterElementHtml,
      ));
  
      $fieldset->addField('to_date', 'date', array(
          'name' => 'to_date',
          'time' => true,
          'label' => Mage::helper('magebird_popup')->__('To Date'),
          'title' => Mage::helper('magebird_popup')->__('To Date'),
          'image' => $this->getSkinUrl('images/grid-cal.gif'),
          'input_format' => Varien_Date::DATETIME_INTERNAL_FORMAT,
          'format'       => $dateFormatIso
      ));

      if ( Mage::getSingleton('adminhtml/session')->getPopupData() ){
          $form->setValues(Mage::getSingleton('adminhtml/session')->getPopupData());
          Mage::getSingleton('adminhtml/session')->setPopupData(null);
      } elseif ( Mage::registry('popup_data') ) {
          $form->setValues(Mage::registry('popup_data')->getData());
      }
      return parent::_prepareForm();
  }
}