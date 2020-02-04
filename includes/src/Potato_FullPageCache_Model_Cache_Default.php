<?php

class Potato_FullPageCache_Model_Cache_Default extends Mage_Core_Model_Cache
{
    protected $_id = null;

    /**
     * @param $options
     */
    public function __construct($options)
    {
        if (!array_key_exists('backend_options', $options)) {
            $options['backend_options'] = array();
        }
        $options['backend_options']['cache_dir'] = self::getRootDir();
        Mage::app()->getConfig()->getOptions()->createDirIfNotExists(self::getRootDir());

        if (!array_key_exists('frontend_options', $options)) {
            $options['frontend_options'] = array();
        }
        $options['frontend_options']['automatic_serialization'] = true;
        parent::__construct($options);
    }

    /**
     * @param            $content
     * @param null       $id
     * @param array      $tags
     * @param bool | int $lifetime
     *
     * @return $this
     */
    public function save($content, $id = null, $tags = array(), $lifetime = false)
    {
        if (null === $id) {
            $id = $this->getId();
        }
        $content = $this->_gzcompress($content);
        $cacheSize = Potato_FullPageCache_Helper_CacheStorage::calculateSize($content);
        if (!Potato_FullPageCache_Helper_CacheStorage::getIsAllowedCacheSize($cacheSize)) {
            Potato_FullPageCache_Model_Cache::cleanExpire();
            return $this;
        }
        $this->getFrontend()->save($content, $id, $tags, $lifetime);
        $metadata = $this->getFrontend()->getMetadatas($id);
        $metadataSize = Potato_FullPageCache_Helper_CacheStorage::calculateSize(serialize($metadata));
        Potato_FullPageCache_Helper_CacheStorage::registerCache($this, $tags, $cacheSize + $metadataSize);
        return $this;
    }

    /**
     * compress saved content
     *
     * @param $content
     *
     * @return string
     */
    protected function _gzcompress($content)
    {
        if (is_string($content) && function_exists('gzcompress')) {
            //compress content
            $content = gzcompress($content);
        }
        return $content;
    }

    /**
     * uncompress saved content
     *
     * @param $content
     *
     * @return string
     */
    protected function _gzuncompress($content)
    {
        if (is_string($content) && function_exists('gzuncompress')) {
            $content = gzuncompress($content);
        }
        return $content;
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->_id = md5($id);
        return $this;
    }

    /**
     * @param null | string $id
     *
     * @return mixed
     */
    public function load($id = null)
    {
        if (null === $id) {
            $id = $this->getId();
        }
        $content = $this->getFrontend()->load($id);
        return $this->_gzuncompress($content);
    }

    /**
     * @param null | string $id
     *
     * @return mixed
     */
    public function test($id = null)
    {
        $testId = $this->getId();
        if (null !== $id) {
            $testId = $id;
        }
        return $this->getFrontend()->test($testId);
    }

    /**
     * @return string
     */
    static function getRootDir()
    {
        return Mage::getBaseDir('var') . DS . 'po_fpc';
    }

    /**
     * @return null | string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @return mixed
     */
    public function delete()
    {
        Potato_FullPageCache_Helper_CacheStorage::unregisterCache($this->getId());
        return $this->getFrontend()->remove($this->getId());
    }
}