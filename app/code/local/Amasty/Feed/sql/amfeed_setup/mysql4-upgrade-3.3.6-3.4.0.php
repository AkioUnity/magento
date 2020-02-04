<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$magentoVersion = Mage::getVersion();
if (version_compare($magentoVersion, '1.6', '>=')) {
    $installer->getConnection()->addColumn(
        $this->getTable('amfeed/profile'),
        'compress',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 255,
            'nullable' => false,
            'default' => Amasty_Feed_Model_Profile::COMPRESS_NONE,
            'comment' => 'Compress'
        )
    );
} else {
    $installer->getConnection()->addColumn(
        $this->getTable('amfeed/profile'),
        'compress',
        'TEXT NOT NULL DEFAULT \'\''
    );
}

$this->endSetup();
