<?php

class Potato_FullPageCache_Helper_CacheStore extends Mage_Core_Helper_Abstract
{
    /**
     * @return Potato_FullPageCache_Model_Cache_Default
     */
    static function getCacheStoreInstance()
    {
        return Potato_FullPageCache_Model_Cache::getOutputCache(Potato_FullPageCache_Helper_Data::getRequestHash(),
            array('lifetime' => Potato_FullPageCache_Model_Cache::CONFIG_CACHE_LIFETIME)
        );
    }

    /**
     * @return bool
     */
    static function loadStoreByRequest()
    {
        $cache = self::getCacheStoreInstance();
        if ($cache->test()) {
            $result = $cache->load();
            if (!is_array($result)) {
                return false;
            }
            asort($result);
            return $result[0];
        }
        return false;
    }

    /**
     * @return bool
     */
    static function saveStoreByRequest()
    {
        $cache = self::getCacheStoreInstance();
        $result = array();
        if ($cache->test()) {
            $result = $cache->load();
        }
        $result[] = Mage::app()->getStore()->getId();
        $cache->save($result, null, array(Potato_FullPageCache_Model_Cache::CACHE_STORE));
        return true;
    }
}