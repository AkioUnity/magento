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
class Magebird_Popup_Block_Adminhtml_Widget_Successmsg extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct($arguments=array())
    {
        parent::__construct($arguments);
    } 

    public function prepareElementHtml(Varien_Data_Form_Element_Abstract $element)
    {   
        $element->setValue(urldecode($element->getValue()));
        return $element;
    }





}
