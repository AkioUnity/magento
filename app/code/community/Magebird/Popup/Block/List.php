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
class Magebird_Popup_Block_List extends Magebird_Popup_Block_Widget_Abstract {

    public function getScript($template) {
        $html = "<script type=\"text/javascript\">\n";
        $html .= "if(!jQuery(\"link[href*='/css/magebird_popup/widget/newsletter/" . $template . ".css']\").length){\n";
        $html .= "jQuery('head').append('<link rel=\"stylesheet\" href=\"" . $this->getSkinUrl("css/magebird_popup/widget/newsletter/" . $template . ".css?v=1.4.8") . "\" type=\"text/css\" />');";
        $html .= "}\n";
        $html .= "newslPopup['" . $this->getWidgetId() . "'] = {};\n";
        $html .= "newslPopup['" . $this->getWidgetId() . "'].successMsg = decodeURIComponent(('" . urlencode(Mage::helper('cms')->getBlockTemplateProcessor()->filter(urldecode($this->getData('success_msg')))) . "'+'').replace(/\+/g, '%20'));";
        $onSuccess = $this->getData('on_success') ? $this->getData('on_success') : 1;
        $html .= "newslPopup['" . $this->getWidgetId() . "'].successAction = '" . $onSuccess . "';";
        $html .= "newslPopup['" . $this->getWidgetId() . "'].successUrl = '" . $this->getData('success_url') . "';";
        $html .= "newslPopup['" . $this->getWidgetId() . "'].errorText = '" . $this->__('Write a valid Email address') . "';";
        $html .= "newslPopup['" . $this->getWidgetId() . "'].checkboxErrorText = '" . $this->__('Please check the checkbox') . "';";
        $delay = $this->getDelay() * 1000;
        $html .= "newslPopup['" . $this->getWidgetId() . "'].actionDelay = '" . $delay . "';";
        $html .= "</script>\n";
        return $html;
    }

}
