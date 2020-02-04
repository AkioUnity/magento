<?php
class Biztech_Fedex_Block_Adminhtml_Pickup_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
		protected function _prepareForm()
		{

				$form = new Varien_Data_Form();
				$this->setForm($form);

					$fieldset = $form->addFieldset("fedex_form_contact", array("legend"=>Mage::helper("fedex")->__("Contact information")));

						$fieldset->addField("person_name", "text", array(
							"label" => Mage::helper("fedex")->__("Person Name"),					
							// "class" => "required-entry",	
							"required" => false,
							"name" => "person_name",
						));
						$fieldset->addField("company_name", "text", array(
							"label" => Mage::helper("fedex")->__("Company Name"),					
							// "class" => "required-entry",	
							"required" => false,
							"name" => "company_name",
						));
						$fieldset->addField("phone_no", "text", array(
							"label" => Mage::helper("fedex")->__("Phone No."),					
							// "class" => "required-entry",	
							"required" => false,
							"name" => "phone_no",
						));

					$fieldset = $form->addFieldset("fedex_form_address", array("legend"=>Mage::helper("fedex")->__("Address information")));

						$fieldset->addField("pickup_address", "text", array(
							"label" => Mage::helper("fedex")->__("Address"),					
							// "class" => "required-entry",	
							"required" => false,
							"name" => "pickup_address",
						));

						$fieldset->addField("pickup_city", "text", array(
							"label" => Mage::helper("fedex")->__("City"),					
							// "class" => "required-entry",	
							"required" => false,
							"name" => "pickup_city",
						));

						$fieldset->addField("pickup_country", "select", array(
							"label" => Mage::helper("fedex")->__("Country"),					
							// "class" => "required-entry",	
							"required" => false,
							"name" => "pickup_country",
							// "onchange" => "checkstate()",
							"values" => Mage::getModel('fedex/pickup')->getCountries(),
							// "renderer"  => "fedex/adminhtml_pickup_edit_renderer_countries",
						));

						$fieldset->addType('customtype', 'Biztech_Fedex_Block_Adminhtml_Pickup_Renderer_State');     
					     $fieldset->addField('rating', 'customtype', array(
					        'name'      => 'rating',
					        'label'     => Mage::helper('fedex')->__('State'),
					    ));

						$fieldset->addField("pickup_postcode", "text", array(
							"label" => Mage::helper("fedex")->__("Postcode"),					
							// "class" => "required-entry",	
							"required" => false,
							"name" => "pickup_postcode",
						));

						
						$fieldset->addField("package_location", "select", array(
							"label" => Mage::helper("fedex")->__("Package Location"),					
							// "class" => "required-entry",	
							"required" => false,
							"name" => "package_location",
							'values'    => array(
		                        array(
		                            'value'     => 'FRONT',
		                            'label'     => Mage::helper('fedex')->__('Front'),
		                        ),
								array(
		                            'value'     => 'REAR',
		                            'label'     => Mage::helper('fedex')->__('Rear'),
		                        ),
		                        array(
		                            'value'     => 'SIDE',
		                            'label'     => Mage::helper('fedex')->__('Side'),
		                        ),
								array(
		                            'value'     => 'NONE',
		                            'label'     => Mage::helper('fedex')->__('None'),
		                        )
		                    ),

						));
						$fieldset->addField("building_partcode", "select", array(
							"label" => Mage::helper("fedex")->__("Bilding Partcode"),					
							// "class" => "required-entry",	
							"required" => false,
							"name" => "building_partcode",
							'values'    => array(
		                        array(
		                            'value'     => 'APARTMENT',
		                            'label'     => Mage::helper('fedex')->__('APARTMENT'),
		                        ),
								array(
		                            'value'     => 'BUILDING',
		                            'label'     => Mage::helper('fedex')->__('BUILDING'),
		                        ),
		                        array(
		                            'value'     => 'DEPARTMENT',
		                            'label'     => Mage::helper('fedex')->__('DEPARTMENT'),
		                        ),
								array(
		                            'value'     => 'SUITE',
		                            'label'     => Mage::helper('fedex')->__('SUITE'),
		                        ),
		                        array(
		                            'value'     => 'FLOOR',
		                            'label'     => Mage::helper('fedex')->__('FLOOR'),
		                        ),
								array(
		                            'value'     => 'ROOM',
		                            'label'     => Mage::helper('fedex')->__('ROOM'),
		                        )
		                    ),

						));
						$fieldset->addField("building_part_description", "text", array(
							"label" => Mage::helper("fedex")->__("Building Part Description"),
							// "class" => "required-entry",	
							"required" => false,
							"name" => "building_part_description",
							
						));
						



						$fieldset->addField("ready_timestamp", "text", array(
							"label" => Mage::helper("fedex")->__("Ready Timestamp"),
							// "class" => "required-entry",	
							"required" => false,
							"name" => "ready_timestamp",
							
						));


						$fieldset->addField("company_closetime", "text", array(
							"label" => Mage::helper("fedex")->__("Company Close Time"),
							// "class" => "required-entry",	
							"required" => false,
							"name" => "company_closetime",
							
						));
						
					$fieldset = $form->addFieldset("shipment_information", array("legend"=>Mage::helper("fedex")->__("Package Information")));

						$fieldset->addField("package_count", "text", array(
							"label" => Mage::helper("fedex")->__("Package Count"),					
							"class" => "required-entry",	
							"required" => true,
							"name" => "package_count",
						));

						$fieldset->addField("total_weight_unit", "select", array(
							"label" => Mage::helper("fedex")->__("Total Weight Unit"),					
							// "class" => "required-entry",	
							"required" => false,
							"name" => "total_weight_unit",
							'values'    => array(
		                        array(
		                            'value'     => 'LB',
		                            'label'     => Mage::helper('fedex')->__('LB'),
		                        ),
								array(
		                            'value'     => 'KG',
		                            'label'     => Mage::helper('fedex')->__('KG'),
		                        ),
		                    ),
						));

						$fieldset->addField("total_weight_value", "text", array(
							"label" => Mage::helper("fedex")->__("Total Weight Value"),					
							"class" => "required-entry",	
							"required" => true,
							"name" => "total_weight_value",
						));

						$fieldset->addField("courier_code", "select", array(
							"label" => Mage::helper("fedex")->__("Courier Code"),					
							// "class" => "required-entry",	
							"required" => false,
							"name" => "courier_code",
							'values'    => array(
		                        array(
		                            'value'     => 'FDXE',
		                            'label'     => Mage::helper('fedex')->__('Fedex Express'),
		                        ),
								array(
		                            'value'     => 'FDXG',
		                            'label'     => Mage::helper('fedex')->__('Fedex Ground'),
		                        ),
		                    ),
						));

						$fieldset->addField("courier_remarks", "text", array(
							"label" => Mage::helper("fedex")->__("Courier Remarks"),					
							// "class" => "required-entry",	
							"required" => false,
							"name" => "courier_remarks",
						));


				if (Mage::getSingleton("adminhtml/session")->getPickupData())
				{
					$form->setValues(Mage::getSingleton("adminhtml/session")->getPickupData());
					Mage::getSingleton("adminhtml/session")->setPickupData(null);
				} 
				elseif(Mage::registry("pickup_data")) {
				    $form->setValues(Mage::registry("pickup_data")->getData());
				}
				return parent::_prepareForm();
		}
}
