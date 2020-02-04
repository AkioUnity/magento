<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

    $installer = $this;
    $installer->startSetup();
    
    $installer->run("
        delete from `{$this->getTable('amfeed/template')}`
        where title = 'test';
        
        delete from `{$this->getTable('amfeed/template')}`
        where feed_id = 14;
    ");