<?php
    /**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */
    $installer = $this;
    $installer->startSetup();
    
    $installer->run("
        UPDATE`{$this->getTable('amfeed/template')}`
        SET store_id = " . Mage::app()->getStore()->getId() . "
        ;
    ");