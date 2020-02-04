<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */
$installer = $this;
$installer->startSetup();
$installer->run("
            ALTER TABLE `{$this->getTable('amfeed/profile')}` 
            ADD COLUMN `frm_dont_use_category_in_url` TINYINT(1) NOT NULL DEFAULT '0' after frm_image_url;
");
$installer->endSetup(); 