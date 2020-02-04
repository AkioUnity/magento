<?php
class Biztech_Fedex_Block_Adminhtml_Etdtype_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{		
		


		protected function _prepareForm()
		{

				$form = new Varien_Data_Form();
				$this->setForm($form);

					$fieldset = $form->addFieldset("fedex_form_contact", array("legend"=>Mage::helper("fedex")->__("Contact information")));

						$fieldset->addField("name", "select", array(
							"label" => Mage::helper("fedex")->__("Name"),					
							"required" => true,
							"name" => "name",
							"class" => "required-entry",	
							'values' => Mage::getModel('fedex/etdtype')->getEtdtypes(),


						));
						$fieldset->addField("title", "text", array(
							"label" => Mage::helper("fedex")->__("Title"),					
							"class" => "required-entry",	
							"required" => true,
							"name" => "title",
						));

						$fieldset->addField("content", "editor", array(
							"label" => Mage::helper("fedex")->__("Content"),					
							"class" => "required-entry",	
							"required" => true,
							"name" => "content",
							/*'wysiwyg'   => true,
							'config'    => Mage::getSingleton('cms/wysiwyg_config')->getConfig(),*/

						));

					


				if (Mage::getSingleton("adminhtml/session")->getEtdtypeData())
				{
					$form->setValues(Mage::getSingleton("adminhtml/session")->getEtdtypeData());
					Mage::getSingleton("adminhtml/session")->setEtdtypeData(null);
				} 
				elseif(Mage::registry("etdtype_data")) {
				    $form->setValues(Mage::registry("etdtype_data")->getData());
				}
				return parent::_prepareForm();
		}
}
