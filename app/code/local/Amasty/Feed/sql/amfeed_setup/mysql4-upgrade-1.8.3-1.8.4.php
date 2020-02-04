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
                CHANGE COLUMN `info_cnt` `info_cnt` INT(11) NOT NULL DEFAULT '0';
    ");
    $installer->endSetup();