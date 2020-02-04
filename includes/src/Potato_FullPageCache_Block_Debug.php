<?php

class Potato_FullPageCache_Block_Debug extends Mage_Core_Block_Template
{
    /**
     * @return bool
     */
    public function canShow()
    {
        return Potato_FullPageCache_Helper_Data::isDebugModeEnabled();
    }
}