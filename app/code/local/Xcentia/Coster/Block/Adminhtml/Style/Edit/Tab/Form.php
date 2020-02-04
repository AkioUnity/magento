<?php
/**
 * Xcentia_Coster extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       Xcentia
 * @package        Xcentia_Coster
 * @copyright      Copyright (c) 2017
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Style edit form tab
 *
 * @category    Xcentia
 * @package     Xcentia_Coster
 * @author      Ultimate Module Creator
 */
class Xcentia_Coster_Block_Adminhtml_Style_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return Xcentia_Coster_Block_Adminhtml_Style_Edit_Tab_Form
     * @author Ultimate Module Creator
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('style_');
        $form->setFieldNameSuffix('style');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'style_form',
            array('legend' => Mage::helper('xcentia_coster')->__('Style'))
        );

        $fieldset->addField(
            'style_code',
            'text',
            array(
                'label' => Mage::helper('xcentia_coster')->__('Style Code'),
                'name'  => 'style_code',
                'required'  => true,
                'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'style_name',
            'text',
            array(
                'label' => Mage::helper('xcentia_coster')->__('Style Name'),
                'name'  => 'style_name',
                'required'  => true,
                'class' => 'required-entry',

           )
        );
        $fieldset->addField(
            'status',
            'select',
            array(
                'label'  => Mage::helper('xcentia_coster')->__('Status'),
                'name'   => 'status',
                'values' => array(
                    array(
                        'value' => 1,
                        'label' => Mage::helper('xcentia_coster')->__('Enabled'),
                    ),
                    array(
                        'value' => 0,
                        'label' => Mage::helper('xcentia_coster')->__('Disabled'),
                    ),
                ),
            )
        );
        $formValues = Mage::registry('current_style')->getDefaultValues();
        if (!is_array($formValues)) {
            $formValues = array();
        }
        if (Mage::getSingleton('adminhtml/session')->getStyleData()) {
            $formValues = array_merge($formValues, Mage::getSingleton('adminhtml/session')->getStyleData());
            Mage::getSingleton('adminhtml/session')->setStyleData(null);
        } elseif (Mage::registry('current_style')) {
            $formValues = array_merge($formValues, Mage::registry('current_style')->getData());
        }
        $form->setValues($formValues);
        return parent::_prepareForm();
    }
}
