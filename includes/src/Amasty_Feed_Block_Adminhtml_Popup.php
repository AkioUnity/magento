<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

class Amasty_Feed_Block_Adminhtml_Popup extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }

    public function getCloseUrl()
    {
        $url = '';

        if (Mage::registry('amfeed_profile')) {
            $feed = Mage::registry('amfeed_profile');
            $url = Mage::getModel('adminhtml/url')->getUrl('*/amfeed_profile/edit', array(
                'id' => $feed->getId()
            ));

        }

        return $url;
    }
}