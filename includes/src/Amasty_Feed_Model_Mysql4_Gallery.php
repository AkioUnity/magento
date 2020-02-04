<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */  
class Amasty_Feed_Model_Mysql4_Gallery extends Mage_Core_Model_Mysql4_Abstract
{
    const GALLERY_TABLE       = 'catalog/product_attribute_media_gallery';
    const GALLERY_VALUE_TABLE = 'catalog/product_attribute_media_gallery_value';
    const GALLERY_IMAGE_TABLE = 'catalog/product_attribute_media_gallery_image';

    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init(self::GALLERY_TABLE, 'value_id');
    }
    
    public function loadGallery($attributeId, $storeId, $collection)
    {
        $ids = "select DISTINCT entity_id from (" . $collection->getSelect()->__toString() . ") as tmp";
        
        $adapter = $this->_getReadAdapter();

        $positionCheckSql = $adapter->getCheckSql('value.position IS NULL', 'default_value.position', 'value.position');

        // Select gallery images for product
        $select = $adapter->select()
            ->from(
                array('main'=>$this->getMainTable()),
                array('value_id', 'value AS file', 'entity_id as product_id')
            )
            ->joinLeft(
                array('value' => $this->getTable(self::GALLERY_VALUE_TABLE)),
                $adapter->quoteInto('main.value_id = value.value_id AND value.store_id = ?', (int)$storeId),
                array('label','position','disabled')
            )
            ->joinLeft( // Joining default values
                array('default_value' => $this->getTable(self::GALLERY_VALUE_TABLE)),
                'main.value_id = default_value.value_id AND default_value.store_id = 0',
                array(
                    'label_default' => 'label',
                    'position_default' => 'position',
                    'disabled_default' => 'disabled'
                )
            )
            ->where('main.attribute_id = ?', $attributeId)
            ->where('main.entity_id IN (' . $ids . ')')
            ->order($positionCheckSql . ' ' . Varien_Db_Select::SQL_ASC);

        $result = $adapter->fetchAll($select);
        
        return $result;
    }
}