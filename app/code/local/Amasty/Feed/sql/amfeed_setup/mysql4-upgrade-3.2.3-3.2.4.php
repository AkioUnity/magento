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
        ADD COLUMN `currency` VARCHAR(255) default null;
    ");