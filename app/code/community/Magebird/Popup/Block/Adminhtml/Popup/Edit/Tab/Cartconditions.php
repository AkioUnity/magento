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
class Magebird_Popup_Block_Adminhtml_Popup_Edit_Tab_Cartconditions extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('appearance_form', array('legend' => Mage::helper('magebird_popup')->__('Cart conditions')));    		         

        $afterElementHtml = "<p class='nm'><small>".Mage::helper('magebird_popup')->__('Show popup only if current user has any pending payment order in history. Works only if user is logged in.')."</small></p>";
        $fieldset->addField('if_pending_order', 'select', array(
    		  'label'     => Mage::helper('magebird_popup')->__('If pending payment order'),
    		  'name'      => 'if_pending_order',
    		  'values'    => array(
    			  array(
    				  'value'     => 0,
    				  'label'     => Mage::helper('magebird_popup')->__('Skip this condition'),
    			  ),    			  
            array(
    				  'value'     => 1,
    				  'label'     => Mage::helper('magebird_popup')->__('Yes, apply this condition'),
    			  ),
    		  ),          
          'after_element_html' => $afterElementHtml
    		));
                
        $productsInCart = $fieldset->addField('product_in_cart', 'select', array(
    		  'label'     => Mage::helper('magebird_popup')->__('If product in cart'),
    		  'name'      => 'product_in_cart',
    		  'values'    => array(
    			  array(
    				  'value'     => 0,
    				  'label'     => Mage::helper('magebird_popup')->__("Skip this condition"),
    			  ),
    			  array(
    				  'value'     => 1,
    				  'label'     => Mage::helper('magebird_popup')->__('Show only if there is any product in cart'),
    			  ),    
    			  array(
    				  'value'     => 2,
    				  'label'     => Mage::helper('magebird_popup')->__('Show only if product cart is empty'),
    			  )
           ),                               
    		));
        
        $afterElementHtml = "<p class='nm'><small>".Mage::helper('magebird_popup')->__('Show if total cart qty (number of products in cart) is more than x. Leave empty or 0 to skip this condition.')."</small></p>";
        $fieldset->addField('cart_qty_min', 'text', array(
    		  'label'     => Mage::helper('magebird_popup')->__('Cart qty more than'),
    		  'name'      => 'cart_qty_min',
          'after_element_html' => $afterElementHtml 
    		));
        
        $fieldset->addField('cart_qty_max', 'text', array(
    		  'label'     => Mage::helper('magebird_popup')->__('Cart qty less than'),
    		  'name'      => 'cart_qty_max'
    		));                   
        
        $afterElementHtml = "<p class='nm'><small>".Mage::helper('magebird_popup')->__("Leave empty or write 0 if you don't want to apply this condition.")."</small></p>";
        $cartSubtotalMin = $fieldset->addField('cart_subtotal_min', 'text', array(
    		  'label'     => Mage::helper('magebird_popup')->__('Cart subtotal less than'),
    		  'name'      => 'cart_subtotal_min',
          'after_element_html' => $afterElementHtml 
    		));    
        
        $afterElementHtml = "<p class='nm'><small>".Mage::helper('magebird_popup')->__("Leave empty or write 0 if you don't want to apply this condition.")."</small></p>";
        $cartSubtotalMax = $fieldset->addField('cart_subtotal_max', 'text', array(
    		  'label'     => Mage::helper('magebird_popup')->__('Cart subtotal more than'),
    		  'name'      => 'cart_subtotal_max',
          'after_element_html' => $afterElementHtml 
    		));  
        
        $afterElementHtml = "<p class='nm'><small>".Mage::helper('magebird_popup')->__('Show popup only if there is at least 1 product with attribute value that matches your value (e.g. if color is green, ...). See instructions <a target="_blank" href="http://www.magebird.com/magento-extensions/popup.html?tab=faq#productAttributeCond">here</a>. Leave empty to skip this condition.')."</small></p>";
        $cartSubtotalMin = $fieldset->addField('product_cart_attr', 'text', array(
    		  'label'     => Mage::helper('magebird_popup')->__('Product attribute in cart'),
    		  'name'      => 'product_cart_attr',
          'after_element_html' => $afterElementHtml 
    		));   
        
        $afterElementHtml = "<p class='nm'><small>".Mage::helper('magebird_popup')->__('Show popup only if there is NO product with attribute value in cart (e.g. if NO products with green color in cart). See instructions <a target="_blank" href="http://www.magebird.com/magento-extensions/popup.html?tab=faq#productAttributeCond">here</a>. Leave empty to skip this condition.')."</small></p>";
        $cartSubtotalMin = $fieldset->addField('not_product_cart_attr', 'text', array(
    		  'label'     => Mage::helper('magebird_popup')->__('Not product attribute in cart'),
    		  'name'      => 'not_product_cart_attr',
          'after_element_html' => $afterElementHtml 
    		));    
        
        $afterElementHtml = "<p class='nm'><small>".Mage::helper('magebird_popup')->__('Show popup only if there is any product in cart that belongs to selected categories. Write categories ids separated with comma (e.g.:1,12,31). Leave empty to skip this condition.')."</small></p>";
        $cartSubtotalMin = $fieldset->addField('cart_product_categories', 'text', array(
    		  'label'     => Mage::helper('magebird_popup')->__('Product categories in cart'),
    		  'name'      => 'cart_product_categories',
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
    
}
