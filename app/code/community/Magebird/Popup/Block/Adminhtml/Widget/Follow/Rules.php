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
class Magebird_Popup_Block_Adminhtml_Widget_Follow_Rules extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct($arguments=array())
    {
        parent::__construct($arguments);
    } 

    public function prepareElementHtml(Varien_Data_Form_Element_Abstract $element)
    {   
        $rules = Mage::getModel('salesrule/rule')->getCollection();
        $rules->addFieldToFilter('coupon_type',2);
        $rules->addFieldToFilter('use_auto_generation',1);          
        if(count($rules)==0){     
          $html = "<p class='note'><span style='color:red'>ERROR:</span><span>".Mage::helper('magebird_popup')->__('Only rules with field &quot;<a target="_blank" href="%s">Use Auto Generation</a>&quot; checked on can be used. Also make sure you set &quot;Coupon&quot; field to &quot;Specific coupon&quot; value.',"http://www.magebird.com/magento-extensions/popup.html?tab=faq#dynamicCoupon")." <a target='_blank' href='http://www.magebird.com/magento-extensions/popup.html?tab=faq#dynamicCoupon'>".Mage::helper('magebird_popup')->__("See instructions here")."</a></span></p>";        
        }else{
          $html = '<p class="note"><span>'.Mage::helper('magebird_popup')->__('Choose your shoping cart rule you want to be used to generate coupons. Only rules with field &quot;<a target="_blank" href="%s">Use Auto Generation</a>&quot; checked on can be used. If you want to use static coupon code change &quot;Coupon type&quot; above.','http://www.magebird.com/magento-extensions/popup.html?tab=faq#dynamicCoupon').'</span></p>';         
        }        
        $element->setData('after_element_html', $html);
        return $element;
    }

}
