<?php

class Potato_FullPageCache_Helper_CacheStorage extends Mage_Core_Helper_Abstract
{
    const WITHOUT_TAGS_INDEX = 'without_tags';
    /**
     * check cache size
     *
     * @param $cacheSize
     *
     * @return bool
     */
    static function getIsAllowedCacheSize($cacheSize)
    {
        return (self::getCacheSize() + $cacheSize) < Potato_FullPageCache_Helper_Config::getMaxAllowedSize();
    }

    /**
     * @param Potato_FullPageCache_Model_Cache_Default $cache
     * @param array                                    $tags
     * @param null                                     $contentSize
     *
     * @return bool
     */
    static function registerCache(Potato_FullPageCache_Model_Cache_Default $cache, $tags = array(), $contentSize = null)
    {
        $storage = self::getStorageData();
        if (!$storage || !is_array($storage)) {
            $storage = array('size' => array(), 'tags' => array());
        }
        //cache size
        $storage['size'][$cache->getId()] = $contentSize;
        if (null === $contentSize) {
            $storage['size'][$cache->getId()] = self::calculateSize($cache->load($cache->getId()));
        }
        //cache tags
        $tagIndex = self::getKeyByTags($tags);
        if (!array_key_exists($tagIndex, $storage['tags'])) {
            $storage['tags'][self::getKeyByTags($tags)] = array();
        }
        array_push($storage['tags'][$tagIndex], $cache->getId());
        self::saveStorageData($storage);
        return true;
    }

    /**
     * @param $tags
     *
     * @return string
     */
    static function getKeyByTags($tags)
    {
        $tagsIndex = self::WITHOUT_TAGS_INDEX;
        if (!empty($tags)) {
            $tagsIndex = implode('_', $tags);
        }
        return $tagsIndex;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    static function unregisterCache($id)
    {
        $storage = self::getStorageData();
        if (array_key_exists($id, $storage['size'])) {
            unset( $storage['size'][$id]);
            foreach ($storage['tags'] as $tagIds) {
                foreach ($tagIds as $key => $cacheId) {
                    if ($id == $cacheId) {
                        unset($storage['tags'][$key]);
                        break 2;
                    }
                }
            }
        }
        self::saveStorageData($storage);
        return true;
    }

    /**
     * @param $tags
     *
     * @return array
     */
    static function getIdsByTags($tags)
    {
        $storage = self::getStorageData();
        $ids = array();
        if (array_key_exists(self::getKeyByTags($tags), $storage['tags'])) {
            return $storage['tags'][self::getKeyByTags($tags)];
        }
        return $ids;
    }

    static function saveStorageData($data)
    {
        $filename = Mage::getBaseDir('var') . DS . 'po_fpc' . DS . 'cache_storage';
        file_put_contents($filename, serialize($data));
        return true;
    }

    static function getStorageData()
    {
        $filename = Mage::getBaseDir('var') . DS . 'po_fpc' . DS . 'cache_storage';
        if (file_exists($filename)) {
            $data = @unserialize(file_get_contents($filename));
        }
        if (!isset($data) || !is_array($data)) {
            $data = array('size' => array(), 'tags' => array());
        }
        return $data;
    }

    static function cleanStorageData()
    {
        @unlink(Mage::getBaseDir('var') . DS . 'po_fpc' . DS . 'cache_storage');
        return true;
    }

    /**
     * @return int
     */
    static function getCacheSize()
    {
        $storage = self::getStorageData();
        return (int)array_sum($storage['size']);
    }

    /**
     * @param $content
     *
     * @return int
     */
    static function calculateSize($content)
    {
        if (is_array($content)) {
            $content = serialize($content);
        }
        return strlen($content);
    }

    static function getCacheFolderSize()
    {
        $size = 0;
        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(Potato_FullPageCache_Model_Cache_Default::getRootDir()), RecursiveIteratorIterator::SELF_FIRST);
        foreach($objects as $object) {
            if ($object->isFile()) {
                $size += $object->getSize();
            }
        }
        return $size;
    }
}