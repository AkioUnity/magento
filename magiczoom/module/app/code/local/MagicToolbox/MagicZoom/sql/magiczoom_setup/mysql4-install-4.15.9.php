<?php

/* @var $installer MagicToolbox_MagicZoom_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$oldModulesInstalled = Mage::helper('magiczoom/params')->checkForOldModules();
if (empty($oldModulesInstalled)) {
    $mtDefaultValues = Mage::helper('magiczoom/params')->getDefaultValues();
} else {
    $mtDefaultValues = Mage::helper('magiczoom/params')->getFixedDefaultValues();
}

//NOTE: quotes need to be escaped
$mtDefaultValues = serialize($mtDefaultValues);

$installer->run("

DROP TABLE IF EXISTS `{$this->getTable('magiczoom/settings')}`;
CREATE TABLE `{$this->getTable('magiczoom/settings')}` (
    `setting_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `website_id` smallint(5) unsigned default NULL,
    `group_id` smallint(5) unsigned default NULL,
    `store_id` smallint(5) unsigned default NULL,
    `package` varchar(255) NOT NULL default '',
    `theme` varchar(255) NOT NULL default '',
    `last_edit_time` datetime default NULL,
    `custom_settings_title` varchar(255) NOT NULL default '',
    `value` text,
    PRIMARY KEY (`setting_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

INSERT INTO `{$this->getTable('magiczoom/settings')}` (`setting_id`, `website_id`, `group_id`, `store_id`, `package`, `theme`, `last_edit_time`, `custom_settings_title`, `value`) VALUES (NULL, NULL, NULL, NULL, '', '', NULL, 'Edit Magic Zoom default settings', '{$mtDefaultValues}');

");

$attribute = $installer->getAttribute('catalog_product', 'product_videos');
if (!$attribute) {
    $installer->installEntities();
}

$installer->endSetup();
