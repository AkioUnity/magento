<?php
    /**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */
    $installer = $this;
    $installer->startSetup();
    
    $installer->run("
        
        CREATE TABLE {$this->getTable('amfeed/field')}_300_backup LIKE {$this->getTable('amfeed/field')};
        INSERT INTO {$this->getTable('amfeed/field')}_300_backup SELECT * FROM {$this->getTable('amfeed/field')};

        CREATE TABLE {$this->getTable('amfeed/profile')}_300_backup LIKE {$this->getTable('amfeed/profile')};
        INSERT INTO {$this->getTable('amfeed/profile')}_300_backup SELECT * FROM {$this->getTable('amfeed/profile')};

        CREATE TABLE {$this->getTable('amfeed/template')}_300_backup LIKE {$this->getTable('amfeed/template')};
        INSERT INTO {$this->getTable('amfeed/template')}_300_backup SELECT * FROM {$this->getTable('amfeed/template')};


        ALTER TABLE `{$this->getTable('amfeed/profile')}` 
        ADD COLUMN `export_key` VARCHAR(100) DEFAULT NULL,
        ADD COLUMN `export_step` INT(5) DEFAULT NULL;
        
        
    ");
    
    $installer->endSetup();
    