<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */ 
class Amasty_Feed_Block_Adminhtml_Profile_Grid_Renderer_File extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
        if (file_exists($row->geOutputPath())) {

            $downloadUrl = $row->getDownloadUrl();

            return '<a href="'. $downloadUrl .'">' . $row->getFilename() . '</a>';
        } else {
            return $row->getFilename();
        }
    }
}