<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */
$this->startSetup();

$this->run("

CREATE TABLE `{$this->getTable('amfeed/category')}` (
  `feed_category_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Feed Category Id',
  `code` VARCHAR(255) DEFAULT NULL COMMENT 'Code',
  `name` VARCHAR(255) DEFAULT NULL COMMENT 'Name',
  PRIMARY KEY (`feed_category_id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('amfeed/category_mapping')}` (
  `entity_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Rule Id',
  `feed_category_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Feed Category ID',
  `category_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Category ID',
  `variable` VARCHAR(255) DEFAULT NULL COMMENT 'Variable',
  PRIMARY KEY (`entity_id`),
  KEY `AMASTY_FEED_CATEGORY_FEED_CATEGORY_ID` (`feed_category_id`),
  KEY `CATALOG_CATEGORY_ENTITY_CATEGORY_ID` (`category_id`),
  CONSTRAINT `AMASTY_FEED_CTGR_FEED_CTGR_ID_FEED_CTGR_FEED_CTGR_ID` FOREIGN KEY (`feed_category_id`) REFERENCES `{$this->getTable('amfeed/category')}` (`feed_category_id`) ON DELETE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8;
");

$this->endSetup();