<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */
class Amasty_Feed_Block_Adminhtml_Google_Edit_Tab_Delivery extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $hlp   = Mage::helper('amfeed');

        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('delivery_fieldset', array('legend' => $hlp->__('Upload feeds to Google servers automatically?')));

        $fieldset->addField('filename', 'text', array(
            'label'    => $hlp->__('Filename'),
            'name'     => 'filename',
            'class'    => 'required-entry',
            'required' => true,
        ));

        $fieldset->addField(
            'delivery_type',
            'select',
            array(
                'label' => $hlp->__('Upload method'),
                'title' => $hlp->__('Upload method'),
                'name' => 'delivery_type',
                'values'   => array(
                    array(
                        'value' => Amasty_Feed_Model_Profile::DELIVERY_TYPE_DLD,
                        'label' => Mage::helper('amfeed')->__('No, upload manually')
                    ),
                    array(
                        'value' => Amasty_Feed_Model_Profile::DELIVERY_TYPE_FTP,
                        'label' => Mage::helper('amfeed')->__('Yes, use FTP connection')
                    ),
                    array(
                        'value' => Amasty_Feed_Model_Profile::DELIVERY_TYPE_SFTP,
                        'label' => Mage::helper('amfeed')->__('Yes, use SFTP connection')
                    )
                ),
                'after_element_html' => '<small>'.$hlp->__('You can generate password in Google Merchant Center > Settings > FTP and SFTP').'</small>',
                'value' => Amasty_Feed_Model_Profile::DELIVERY_TYPE_DLD
            )
        );

        $fieldset->addField(
            'ftp_host',
            'text',
            array(
                'name' => 'ftp_host',
                'label' => $hlp->__('Host'),
                'title' => $hlp->__('Host'),
                'after_element_html' => '<small>'.$hlp->__('Add port if necessary (example.com:321)').'</small>'
            )
        );

        $fieldset->addField(
            'ftp_user',
            'text',
            array(
                'name' => 'ftp_user',
                'label' => $hlp->__('Login'),
                'title' => $hlp->__('Login')
            )
        );

        $fieldset->addField(
            'ftp_pass',
            'text',
            array(
                'name' => 'ftp_pass',
                'label' => $hlp->__('Password'),
                'title' => $hlp->__('Password')
            )
        );

        $fieldset->addField(
            'ftp_is_passive',
            'select',
            array(
                'label' => $hlp->__('Passive Mode'),
                'title' => $hlp->__('Passive Mode'),
                'name' => 'ftp_is_passive',
                'options' => array('1' => $hlp->__('Yes'), '0' => $hlp->__('No'))
            )
        );

        $fieldset->addField('mode', 'select', array(
            'label'    => $hlp->__('Generate feed'),
            'name'     => 'mode',
            'values'   => Mage::getModel('amfeed/source_mode')->toOptionArray()
        ));

        $fieldset->addField('setup_complete', 'hidden', array(
            'name'  => 'setup_complete',
            'value' => 1
        ));




        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getFormHtml()
    {
        $formHtml = parent::getFormHtml();

        $formHtml .= '<script>
                function onChangeDeliveryType(){
                    $("ftp_host").up("tr").show()
                    $("ftp_user").up("tr").show()
                    $("ftp_pass").up("tr").show()

                    if ($("delivery_type" ).value == "'.Amasty_Feed_Model_Profile::DELIVERY_TYPE_FTP.'"){
                        $("ftp_is_passive").up("tr").show();
                    } else if ($("delivery_type" ).value == "'.Amasty_Feed_Model_Profile::DELIVERY_TYPE_DLD.'"){
                        $("ftp_is_passive").up("tr").hide()

                        $("ftp_host").up("tr").hide()
                        $("ftp_user").up("tr").hide()
                        $("ftp_pass").up("tr").hide()
                    } else {
                        $("ftp_is_passive").up("tr").hide()
                    }
                }

                $("delivery_type" ).observe("change", onChangeDeliveryType);

                onChangeDeliveryType();
        </script>';

        return $formHtml;
    }
}