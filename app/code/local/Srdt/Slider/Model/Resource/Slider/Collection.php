<?php 
class Srdt_Slider_Model_Resource_Slider_Collection 
extends Mage_Core_Model_Resource_Db_Collection_Abstract{
	protected function _constuct(){
		$this->_init('slider/slider');	
	}
}
