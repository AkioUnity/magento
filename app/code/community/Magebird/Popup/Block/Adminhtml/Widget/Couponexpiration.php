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
class Magebird_Popup_Block_Adminhtml_Widget_Couponexpiration extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct($arguments=array())
    {
        parent::__construct($arguments);
    } 

    public function prepareElementHtml(Varien_Data_Form_Element_Abstract $element)
    {   
        $html = "<input type='checkbox' class='inheritTimer' /> Iherit from countdown timer (<a target='_blank' href='http://www.magebird.com/magento-extensions/popup.html?tab=faq#timelimitedCoupons'>What is that?</a>)
        <script>
        var element = jQuery(\"#widget_options input[name='parameters\[coupon_expiration\]']\");
        element.attr('style', 'width: 70px !important');
        jQuery(\".inheritTimer\").click(function(){
          if(element.attr('readonly')){
            element.attr('readonly', false);      
            element.val('');        
          }else{
            element.val('inherit');
            element.attr('readonly', true);      
          }
        });
        </script>";    
        $element->setData('after_element_html', $html);
        return $element;
    }
}
