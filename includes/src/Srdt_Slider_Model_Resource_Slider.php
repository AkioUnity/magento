<?php
class Srdt_Slider_Model_Resource_Slider extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('slider/slider', 'banner_id');
    }
}
