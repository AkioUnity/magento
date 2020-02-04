<?php

class Potato_FullPageCache_Model_Processor_Block_Session extends Potato_FullPageCache_Model_Processor_Block_Default
{
    static function getId()
    {
        return session_id();
    }

    /**
     * save cache
     *
     * @param $data
     * @param $index
     *
     * @return $this
     */
    public function save($data, $index)
    {
        $sessionBlockCache = Potato_FullPageCache_Model_Cache::getOutputCache(md5($index . $this->getId()),
            array('lifetime' => Potato_FullPageCache_Model_Cache::BLOCK_CACHE_LIFETIME)
        );
        $tags = array($this->getId());
        return $sessionBlockCache->save($data, null, $tags);
    }

    /**
     * @param string $prefix
     *
     * @return bool|mixed
     */
    public function load($prefix = '')
    {
        $sessionBlockCache = Potato_FullPageCache_Model_Cache::getOutputCache(md5($prefix . $this->getId()),
            array('lifetime' => Potato_FullPageCache_Model_Cache::BLOCK_CACHE_LIFETIME)
        );
        return $sessionBlockCache->test() ? $sessionBlockCache->load() : false;
    }
}