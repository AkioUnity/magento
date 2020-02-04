<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */ 
class Amasty_Feed_Block_Adminhtml_Profile_Edit_Tab_Delivery extends Amasty_Feed_Block_Adminhtml_Widget_Edit_Tab_Dynamic
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amasty/amfeed/feed/delivery.phtml');
        $this->_fields  = array('delivery_type', 'ftp_host', 'ftp_user', 'ftp_pass', 'ftp_folder', 'ftp_is_passive');
        $this->_model   = 'amfeed_profile';        
    }
}