<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$this->startSetup();

$this->run("
    ALTER TABLE `{$this->getTable('amacart/canceled')}`
    CHANGE COLUMN `reason` `reason` ENUM('elapsed','bought','link','blacklist','admin','updated','quote') DEFAULT NULL;
");

$this->endSetup();