<?php

class MagicToolbox_MagicZoom_Block_Product_New extends Mage_Catalog_Block_Product_New
{
    protected function _toHtml()
    {
        $_productCollection = $this->getProductCollection();
        if ($_productCollection && $_productCollection->getSize()) {
            $magicToolboxHelper = Mage::helper('magiczoom/settings');
            $tool = $magicToolboxHelper->loadTool('newproductsblock');
            if (!$tool->params->checkValue('enable-effect', 'No')) {
                $contents = parent::_toHtml();
                $group = 'newproductsblock';
                $templatePath = Mage::getSingleton('core/design_package')->getTemplateFilename('magiczoom'.DS.'magictoolbox_list.phtml');
                require $templatePath;
                return $contents;
            }
        }
        return parent::_toHtml();
    }
}
