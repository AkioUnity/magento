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
            ADD COLUMN `max_images` int(10) unsigned not null default 5 ;
");
$installer->endSetup(); 