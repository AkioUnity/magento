<?php

class Biztech_Fedex_Model_Pickup extends Mage_Core_Model_Abstract
{
    protected function _construct(){

       $this->_init("fedex/pickup");

    }
    public function getCountries(){
    	$countryList = Mage::getModel('directory/country')->getResourceCollection()->loadByStore()->toOptionArray(true);
        $countriesListNew = array();
    	foreach($countryList as $key => $countries){
    		if($countries['label'] != 1){
    			$countriesListNew[] = $countries;
	    	}
    	}
        return $countriesListNew;
    }
}
	 