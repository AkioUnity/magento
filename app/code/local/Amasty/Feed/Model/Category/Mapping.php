<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */
class Amasty_Feed_Model_Category_Mapping extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('amfeed/category_mapping');
    }

    public function getCategoriesMappingCollection($category)
    {
        $collection = $this->getCollection()
            ->addFieldToFilter(
            'feed_category_id',
            $category->getId()
        );

        return $collection;
    }

}