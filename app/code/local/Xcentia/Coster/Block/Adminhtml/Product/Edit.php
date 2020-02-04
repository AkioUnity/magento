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
 * Product admin edit form
 *
 * @category    Xcentia
 * @package     Xcentia_Coster
 * @author      Ultimate Module Creator
 */
class Xcentia_Coster_Block_Adminhtml_Product_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * constructor
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function __construct()
    {
        parent::__construct();
        $this->_blockGroup = 'xcentia_coster';
        $this->_controller = 'adminhtml_product';
        $this->_updateButton(
            'save',
            'label',
            Mage::helper('xcentia_coster')->__('Save Product')
        );
        $this->_updateButton(
            'delete',
            'label',
            Mage::helper('xcentia_coster')->__('Delete Product')
        );
        $this->_addButton(
            'saveandcontinue',
            array(
                'label'   => Mage::helper('xcentia_coster')->__('Save And Continue Edit'),
                'onclick' => 'saveAndContinueEdit()',
                'class'   => 'save',
            ),
            -100
        );
        $this->_formScripts[] = "
            function saveAndContinueEdit() {
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    /**
     * get the edit form header
     *
     * @access public
     * @return string
     * @author Ultimate Module Creator
     */
    public function getHeaderText()
    {
        if (Mage::registry('current_product') && Mage::registry('current_product')->getId()) {
            return Mage::helper('xcentia_coster')->__(
                "Edit Product '%s'",
                $this->escapeHtml(Mage::registry('current_product')->getSku())
            );
        } else {
            return Mage::helper('xcentia_coster')->__('Add Product');
        }
    }
}
