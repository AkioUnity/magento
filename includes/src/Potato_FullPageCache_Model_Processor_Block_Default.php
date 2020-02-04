<?php

class Potato_FullPageCache_Model_Processor_Block_Default
{
    /**
     * @param $block
     *
     * @return mixed
     */
    public function getBlockHtml($block)
    {
        return $block->toHtml();
    }

    /**
     * @param string $cachedHtml
     *
     * @return string
     */
    public function getPreparedHtmlFromCache($cachedHtml)
    {
        return $cachedHtml;
    }

    /**
     * ignore cache flag
     *
     * @return bool
     */
    public function getIsIgnoreCache()
    {
        return false;
    }
}