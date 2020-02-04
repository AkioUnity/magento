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
 * Style admin edit tabs
 *
 * @category    Xcentia
 * @package     Xcentia_Coster
 * @author      Ultimate Module Creator
 */
class Xcentia_Coster_Block_Adminhtml_Style_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
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
        $this->setId('style_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('xcentia_coster')->__('Style'));
    }

    /**
     * before render html
     *
     * @access protected
     * @return Xcentia_Coster_Block_Adminhtml_Style_Edit_Tabs
     * @author Ultimate Module Creator
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_style',
            array(
                'label'   => Mage::helper('xcentia_coster')->__('Style'),
                'title'   => Mage::helper('xcentia_coster')->__('Style'),
                'content' => $this->getLayout()->createBlock(
                    'xcentia_coster/adminhtml_style_edit_tab_form'
                )
                ->toHtml(),
            )
        );
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve style entity
     *
     * @access public
     * @return Xcentia_Coster_Model_Style
     * @author Ultimate Module Creator
     */
    public function getStyle()
    {
        return Mage::registry('current_style');
    }
}
