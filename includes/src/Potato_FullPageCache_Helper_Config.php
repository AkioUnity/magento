<?php

class Potato_FullPageCache_Helper_Config extends Mage_Core_Helper_Abstract
{
    const GENERAL_USE_USER_AGENT    = 'default/po_fpc/general/use_user_agent';
    const GENERAL_MAX_ALLOWED_SIZE  = 'default/po_fpc/general/max_allowed_size';

    const GENERATION_ENABLED        = 'po_fpc/auto_generation/enabled';
    const GENERATION_THREAD_NUMBER  = 'po_fpc/auto_generation/thread_number';

    const DEBUG_ENABLED             = 'default/po_fpc/debug/enabled';
    const DEBUG_IP_ADDRESSES        = 'default/po_fpc/debug/ip_addresses';

    /**
     * @return int
     */
    static function getIsDebugEnabled()
    {
        return (int)Potato_FullPageCache_Model_Cache::getCacheConfig()->getNode(self::DEBUG_ENABLED);
    }

    /**
     * @return array
     */
    static function getDebugIpAddresses()
    {
        $value = trim((string)Potato_FullPageCache_Model_Cache::getCacheConfig()->getNode(self::DEBUG_IP_ADDRESSES));
        $_result = array();
        if ($value) {
            $_result = explode(',', $value);
        }
        return $_result;
    }

    /**
     * @return int
     */
    static function getIsCanUseUserAgent()
    {
        return (int)Potato_FullPageCache_Model_Cache::getCacheConfig()->getNode(self::GENERAL_USE_USER_AGENT);
    }

    /**
     * @return int
     */
    static function getMaxAllowedSize()
    {
        return (int)Potato_FullPageCache_Model_Cache::getCacheConfig()->getNode(self::GENERAL_MAX_ALLOWED_SIZE) * 1024 * 1024;
    }

    /**
     * @param null $store
     *
     * @return bool
     */
    static function getIsAutoGenerationEnabled($store = null)
    {
        return (int)Mage::app()->getStore($store)->getConfig(self::GENERATION_ENABLED);
    }

    /**
     * @param null $store
     *
     * @return int
     */
    static function getAutoGenerationThreadNumber($store = null)
    {
        return max((int)Mage::app()->getStore($store)->getConfig(self::GENERATION_THREAD_NUMBER), 1);
    }
}