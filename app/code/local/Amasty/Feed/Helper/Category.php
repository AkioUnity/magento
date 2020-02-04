<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */
class Amasty_Feed_Helper_Category extends Mage_Core_Helper_Abstract
{
    protected $_path = array();
    
    public function getOptionsForFilter()
    {
        $parentId       = Mage_Catalog_Model_Category::TREE_ROOT_ID;

        $category       = Mage::getModel('catalog/category');
        $parentCategory = $category->load($parentId);
        
        $this->_buildPath($parentCategory);

        $options = array();
        $options[0] = $this->__('- With no category');
        foreach ($this->_path as $i => $path)
        {
            $string = str_repeat(". ", max(0, ($path['level'] - 1) * 3)) . $path['name'];
            $options[$path['id']] = $string;
        }
        return $options;
    }
    
    protected function _buildPath($category)
    {
        if ($category->getName()) // main root category will have no name, so we'll not add it
        {
            $this->_path[] = array(
                'id'    => $category->getId(),
                'level' => $category->getLevel(),
                'name'  => $category->getName(),
            );
        }
        if ($category->hasChildren())
        {
            foreach ($this->getChildrenCategories($category) as $child)
            {
                $this->_buildPath($child);
            }
        }
    }
    
    public function getChildrenCategories($category)
    {
        $collection = $category->getCollection();
        /* @var $collection Mage_Catalog_Model_Resource_Category_Collection */
        $collection->addAttributeToSelect('url_key')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('all_children')
            ->addAttributeToSelect('is_anchor')
            ->addFieldToFilter('parent_id', $category->getId())
            ->setOrder('position', Varien_Db_Select::SQL_ASC)
            ->joinUrlRewrite()
            ->load();
        
        return $collection;
    }
}