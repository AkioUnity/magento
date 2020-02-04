<?php

class Potato_FullPageCache_Model_Processor_Block_Session_Debug
    extends Potato_FullPageCache_Model_Processor_Block_Session
{
    /**
     * @return string
     */
    static function getId()
    {
        return 'Potato_FullPageCache_Model_Processor_Block_Session_Debug';
    }

    /**
     * @param array $data('html','name_in_layout')
     * @param $index
     *
     * @return bool
     */
    public function save($data, $index)
    {
        $data['html'] .= '<script type="text/javascript">if($("cache_miss_message") && $("cache_hit_message")) {$("cache_miss_message").hide();$("cache_hit_message").show()};</script>';
        $sessionBlockCache = Potato_FullPageCache_Model_Cache::getOutputCache(md5($index . $this->getId()),
            array('lifetime' => Potato_FullPageCache_Model_Cache::BLOCK_CACHE_LIFETIME)
        );
        $tags = array(Potato_FullPageCache_Model_Cache::SESSION_BLOCK_TAG);
        return $sessionBlockCache->save($data, null, $tags);
    }
}