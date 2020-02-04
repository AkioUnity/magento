<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Storepickup
 * @module      Storepickup
 * @author      Magestore Developer
 *
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */

/**
 * Class Magestore_Storepickup_Block_Adminhtml_Tag_Edit_Tab_Form
 */
class Magestore_Storepickup_Block_Adminhtml_Tag_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {
    /**
     * @return mixed
     */
    protected function _prepareForm() {
		$form = new Varien_Data_Form();
		$this->setForm($form);

		if (Mage::getSingleton('adminhtml/session')->getStorepickupData()) {
			$data = Mage::getSingleton('adminhtml/session')->getStorepickupData();
			Mage::getSingleton('adminhtml/session')->setStorepickupData(null);
		} elseif (Mage::registry('tag_data')) {
			$data = Mage::registry('tag_data')->getData();
		}

		$fieldset = $form->addFieldset('tag_form', array('legend' => Mage::helper('storepickup')->__('Tag Information')));
		$fieldset->addField('title', 'text', array(
			'label' => Mage::helper('storepickup')->__('Name'),
			'class' => 'required-entry',
			'required' => true,
			'name' => 'title',
		));

//		$fieldset->addField('icon', 'image', array(
		//			'label'		=> Mage::helper('storepickup')->__('Icon'),
		//			'required'	=> false,
		//			'name'		=> 'icon',
		//		));

		if (isset($data['icon']) && $data['icon']) {
			$data['img_icon'] = 'storepickup/images/icon/' . $data['icon'];
		}
		$fieldset->addField('img_icon', 'image', array(
			'label' => Mage::helper('storepickup')->__('Tag Icon'),
			'note' => 'Shown on Google Map<br/>Recommended size: 600x400 px. Supported format: jpeg, png, gif',
			'name' => 'img_icon',
		));

		$fieldset->addField('status', 'select', array(
			'label' => Mage::helper('storepickup')->__('Status'),
			'name' => 'status',
			'values' => array(
				array(
					'value' => 1,
					'label' => Mage::helper('storepickup')->__('Enabled'),
				),

				array(
					'value' => 2,
					'label' => Mage::helper('storepickup')->__('Disabled'),
				),
			),
		));
//
		//		$fieldset->addField('content', 'editor', array(
		//			'name'		=> 'content',
		//			'label'		=> Mage::helper('storepickup')->__('Content'),
		//			'title'		=> Mage::helper('storepickup')->__('Content'),
		//			'style'		=> 'width:700px; height:500px;',
		//			'wysiwyg'	=> false,
		//			'required'	=> true,
		//		));

		$form->setValues($data);
		return parent::_prepareForm();
	}
}