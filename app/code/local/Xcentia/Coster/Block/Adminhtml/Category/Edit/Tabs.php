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
 * Category admin edit tabs
 *
 * @category    Xcentia
 * @package     Xcentia_Coster
 * @author      Ultimate Module Creator
 */
class Xcentia_Coster_Block_Adminhtml_Category_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Initialize Tabs
     *
     * @access public
     * @author Ultimate Module Creator
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('category_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('xcentia_coster')->__('Category'));
    }

    /**
     * before render html
     *
     * @access protected
     * @return Xcentia_Coster_Block_Adminhtml_Category_Edit_Tabs
     * @author Ultimate Module Creator
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_category',
            array(
                'label'   => Mage::helper('xcentia_coster')->__('Category'),
                'title'   => Mage::helper('xcentia_coster')->__('Category'),
                'content' => $this->getLayout()->createBlock(
                    'xcentia_coster/adminhtml_category_edit_tab_form'
                )
                ->toHtml(),
            )
        );
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve category entity
     *
     * @access public
     * @return Xcentia_Coster_Model_Category
     * @author Ultimate Module Creator
     */
    public function getCategory()
    {
        return Mage::registry('current_category');
    }
}
