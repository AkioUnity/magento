<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Table
 */
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE `{$this->getTable('amtable/rate')}` ADD `num_zip_from` INT UNSIGNED NOT NULL , ADD `num_zip_to` INT UNSIGNED NOT NULL;

UPDATE `{$this->getTable('amtable/rate')}` SET num_zip_from=zip_from, num_zip_to=zip_to;

");
