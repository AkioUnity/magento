<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Storepickup
 * @module      Storepickup
 * @author      Magestore Developer
 *
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */

$installer = $this;
$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('storepickup_store_value')};
CREATE TABLE {$this->getTable('storepickup_store_value')} (
  `value_id` int(10) unsigned NOT NULL auto_increment,
  `storepickup_id` int(11) unsigned NOT NULL,
  `store_id` smallint(5) unsigned  NOT NULL,
  `attribute_code` varchar(63) NOT NULL default '',
  `value` text NOT NULL,
  UNIQUE(`storepickup_id`,`store_id`,`attribute_code`),
  INDEX (`storepickup_id`),
  INDEX (`store_id`),
  FOREIGN KEY (`storepickup_id`) REFERENCES {$this->getTable('storepickup_store')} (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`store_id`) REFERENCES {$this->getTable('core/store')} (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  PRIMARY KEY (`value_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup();
