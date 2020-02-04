<?php
class eClickM_AdminGridCategoryFilter_Model_Observer
{

    public function addCategoryFilterToProductGrid(Varien_Event_Observer $observer)
    {   
        $block = $observer->getEvent()->getBlock();
        if( ($block instanceof Mage_Adminhtml_Block_Catalog_Product_Grid)  ) {
            $block->addColumnAfter('eclickm_category_list', array(
                    'header'    => Mage::helper('admingridcategoryfilter')->__('Category'),
                    'index'     => 'eclickm_category_list',
                    'sortable'  => false,
                    'width' => '250px',
                    'type'  => 'options',
                    'options'   => Mage::getSingleton('admingridcategoryfilter/system_config_source_category')->toOptionArray(),
                    'renderer'  => 'admingridcategoryfilter/catalog_product_grid_render_category',
                    'filter_condition_callback' => array($this, 'filterCallback'),
            ),'name');
        }
    }

    public function filterCallback($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        $_category = Mage::getModel('catalog/category')->load($value);
        $collection->addCategoryFilter($_category);

        return $collection;
    }

}