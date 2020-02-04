<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Table
 */
class Amasty_Table_Block_Adminhtml_Method_Edit_Tab_Labels extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        /* @var $hlp Amasty_Table_Helper_Data */
        $helper = Mage::helper('amtable');

        $form = new Varien_Data_Form();
        $formModel = $this->getFormModel();

        $fieldset = $form->addFieldset('store_labels_fieldset', array(
            'legend'       => Mage::helper('salesrule')->__('Store View Specific Labels'),
            'table_class'  => 'form-list stores-tree',
        ));

        foreach (Mage::app()->getWebsites() as $website) {
            $fieldset->addField("w_{$website->getId()}_label", 'note', array(
                'label'    => $website->getName(),
                'fieldset_html_class' => 'website',
            ));
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                if (count($stores) == 0) {
                    continue;
                }
                $fieldset->addField("sg_{$group->getId()}_label", 'note', array(
                    'label'    => $group->getName(),
                    'fieldset_html_class' => 'store-group',
                ));
                foreach ($stores as $store) {
                    $fieldset->addField("s_{$store->getId()}_label", 'note', array(
                        'label' => $this->escapeHtml($store->getName()) . ':',
                    ));
                    $fieldset->addField("label-{$store->getId()}", 'text', array(
                        'name'      => 'label_['.$store->getId().']',
                        'required'  => false,
                        'label'     => $helper->__('Method Label'),
                        'value'     => $formModel->getLabel($store->getId(), false),
                        'fieldset_html_class' => 'store',
                    ));
                    $fieldset->addField("comment-{$store->getId()}", 'textarea', array(
                        'name'      => 'comment_['.$store->getId().']',
                        'required'  => false,
                        'label'     => $helper->__('Comment'),
                        'style'     => 'height:15px;',
                        'value'     => $formModel->getCommentLabel($store->getId(), false),
                        'fieldset_html_class' => 'store',
                    ));
                }
            }
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * @return Amasty_Table_Model_Method
     */
    protected function getFormModel()
    {
        return Mage::registry('amtable_method');
    }
}