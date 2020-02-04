<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Table
 */ 
class Amasty_Table_Block_Adminhtml_Method_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        /* @var $hlp Amasty_Table_Helper_Data */
        $hlp = Mage::helper('amtable');
    
        $fldInfo = $form->addFieldset('general', array('legend'=> $hlp->__('General')));
        $fldInfo->addField('name', 'text', array(
            'label'     => $hlp->__('Name'),
            'required'  => true,
            'name'      => 'name',
            'note'      => $hlp->__('Specify the "estimated delivery" value for the {day} variable on the Rate Configuration page or it will be automatically taken from the uploaded CSV file'),
        ));

        $fldInfo->addField('free_types', 'multiselect', array(
            'label'     => $hlp->__('Ship These Shipping Types for Free'),
            'name'      => 'free_types[]',
            'values'    => $hlp->getAllShippingTypes(),
            'note'      => $hlp->__('Products will be sent for free if the method does not contain rates applicable for them. If there will be no applicable rates in the method it won\'t be displayed as products with Shipping Types above will be ignored in calculations.'),
        ));

        $fldInfo->addField('comment', 'textarea', array(
            'label'     => $hlp->__('Comment'),
            'name'      => 'comment',
            'note'      => $hlp->__('HTML tags supported'),
        ));

        $fldInfo->addField('is_active', 'select', array(
            'label'     => Mage::helper('salesrule')->__('Status'),
            'name'      => 'is_active',
            'options'    => $hlp->getStatuses(),
        ));  
            
        if ($hlp->_isRateResultRewritten()) {
            $fldInfo->addField('pos', 'text', array(
                'label'     => Mage::helper('salesrule')->__('Position'),
                'name'      => 'pos',
            ));
        }

        $fldRate = $form->addFieldset('rates', array('legend'=> $hlp->__('Rates')));

        $fldRate->addField('min_rate', 'text', array(
            'label'     => $hlp->__('Minimal rate'),
            'name'      => 'min_rate',
        ));

        $fldRate->addField('max_rate', 'text', array(
            'label'     => $hlp->__('Maximal rate'),
            'name'      => 'max_rate',
        ));


        $fldRate->addField('select_rate', 'select', array(
            'label'     => $hlp->__('For products with different shipping types'),
            'name'      => 'select_rate',
            'values'    => array(
                array(
                    'value' => Amasty_Table_Model_Rate::ALGORITHM_SUM ,
                    'label' => $hlp->__('Sum up rates')
                ),
                array(
                    'value' => Amasty_Table_Model_Rate::ALGORITHM_MAX ,
                    'label' => $hlp->__('Select maximal rate')
                ),
                array(
                    'value' => Amasty_Table_Model_Rate::ALGORITHM_MIN ,
                    'label' => $hlp->__('Select minimal rate')
                ))
        ));
        
        //set form values
        $form->setValues(Mage::registry('amtable_method')->getData()); 
        
        return parent::_prepareForm();
    }
}