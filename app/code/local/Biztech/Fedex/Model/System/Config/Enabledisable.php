<?php
    class Biztech_Fedex_Model_System_Config_Enabledisable{

        public function toOptionArray()
        {
            $options = array(
                array('value' => 0, 'label'=>Mage::helper('fedex')->__('No')),
            );
            $websites = Mage::helper('fedex')->getAllWebsites();
            if(!empty($websites)){
                $options[] = array('value' => 1, 'label'=>Mage::helper('fedex')->__('Yes'));
            }
            return $options;
        }

}