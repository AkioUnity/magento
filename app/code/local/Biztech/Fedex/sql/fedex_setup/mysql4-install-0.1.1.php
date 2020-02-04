<?php

$installer = $this;
$installer->startSetup();

$installer->getConnection()
->addColumn($installer->getTable('sales/order'),'reason_for_return', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
    'nullable'  => false,
    'length'    => 255,
    'after'     => null, 
    'comment'   => 'Reason For Return'
));   


$installer->getConnection()
->addColumn($installer->getTable('sales/order'),'return_avail_date', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_DATETIME,
    'nullable'  => false,
    'length'    => 255,
    'after'     => null, 
    'comment'   => 'Return Avail Date'
));   


$installer->getConnection()
->addColumn($installer->getTable('sales/order'),'is_return', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
    'nullable'  => false,
    'length'    => 255,
    'after'     => null, 
    'comment'   => 'Is Return'
));   





$installer = new Mage_Sales_Model_Resource_Setup('core_setup');
/**
 * Add 'custom_attribute' attribute for entities
 */
$entities = array(
    'quote',
    'quote_address',
    'quote_item',
    'quote_address_item',
    'order',
    'order_item'
);
$options = array(
    'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
    'visible'  => true,
    'required' => false
);
foreach ($entities as $entity) {
    $installer->addAttribute($entity, 'fedex_hal_content', $options);
}


$installer = new Mage_Sales_Model_Resource_Setup('core_setup');
/**
 * Add 'custom_attribute' attribute for entities
 */
$entities = array(
    'quote',
    'quote_address',
    'quote_item',
    'quote_address_item',
    'order',
    'order_item'
);
$options = array(
    'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
    'visible'  => true,
    'required' => false
);
foreach ($entities as $entity) {
    $installer->addAttribute($entity, 'fedex_hal_enable', $options);
}


 $installer->addAttribute('catalog_product', 'is_dangerous_goods', array(
    'label'         => 'Is Dangerous Goods',
    'group'         => 'General',
    'input'         => 'boolean',
    'type'          => 'int',
    'visible'       => 1,
    'required'      => 0,
    'user_defined'  => 1,
    'source'        => 'eav/entity_attribute_source_boolean',
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL

));

$installer = new Mage_Sales_Model_Resource_Setup('core_setup');
/**
 * Add 'custom_attribute' attribute for entities
 */
$entities = array(
    'quote',
    'quote_address',
    'quote_item',
    'quote_address_item',
    'order',
    'order_item'
);
$options = array(
    'type'     => Varien_Db_Ddl_Table::TYPE_VARCHAR,
    'visible'  => true,
    'required' => false
);
foreach ($entities as $entity) {
    $installer->addAttribute($entity, 'is_dangerous_goods', $options);
}


$installer->addAttribute('catalog_product', 'is_alchohol', array(
    'label'         => 'Is Alchohol',
    'group'         => 'General',
    'input'         => 'boolean',
    'type'          => 'int',
    'visible'       => 1,
    'required'      => 0,
    'user_defined'  => 1,
    'source'        => 'eav/entity_attribute_source_boolean',
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL

));
$installer->endSetup(); 


$installer = new Mage_Sales_Model_Resource_Setup('core_setup');
/**
 * Add 'custom_attribute' attribute for entities
 */
$entities = array(
    'quote',
    'quote_address',
    'quote_item',
    'quote_address_item',
    'order',
    'order_item'
);
$options = array(
    'type'     => Varien_Db_Ddl_Table::TYPE_VARCHAR,
    'visible'  => true,
    'required' => false
);
foreach ($entities as $entity) {
    $installer->addAttribute($entity, 'is_alchohol', $options);
}

$installer->addAttribute('catalog_product', 'dry_ice', array(
    'label'         => 'Dry Ice',
    'group'         => 'General',
    'input'         => 'boolean',
    'type'          => 'int',
    'visible'       => 1,
    'required'      => 0,
    'user_defined'  => 1,
    'source'        => 'eav/entity_attribute_source_boolean',
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL

));
$installer->endSetup(); 


$installer = new Mage_Sales_Model_Resource_Setup('core_setup');
/**
 * Add 'custom_attribute' attribute for entities
 */
$entities = array(
    'quote',
    'quote_address',
    'quote_item',
    'quote_address_item',
    'order',
    'order_item'
);
$options = array(
    'type'     => Varien_Db_Ddl_Table::TYPE_VARCHAR,
    'visible'  => true,
    'required' => false
);
foreach ($entities as $entity) {
    $installer->addAttribute($entity, 'dry_ice', $options);
}


$entities = array(
    'shipment',
    
);
$options = array(
    'type'     => Varien_Db_Ddl_Table::TYPE_BLOB,
    'visible'  => true,
    'required' => false
);
foreach ($entities as $entity) {
    $installer->addAttribute($entity, 'return_shipping_label', $options);
}

$entities = array(
    'shipment',
    
);
$options = array(
    'type'     => Varien_Db_Ddl_Table::TYPE_BLOB,
    'visible'  => true,
    'required' => false
);
foreach ($entities as $entity) {
    $installer->addAttribute($entity, 'etd_label_content', $options);
}



$installer = new Mage_Sales_Model_Resource_Setup('core_setup');

$entities = array(
    'shipment_track',
    
);
$options = array(
    'type'     => Varien_Db_Ddl_Table::TYPE_BLOB,
    'visible'  => true,
    'required' => false
);
foreach ($entities as $entity) {
    $installer->addAttribute($entity, 'spod_content', $options);
}

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('fedex_shipment')};
    CREATE TABLE {$this->getTable('fedex_shipment')} (
    `shipment_id` int(11) unsigned NOT NULL auto_increment,
    `order_id` varchar(255) NOT NULL default '',
    `document_id` varchar(255) NOT NULL default '',
     PRIMARY KEY (`shipment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `{$this->getTable('fedex_shipment')}`
  MODIFY `shipment_id` int(11) unsigned NOT NULL AUTO_INCREMENT;

");


$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('fedex_etdtype')};
    CREATE TABLE {$this->getTable('fedex_etdtype')} (
    `etdtype_id` int(11) unsigned NOT NULL auto_increment,
    `name` varchar(255) NOT NULL default '',
    `title` varchar(255) NOT NULL default '',
    `content` text NOT NULL default '',
     PRIMARY KEY (`etdtype_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `{$this->getTable('fedex_etdtype')}`
  MODIFY `etdtype_id` int(11) unsigned NOT NULL AUTO_INCREMENT;

");





$installer->run("

    -- DROP TABLE IF EXISTS {$this->getTable('fedex_pickup')};
    CREATE TABLE {$this->getTable('fedex_pickup')} (
    `pickup_id` int(11) unsigned NOT NULL auto_increment,
    `shipment_id` int(10) unsigned NULL ,

    person_name varchar(100), 
    company_name varchar(100), 
    phone_no varchar(100), 
    pickup_address varchar(100), 
    pickup_city varchar(100), 
    pickup_state varchar(100), 
    pickup_postcode varchar(100), 
    pickup_country varchar(100), 
    package_location varchar(100), 
    building_partcode varchar(100), 
    building_part_description varchar(100), 
    ready_timestamp varchar(100), 
    company_closetime varchar(100), 
    package_count varchar(100), 
    total_weight_unit varchar(100), 
    total_weight_value varchar(100), 
    courier_code varchar(100), 
    courier_remarks varchar(100), 
    
    `status` smallint(6) NOT NULL default '0',
    `confirmation_no` smallint(6) NOT NULL default '0',
    PRIMARY KEY (`pickup_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


    ALTER TABLE `{$this->getTable('fedex_pickup')}` ADD INDEX ( `shipment_id` );

    ALTER TABLE `{$this->getTable('fedex_pickup')}` ADD FOREIGN KEY ( `shipment_id` ) REFERENCES `{$this->getTable('sales_flat_shipment')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE ;
");





$installer->run("

    -- DROP TABLE IF EXISTS {$this->getTable('fedex_pickup_shipment')};
    CREATE TABLE {$this->getTable('fedex_pickup_shipment')} (
    `pickup_shipment_id` int(11) unsigned NOT NULL auto_increment,
    `shipment_id` int(11) NULL ,
    `status` smallint(6) NOT NULL default '0',
    `confnum` smallint(6) NOT NULL default '0',
    PRIMARY KEY (`pickup_shipment_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


    ALTER TABLE `{$this->getTable('fedex_pickup_shipment')}` ADD INDEX ( `shipment_id` );

    
");






$installer->endSetup();