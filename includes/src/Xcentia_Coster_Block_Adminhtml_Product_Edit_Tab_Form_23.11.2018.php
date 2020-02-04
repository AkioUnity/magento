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
 * Product edit form tab
 *
 * @category    Xcentia
 * @package     Xcentia_Coster
 * @author      Ultimate Module Creator
 */
class Xcentia_Coster_Block_Adminhtml_Product_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return Xcentia_Coster_Block_Adminhtml_Product_Edit_Tab_Form
     * @author Ultimate Module Creator
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('product_');
        $form->setFieldNameSuffix('product');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'product_form',
            array('legend' => Mage::helper('xcentia_coster')->__('Product'))
        );

        $fieldset->addField(
            'sku',
            'text',
            array(
                'label' => Mage::helper('xcentia_coster')->__('SKU'),
                'name'  => 'sku',
                'required'  => true,
                'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'content',
            'textarea',
            array(
                'label' => Mage::helper('xcentia_coster')->__('Content'),
                'name'  => 'content',
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
        $formValues = Mage::registry('current_product')->getDefaultValues();
        if (!is_array($formValues)) {
            $formValues = array();
        }
        if (Mage::getSingleton('adminhtml/session')->getProductData()) {
            $formValues = array_merge($formValues, Mage::getSingleton('adminhtml/session')->getProductData());
            Mage::getSingleton('adminhtml/session')->setProductData(null);
        } elseif (Mage::registry('current_product')) {
            $formValues = array_merge($formValues, Mage::registry('current_product')->getData());
        }
        $form->setValues($formValues);
        return parent::_prepareForm();
    }
}
