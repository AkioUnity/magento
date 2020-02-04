<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */
$this->startSetup();

$this->run("
CREATE TABLE `{$this->getTable('amfeed/profile')}` (
  `feed_id`  mediumint(8) unsigned NOT NULL auto_increment,
  `store_id` smallint(6) NOT NULL,
  `status`   smallint(1) NOT NULL,
  
  `generated_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `delivery_at`  datetime NOT NULL default '0000-00-00 00:00:00',

  `type`     tinyint(1)   NOT NULL,
  `title`    varchar(255) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `mode`     tinyint(1)   NOT NULL default '0',

  `cond_stock`    tinyint(1) NOT NULL default '1',
  `cond_disabled` tinyint(1) NOT NULL default '1',
  `cond_type`     text NOT NULL default '',
  `cond_advanced` text NOT NULL default '',
  
  `xml_header` text NOT NULL default '',
  `xml_body`   text NOT NULL default '',
  `xml_footer` text NOT NULL default '',
  `xml_item`   varchar(255)  default '',
  
  `csv`           text NOT NULL,
  `csv_header`    tinyint(1) NOT NULL default '0',
  `csv_enclosure` tinyint(1) NOT NULL default '0',
  `csv_delimiter` tinyint(1) NOT NULL default '0',
  
  `frm_date`      varchar(255) NOT NULL,
  `frm_price`     tinyint(1) NOT NULL default '2',
  `frm_url`       tinyint(1) NOT NULL default '0',
  `frm_image_url` tinyint(1) NOT NULL default '0',
  `default_image` tinyint(1) NOT NULL default '0',
  
  `delivery_type`  tinyint(1) NOT NULL default '0',
  `delivered`      tinyint(1) NOT NULL default '0',
  `send_email`     varchar(50)    default '',
  
  `ftp_host`       varchar(128)   default '',
  `ftp_user`       varchar(255)   default '',
  `ftp_pass`       varchar(255)   default '',
  `ftp_folder`     varchar(255)   default '/',
  `ftp_is_passive` tinyint(1) NOT NULL default '0',
  
  `info_total`  int  NOT NULL default '0',
  `info_cnt`    smallint(6) NOT NULL default '0',
  `info_errors` tinyint(1)  NOT NULL default '0',
  
  `freq`        tinyint(2)  NOT NULL,
  `on_days`     text NOT NULL default '',
  `hour_from`   tinyint(2)  NOT NULL,
  `hour_to`     tinyint(2)  NOT NULL,
  `error_email` varchar(50) default '',
  
  PRIMARY KEY  (`feed_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE `{$this->getTable('amfeed/field')}` (
  `field_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code`     varchar(128) NOT NULL,
  `title`    varchar(255) NOT NULL,
  
  `base_attr`     varchar(255) NOT NULL,
  `default_value` varchar(255) NOT NULL,
  
  `transform` varchar(255) NOT NULL,
  `mapping`   text NOT NULL default '',
  
  PRIMARY KEY  (`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

");

$this->endSetup(); 