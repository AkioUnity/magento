<?php


class Biztech_Fedex_Model_System_Config_Source_Allowetd extends Varien_Data_Collection{

    public function toOptionArray(){            
        
        $allowetds = array(

            array('value' => 'COMMERCIAL_INVOICE', 'label'=>Mage::helper('fedex')->__('COMMERCIAL_INVOICE')),
            array('value' => 'CERTIFICATE_OF_ORIGIN', 'label'=>Mage::helper('fedex')->__('CERTIFICATE_OF_ORIGIN')),
            array('value' => 'NAFTA_CERTIFICATE_OF_ORIGIN', 'label'=>Mage::helper('fedex')->__('NAFTA_CERTIFICATE_OF_ORIGIN')),
            array('value' => 'PRO_FORMA_INVOICE', 'label'=>Mage::helper('fedex')->__('PRO_FORMA_INVOICE')),
            array('value' => 'OTHER', 'label'=>Mage::helper('fedex')->__('OTHER')),

          
        );
        
        return $allowetds;
    }

    
}
