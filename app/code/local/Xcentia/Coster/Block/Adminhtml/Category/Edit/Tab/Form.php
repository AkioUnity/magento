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
 * Category edit form tab
 *
 * @category    Xcentia
 * @package     Xcentia_Coster
 * @author      Ultimate Module Creator
 */
class Xcentia_Coster_Block_Adminhtml_Category_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return Xcentia_Coster_Block_Adminhtml_Category_Edit_Tab_Form
     * @author Ultimate Module Creator
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('category_');
        $form->setFieldNameSuffix('category');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'category_form',
            array('legend' => Mage::helper('xcentia_coster')->__('Category'))
        );

        $fieldset->addField(
            'piececode',
            'text',
            array(
                'label' => Mage::helper('xcentia_coster')->__('Piece Code'),
                'name'  => 'piececode',
                'required'  => true,
                'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'subcategorycode',
            'text',
            array(
                'label' => Mage::helper('xcentia_coster')->__('Sub Category Code'),
                'name'  => 'subcategorycode',
                'required'  => true,
                'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'categorycode',
            'text',
            array(
                'label' => Mage::helper('xcentia_coster')->__('Category Code'),
                'name'  => 'categorycode',
                'required'  => true,
                'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'category_id',
            'text',
            array(
                'label' => Mage::helper('xcentia_coster')->__('Category Id'),
                'name'  => 'category_id',
                'required'  => true,
                'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'subcategory_id',
            'text',
            array(
                'label' => Mage::helper('xcentia_coster')->__('Sub Category Id'),
                'name'  => 'subcategory_id',
                'required'  => true,
                'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'peice_id',
            'text',
            array(
                'label' => Mage::helper('xcentia_coster')->__('Peice Category Id'),
                'name'  => 'peice_id',
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
        $formValues = Mage::registry('current_category')->getDefaultValues();
        if (!is_array($formValues)) {
            $formValues = array();
        }
        if (Mage::getSingleton('adminhtml/session')->getCategoryData()) {
            $formValues = array_merge($formValues, Mage::getSingleton('adminhtml/session')->getCategoryData());
            Mage::getSingleton('adminhtml/session')->setCategoryData(null);
        } elseif (Mage::registry('current_category')) {
            $formValues = array_merge($formValues, Mage::registry('current_category')->getData());
        }
        $form->setValues($formValues);
        return parent::_prepareForm();
    }
}
