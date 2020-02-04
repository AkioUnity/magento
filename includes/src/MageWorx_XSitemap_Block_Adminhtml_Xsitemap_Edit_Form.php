<?php
/**
 * MageWorx
 * MageWorx XSitemap Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_XSitemap
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */


class MageWorx_XSitemap_Block_Adminhtml_Xsitemap_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('sitemap_form');
        $this->setTitle(Mage::helper('xsitemap')->__('Sitemap Information'));
    }

    protected function _prepareForm()
    {
        $model = Mage::registry('sitemap_sitemap');

        $form = new Varien_Data_Form(array(
            'id'     => 'edit_form',
            'action' => $this->getUrl('*/*/save'),
            'method' => 'post'
        ));

        $fieldset = $form->addFieldset('add_sitemap_form', array('legend' => Mage::helper('sitemap')->__('Sitemap')));

        if ($model->getId()) {
            $fieldset->addField('sitemap_id', 'hidden', array(
                'name' => 'sitemap_id',
            ));
        }

        $fieldset->addField('sitemap_filename', 'text',
            array(
            'label'    => Mage::helper('xsitemap')->__('Filename'),
            'name'     => 'sitemap_filename',
            'required' => true,
            'note'     => Mage::helper('xsitemap')->__('example: seo_sitemap.xml'),
            'value'    => $model->getSitemapFilename()
        ));

        $fieldset->addField('sitemap_path', 'text',
            array(
            'label'    => Mage::helper('xsitemap')->__('Path'),
            'name'     => 'sitemap_path',
            'required' => true,
            'note'     => Mage::helper('xsitemap')->__('example: "sitemaps/" or "/" for base path (path must be writeable)'),
            'value'    => $model->getSitemapPath()
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('store_id', 'select',
                array(
                'label'    => Mage::helper('xsitemap')->__('Store View'),
                'title'    => Mage::helper('xsitemap')->__('Store View'),
                'name'     => 'store_id',
                'required' => true,
                'value'    => $model->getStoreId(),
                'values'   => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm()
            ));
        }
        else {
            $fieldset->addField('store_id', 'hidden',
                array(
                'name'  => 'store_id',
                'value' => Mage::app()->getStore(true)->getId()
            ));
            $model->setStoreId(Mage::app()->getStore(true)->getId());
        }

        $fieldset->addField('generate', 'hidden',
            array(
            'name'  => 'generate',
            'value' => ''
        ));

        $form->setValues($model->getData());

        $form->setUseContainer(true);

        $this->setForm($form);

        return parent::_prepareForm();
    }

}
