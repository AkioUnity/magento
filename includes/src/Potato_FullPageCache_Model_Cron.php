<?php

class Potato_FullPageCache_Model_Cron
{
    public function cleanByLimits()
    {
        if (Potato_FullPageCache_Helper_CacheStorage::getCacheFolderSize() >=
            Potato_FullPageCache_Helper_Config::getMaxAllowedSize()
        ) {
            Potato_FullPageCache_Model_Cache::clean();
        }
        return $this;
    }
}