<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Table
 */


$installer = $this;
$installer->startSetup();

$installer->run("
CREATE TABLE IF NOT EXISTS `{$this->getTable('amtable/method_store')}` (
  `id`          int(10) unsigned NOT NULL auto_increment,
  `method_id`   mediumint(8) unsigned NOT NULL,
  `store_id`    smallint(5) unsigned NOT NULL, 
  `label`       varchar(255)  default NULL, 
  `comment`     TEXT  default NULL, 
  PRIMARY KEY  (`id`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->run("
ALTER TABLE  `{$this->getTable('amtable/method_store')}`
  ADD FOREIGN KEY (`method_id`) REFERENCES `{$this->getTable('amtable/method')}` (`method_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$installer->endSetup();