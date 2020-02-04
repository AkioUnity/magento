<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */
class Amasty_Feed_Model_Mysql4_Category_Mapping extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('amfeed/category_mapping', 'entity_id');
    }

    public function saveCategoriesMapping($feedMapper, $data)
    {
        $connection = $this->_getWriteAdapter();

        if (is_array($data)) {
            foreach($data as $categoryId => $item){
                $connection->delete(
                    $this->getMainTable(),
                    array('feed_category_id = ?' => $feedMapper->getId(), 'category_id = ?' => $categoryId)
                );

                if (isset($item['name']) && !empty($item['name'])) {
                    $bind = array(
                        'feed_category_id' => $feedMapper->getId(),
                        'category_id' => $categoryId,
                        'variable' => $item['name']
                    );
                    $connection->insert($this->getMainTable(), $bind);
                }
            }
        }

    }
}