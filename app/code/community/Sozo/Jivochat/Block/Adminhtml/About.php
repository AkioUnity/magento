<?php
/**
 * SOZO Design
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category    SOZO Design
 * @package     Sozo_Purechat
 * @copyright   Copyright (c) 2018 SOZO Design (http://www.sozodesign.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */

class Sozo_Jivochat_Block_Adminhtml_About
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element = null;
        $version = Mage::helper('sozo_jivochat')->getExtensionVersion();
        $logopath = 'https://sozodesign.co.uk/images/magento/sozo_magento_plugin_about.png';
        $html = <<<HTML
<div style="background: url('$logopath') no-repeat scroll 15px 15px #e7efef; border: 1px solid #ccc; min-height: 97px; margin: 5px 0; padding: 15px 15px 15px 180px;">
    <p>
        <strong>SOZO JivoChat Plugin v$version</strong><br />
        Add the JivoChat widget to your pages.
        To use this plugin you must have a JivoChat account.<br />
        <a href="http://bit.ly/magento1-jivochat-signup" target="_blank" title="Go to JivoChat Website">Sign Up here to get an account</a>
    </p>
    <p>
        Control the look of your Chat window through your <a href="https://admin.jivosite.com/widgets" target="_blank">JivoChat dashboard</a>.
    </p>
    <p>
        Website:
        <a href="https://sozodesign.co.uk" target="_blank">sozodesign.co.uk</a><br />
        Like, share and follow us on
        <a href="https://twitter.com/sozodesign" target="_blank">Twitter</a>,
        <a href="https://plus.google.com/103411336885753828600/" target="_blank">Google+</a>,
        <a href="https://www.youtube.com/user/SozoDesignLtd" target="_blank">YouTube</a>,
        <a href="https://vimeo.com/sozodesign" target="_blank">Vimeo</a>, and
        <a href="http://www.linkedin.com/pub/shaun-uthup/6/785/884" target="_blank">LinkedIn</a>.
    </p>
</div>
HTML;
        return $html;
    }
}
