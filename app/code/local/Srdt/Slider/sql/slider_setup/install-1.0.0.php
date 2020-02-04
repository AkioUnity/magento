<?php
$installer=$this;
$installer->startSetup();
$installer->run("
 DROP TABLE IF EXISTS {$this->getTable('slider')};
CREATE TABLE {$this->getTable('slider')} (
  `banner_id` int(11) unsigned NOT NULL auto_increment COMMENT 'id',
  `banner_title` varchar(255) NOT NULL COMMENT 'Title',
  `banner_type` int(11) unsigned NOT NULL COMMENT 'Banner Type Video Or Image',
  `banner_caption` varchar(255) COMMENT 'caption',
  `banner_image` varchar(255)  COMMENT 'Banner Image',
  `youtube_url` varchar(255)  COMMENT 'Youtube Image',
  `banner_status` boolean NOT NULL  COMMENT 'Status',
  `banner_url` varchar(255) COMMENT 'url to redirect',
  PRIMARY KEY (`banner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");
$installer->endSetup();
?>
