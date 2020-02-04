<?php

/**
 * Magebird.com
 *
 * @category   Magebird
 * @package    Magebird_Popup
 * @copyright  Copyright (c) 2017 Magebird (http://www.Magebird.com)
 * @license    http://www.magebird.com/licence
 * Any form of ditribution, sell, transfer forbidden see licence above
 * Code has been obfuscated to prevent licence violations  
 */
class Magebird_Popup_Block_Widget_Wysiwyg extends Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element {

    public function render(Varien_Data_Form_Element_Abstract $element) {
        $element->setValue(urldecode($element->getValue()));

        $element->setType('textarea');
        return parent::render($element);
    }

}
