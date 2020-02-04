<?php
    /**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */
    $installer = $this;
    $installer->startSetup();
    
    $installer->run("
                alter table `{$this->getTable('amfeed/profile')}` 
                ADD COLUMN `csv_header_static` TEXT NOT NULL AFTER csv_header;
    ");
    $installer->endSetup();
