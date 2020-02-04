<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */
class Amasty_Feed_Block_Adminhtml_Google_Edit_Tab_Optional extends Mage_Adminhtml_Block_Widget_Form
{
    public function getCurrencyList(){
        $currencyModel = Mage::getModel('directory/currency');

        $currencies = $currencyModel->getConfigAllowCurrencies();

        rsort($currencies);

        return $currencies;
    }

    protected function _prepareForm()
    {

        $form = new Varien_Data_Form();
        $this->setForm($form);

        $hlp   = Mage::helper('amfeed');

        $fldInfo = $form->addFieldset('amfeed_optional', array('legend'=> $hlp->__('Step 3: Optional Product Information')));

        $fldInfo->addField(
            'optional',
            'text',
            array(
                'name' => 'optional',
                'value' => Mage::getModel('amfeed/google')->getOptionalAttributes(),
                'label' => $hlp->__('Content'),
                'title' => $hlp->__('Content'),
                'note' => 'Please select attributes to output in feed'
            )
        );

        $form->getElement(
            'optional'
        )->setRenderer(
            $this->getLayout()->createBlock('amfeed/adminhtml_google_edit_tab_content_element')
        );

        return parent::_prepareForm();
    }
}