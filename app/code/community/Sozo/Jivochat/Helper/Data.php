<?php
/**
 * SOZO Design
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category    SOZO Design
 * @package     Sozo_Jivochat
 * @copyright   Copyright (c) 2018 SOZO Design (http://www.sozodesign.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */

class Sozo_Jivochat_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_JIVOCHAT_ENABLED = 'sozo_jivochat/general/enabled';
    const XML_JIVOCHAT_WIDGET_ID = 'sozo_jivochat/general/widget_id';

    public function isEnabled($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_JIVOCHAT_ENABLED, $store);
    }

    public function getWidgetId($store = null)
    {
        return Mage::getStoreConfig(self::XML_JIVOCHAT_WIDGET_ID, $store);
    }

    /**
     * Returns extension version.
     *
     * @return string
     */
    public function getExtensionVersion()
    {
        return (string)Mage::getConfig()->getNode()->modules->Sozo_Jivochat->version;
    }
}
