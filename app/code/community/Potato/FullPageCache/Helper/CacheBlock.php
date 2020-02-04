<?php

class Potato_FullPageCache_Helper_CacheBlock extends Mage_Core_Helper_Abstract
{
    /**
     * @param $data array('html', 'name_in_layout')
     * @param $index
     *
     * @return bool
     */
    static function saveSkippedBlockCache($data, $index)
    {
        if (array_key_exists($index, Potato_FullPageCache_Model_Cache::getSessionBlocks())) {
            $blockProcessor = Potato_FullPageCache_Model_Cache::getBlockCacheProcessor($index);
            return $blockProcessor->save($data, $index);
        }
        return self::saveActionBlockCache($data, $index);
    }

    /**
     * @param $data
     * @param $index
     *
     * @return bool
     */
    static function saveActionBlockCache($data, $index)
    {
        $cache = self::getActionBlockCacheInstance();
        $_cachedData = self::getActionBlockCache();
        $_cachedData[$index] = $data;
        $cache->save($_cachedData, null,
            array_merge(
                array(Potato_FullPageCache_Model_Cache::BLOCK_TAG),
                Potato_FullPageCache_Helper_Data::getCacheTags()
            )
        );
        return true;
    }

    /**
     * @return Potato_FullPageCache_Model_Cache_Default
     */
    static function getActionBlockCacheInstance()
    {
        $lifetime = false;
        $actionConfig = Potato_FullPageCache_Model_Cache::getActionConfig();
        if (isset($actionConfig['lifetime'])) {
            $lifetime = $actionConfig['lifetime'];
        }
        return Potato_FullPageCache_Model_Cache::getOutputCache('blocks_'
            . Potato_FullPageCache_Model_Cache::getPageCache()->getId(),
            array('lifetime' => $lifetime)
        );
    }

    /**
     * @return array|mixed
     */
    static function getActionBlockCache()
    {
        $result = array();
        $cache = self::getActionBlockCacheInstance();
        if ($cache->test()) {
            $result = $cache->load();
        }
        return $result;
    }

    /**
     * return index for skipped blocks by actions ( specified in po_fpc.xml)
     *
     * @return string
     */
    static function getSkippedBlocksByActionIndex()
    {
        return Mage::app()->getRequest()->getModuleName() . '_'
            . Mage::app()->getRequest()->getControllerName() . '_'
            . Mage::app()->getRequest()->getActionName()
        ;
    }
}