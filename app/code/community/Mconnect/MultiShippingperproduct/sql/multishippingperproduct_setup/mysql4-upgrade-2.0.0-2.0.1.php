<?php
/**
 * M-Connect Solutions.
 *
 * NOTICE OF LICENSE
 *

 *
 * @category   Catalog
 * @package    Mconnect_MultiShippingperproduct
 * @author     M-Connect Solutions (http://www.mconnectsolutions.com, http://www.mconnectmedia.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
$installer = $this;
$installer->startSetup();
$installer->addAttribute('catalog_product', "multishipping_rate",  array(
    "type"     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    "backend"  => "",
    "frontend" => "",
    "label"    => "M-Connect Flat Rate Shipping",
    'input'    => 'price',
    "class"    => "",
    "source"   => "",
    'group' => 'M-Connect Custom Shipping',
    "global"   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    "visible"  => true,
    "required" => false,
    "default" => "",
    "searchable" => false,
    "filterable" => false,
    "comparable" => false,	
    "visible_on_front"  => true,
    "used_in_product_listing" => true,
    "unique"     => false));
    
$installer->addAttribute('catalog_product', "expedite_shipping_rate",  array(
    "type"     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    "backend"  => "",
    "frontend" => "",
    "label"    => "Expedite Flat Rate Shipping",
    'input'    => 'price',
    "class"    => "",
    "source"   => "",
    'group' => 'M-Connect Custom Shipping',
    "global"   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    "visible"  => true,
    "required" => false,
    "default" => "",
    "searchable" => false,
    "filterable" => false,
    "comparable" => false,    
    "visible_on_front"  => true,
    "used_in_product_listing" => true,
    "unique"     => false));

    
$installer->endSetup();