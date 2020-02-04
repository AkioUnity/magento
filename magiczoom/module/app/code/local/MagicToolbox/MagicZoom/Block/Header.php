<?php

class MagicToolbox_MagicZoom_Block_Header extends Mage_Core_Block_Template
{

    protected $pageType = '';
    protected $doDisplayProductPageScript = true;
    protected $doDisplayAdditionalScroll = true;

    public function _construct()
    {
        $this->setTemplate('magiczoom/header.phtml');
    }

    public function setPageType($pageType = '')
    {
        $this->pageType = $pageType;
    }

    public function getPageType()
    {
        return $this->pageType;
    }

    public function displayProductPageScript($display = null)
    {
        if ($display !== null) {
            $this->doDisplayProductPageScript = $display;
        }
        return $this->doDisplayProductPageScript;
    }

    public function displayAdditionalScroll($display = null)
    {
        if ($display !== null) {
            $this->doDisplayAdditionalScroll = $display;
        }
        return $this->doDisplayAdditionalScroll;
    }
}
