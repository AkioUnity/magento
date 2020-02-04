<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */ 
class Amasty_Feed_Model_Rule extends Mage_CatalogRule_Model_Rule
{
    public function _construct()
    {
        parent::_construct();
        
    }
    
    public function getConditionsInstance()
    {
        return Mage::getModel('amfeed/rule_condition_combine');
    }
}