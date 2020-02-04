<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */ 
class Amasty_Feed_Block_Adminhtml_Google_Edit_Tab_Basic extends Mage_Adminhtml_Block_Widget_Form
{
    public function getCurrencyList(){
        $currencyModel = Mage::getModel('directory/currency');

        $currencies = $currencyModel->getConfigAllowCurrencies();

        rsort($currencies);

        $ret = array();

        foreach($currencies as $currency){
            $ret[$currency] = $currency;
        }



        return $ret;
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $hlp   = Mage::helper('amfeed');

        $google = Mage::getModel('amfeed/google');

        $fldInfo = $form->addFieldset('amfeed_basic', array('legend'=> $hlp->__('Step 2: Basic Product Information')));

        $fldInfo->addField(
            'basic',
            'text',
            array(
                'name' => 'basic',
                'value' => $google->getBasicAttributes(),
                'label' => $hlp->__('Content'),
                'title' => $hlp->__('Content'),
                'note' => 'Please select attributes to output in feed'
            )
        );

        $form->getElement(
            'basic'
        )->setRenderer(
            $this->getLayout()->createBlock('amfeed/adminhtml_google_edit_tab_content_element')
        );

        $fldOptions = $form->addFieldset('amfeed_options', array('legend'=> $hlp->__('Options')));

        if (!Mage::app()->isSingleStoreMode()){
            $fldOptions->addField('store_id', 'select', array(
                'label'    => $hlp->__('Store View'),
                'class'    => 'required-entry',
                'required' => true,
                'name'     => 'store_id',
                'value' => $google->getStoreId(),
                'values'   => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm()
            ));
        }
        else {
            $fldOptions->addField('store_id', 'hidden', array(
                'name'  => 'store_id',
                'value' => $google->getStoreId()
            ));
        }

        $fldOptions->addField('currency', 'select', array(
            'label'     => $hlp->__('Price Currency'),
            'name'      => 'currency',
            'value'     => $google->getCurrency(),
            'options'    => $this->getCurrencyList(),
        ));


        return parent::_prepareForm();
    }
}