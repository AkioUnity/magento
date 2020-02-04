<?php

class MagicToolbox_MagicZoom_Block_Adminhtml_Settings_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {

        $blockId = preg_replace('/^magiczoom_|_settings_block$/is', '', $this->getNameInLayout());

        $helper = Mage::helper('magiczoom/params');

        $tool = Mage::registry('magiczoom_core_class');
        //$optionsIds = Mage::registry('magiczoom_options_ids');

        if ($tool === null) {

            $coreClassPath = BP . str_replace('/', DS, '/app/code/local/MagicToolbox/MagicZoom/core/magiczoom.module.core.class.php');
            require_once $coreClassPath;
            $tool = new MagicZoomModuleCoreClass();

            /*
            foreach ($helper->getDefaultValues() as $block => $params) {
                foreach ($params as $id => $value) {
                    $tool->params->setValue($id, $value, $block);
                }
            }
            */

            //$optionsIds = array();
            $model = Mage::registry('magiczoom_model_data');
            $data = $model->getData();
            if (!empty($data['value'])) {
                $settings = unserialize($data['value']);
                if (isset($settings['desktop'])) {
                    foreach ($settings['desktop'] as $profile => $params) {
                        //$optionsIds[$profile] = array();
                        foreach ($params  as $id => $value) {
                            //$optionsIds[$profile][$id] = true;
                            $tool->params->setValue($id, $value, $profile);
                        }
                    }
                }
                if (isset($settings['mobile'])) {
                    foreach ($settings['mobile'] as $profile => $params) {
                        //$optionsIds[$profile] = array();
                        foreach ($params  as $id => $value) {
                            //$optionsIds[$profile][$id] = true;
                            $tool->params->setMobileValue($id, $value, $profile);
                        }
                    }
                }
            }

            Mage::register('magiczoom_core_class', $tool);
            //Mage::register('magiczoom_options_ids', $optionsIds);

        }

        $form = new Varien_Data_Form();
        //$form->setHtmlIdPrefix('_general');
        $this->setForm($form);

        $elementRenderer = $this->getLayout()->createBlock('magiczoom/adminhtml_settings_edit_tab_form_renderer_fieldset_element');
        $fieldsetRenderer = $this->getLayout()->createBlock('magiczoom/adminhtml_settings_edit_tab_form_renderer_fieldset');

        $gId = 0;
        foreach ($helper->getParamsMap($blockId) as $group => $ids) {
            $fieldset = $form->addFieldset(
                $blockId.'_group_fieldset_'.($gId++),
                array(
                    'legend' => Mage::helper('magiczoom')->__($group),
                    'class' => 'magiczoom-fieldset'
                )
            );
            $fieldset->addType('magiczoom_radios', 'MagicToolbox_MagicZoom_Block_Adminhtml_Settings_Edit_Tab_Form_Element_Radios');
            $fieldset->setRenderer($fieldsetRenderer);
            foreach ($ids as $id) {
                $config = array(
                    'label'     => Mage::helper('magiczoom')->__($tool->params->getLabel($id, $blockId)),
                    'name'      => 'magiczoom[desktop]['.$blockId.']['.$id.']',
                    'note'      => '',
                    'value'     => $tool->params->getValue($id, $blockId),
                    'class'     => 'magictoolbox-option',//'required-entry'
                    //'required'  => true,
                );
                $description = $tool->params->getDescription($id, $blockId);
                if ($description) {
                    $config['note'] = $description;
                }
                $type = $tool->params->getType($id, $blockId);
                $values = $tool->params->getValues($id, $blockId);
                if ($type != 'array' && $tool->params->valuesExists($id, $blockId, false)) {
                    if (!empty($config['note'])) $config['note'] .= "<br />";
                    $config['note'] .= "(allowed values: ".implode(", ", $values).")";
                }
                switch ($type) {
                    case 'num':
                        $type = 'text';
                    case 'text':
                        break;
                    case 'array':
                        //switch ($tool->params->getSubType($id, $tool->params->generalProfile)) {
                        switch ($tool->params->getSubType($id, $blockId)) {
                            case 'select':
                                if ($id == 'template') {
                                    $type = 'select';
                                    break;
                                }
                            case 'radio':
                                //$type = 'radios';
                                $type = 'magiczoom_radios';
                                $config['style'] = 'margin-right: 5px;';
                                break;
                            default:
                                $type = 'text';
                        }
                        $config['values'] = array();
                        foreach ($values as $v) {
                            $config['values'][] = array('value'=>$v, 'label'=>$v);
                        }
                        break;
                    default:
                        $type = 'text';
                }

                $scope = $tool->params->getScope($id, $blockId);
                $desktopOnly = $tool->params->isForDesktopOnly($id, $blockId);
                if ($scope == 'magiczoom' && $desktopOnly !== true) {
                    $config['mobile-value'] = $tool->params->getMobileValue($id, $blockId);
                    if ($config['mobile-value'] === null) {
                        $config['mobile-value'] = $config['value'];
                    }
                    if ($desktopOnly) {
                        $config['desktop-only-values'] = $desktopOnly;
                    }
                    $fieldset->setData('show-platfrom-hint', true);
                }


                $field = $fieldset->addField($blockId.'-'.$id, $type, $config);
                $field->setRenderer($elementRenderer);

            }
        }

        return parent::_prepareForm();

    }

}