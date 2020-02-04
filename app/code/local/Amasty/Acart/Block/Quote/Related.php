<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */

    class Amasty_Acart_Block_Quote_Related extends Mage_Catalog_Block_Product_List_Related
    {
        protected function _prepareData()
        {
            if ($this->getQuote()){
                $itemsPrices = array();
                foreach($quoteItems = $this->getQuote()->getAllVisibleItems() as $item){
                    $itemsPrices[$item->getPrice()] = $item->getProductId();

                }
            }
            ksort($itemsPrices);

            if (count($itemsPrices) > 0) {
                $product = Mage::getModel('catalog/product')->load(end($itemsPrices));

                /* @var $product Mage_Catalog_Model_Product */

                $this->_itemCollection = $product->getRelatedProductCollection()
                    ->addAttributeToSelect('required_options')
                    ->setPositionOrder()
                    ->addStoreFilter()
                ;

                if (Mage::helper('catalog')->isModuleEnabled('Mage_Checkout')) {
                    Mage::getResourceSingleton('checkout/cart')->addExcludeProductFilter($this->_itemCollection,
                        Mage::getSingleton('checkout/session')->getQuoteId()
                    );
                    $this->_addProductAttributesAndPrices($this->_itemCollection);
                }
        //        Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($this->_itemCollection);
                Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($this->_itemCollection);

                $this->_itemCollection->load();

                foreach ($this->_itemCollection as $product) {
                    $product->setDoNotUseCategoryId(true);
                }
            }


            return $this;
        }
    }