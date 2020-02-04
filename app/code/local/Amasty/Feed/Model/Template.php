<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */  
class Amasty_Feed_Model_Template extends Mage_Core_Model_Abstract
{
    public function _construct()
    {    
        $this->_init('amfeed/template');
    }
    
    public function import(){
        $data = $this->getData();
        unset($data['feed_id']);
        
        $profile = Mage::getModel('amfeed/profile');
        
        $profile->setData($data);
        
        return $profile->save();
    }
}