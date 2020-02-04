<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */
class Amasty_Feed_Model_Category extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('amfeed/category');
    }

    function saveCategoriesMapping()
    {
        Mage::getResourceSingleton('amfeed/category_mapping')->saveCategoriesMapping($this, $this->getData("mapping"));
    }

    protected function _afterLoad()
    {
        $collection = Mage::getSingleton('amfeed/category_mapping')->getCategoriesMappingCollection($this);
        if (!$this->getData('mapping')){
            $mapping = array();
            foreach($collection as $mappedCategory){
                $mapping[$mappedCategory->getCategoryId()] = array(
                    'name' => $mappedCategory->getVariable()
                );
            }
            $this->setData('mapping', $mapping);
        }

        parent::_afterLoad();
    }
}