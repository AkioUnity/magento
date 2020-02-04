<?php

class Biztech_Fedex_Model_Etdtype extends Mage_Core_Model_Abstract
{
    protected function _construct(){

       $this->_init("fedex/etdtype");

    }
    public function getEtdtypes(){
    	$etdtype = array();

    	$configEtds = explode(',',Mage::getStoreConfig('carriers/fedex/allow_etd'));
    	
    	foreach ($configEtds as $etd) {
    		$etdtype[] = array(
    			'label' => $etd,
    			'value' => $etd
    		);
    	}

    	return $etdtype;
    }
    
    
}
	 