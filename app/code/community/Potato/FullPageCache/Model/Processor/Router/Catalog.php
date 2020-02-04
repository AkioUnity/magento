<?php

class Potato_FullPageCache_Model_Processor_Router_Catalog extends Potato_FullPageCache_Model_Processor_Router_Default
{
    /**
     * @return $this|bool
     */
    public function beforeLayoutGenerateBlocks()
    {
        if (Mage::app()->getRequest()->getControllerName() == 'category') {
            return $this->_registerCurrentCategory();
        }
        return $this->_registerCurrentProduct();
    }

    /**
     * mage register current category
     *
     * @return $this
     */
    protected function _registerCurrentCategory()
    {
        if (Mage::registry('current_category')) {
            return $this;
        }
        $categoryId = (int) Mage::app()->getRequest()->getParam('id', false);
        if (!$categoryId) {
            return $this;
        }
        $category = Mage::getModel('catalog/category')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($categoryId)
        ;
        Mage::register('current_category', $category, true);
        return $this;
    }

    /**
     * mage register current product and category
     *
     * @return $this|bool
     */
    protected function _registerCurrentProduct()
    {
        if (Mage::registry('current_product')) {
            return $this;
        }
        $categoryId = (int) Mage::app()->getRequest()->getParam('category', false);
        $productId  = (int) Mage::app()->getRequest()->getParam('id');
        if (!$productId) {
            return false;
        }
        $product = Mage::getModel('catalog/product')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($productId)
        ;
        $category = null;

        if ($categoryId) {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            $product->setCategory($category);
            if (!Mage::registry('current_category')) {
                Mage::register('current_category', $category, true);
            }
        } elseif ($categoryId = Mage::getSingleton('catalog/session')->getLastVisitedCategoryId()) {
            if ($product->canBeShowInCategory($categoryId)) {
                $category = Mage::getModel('catalog/category')->load($categoryId);
                $product->setCategory($category);
                if (!Mage::registry('current_category')) {
                    Mage::register('current_category', $category, true);
                }
            }
        }
        Mage::register('current_product', $product, true);
        Mage::register('product', $product, true);
        return $this;
    }

    /**
     * @return $this
     */
    public function dispatchEvents()
    {
        if (Mage::app()->getRequest()->getControllerName() == 'product') {
            $this->_registerCurrentProduct();
            //report viewed stat
            Mage::dispatchEvent('catalog_controller_product_view', array('product' => Mage::registry('current_product')));
        }
        return $this;
    }
}