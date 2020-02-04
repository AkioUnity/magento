<?php

class Potato_FullPageCache_Model_Observer
{
    /**
     * set block frames in block html
     *
     * @param $observer
     *
     * @return $this
     */
    public function setFrameTags($observer)
    {
        if (!Potato_FullPageCache_Helper_Data::canCache() || Potato_FullPageCache_Helper_Data::isUpdater()) {
            return $this;
        }
        $block = $observer->getBlock();
        if (Potato_FullPageCache_Model_Cache::getIsCanCache($block->getNameInLayout())) {
            return $this;
        }
        $pageCache = Potato_FullPageCache_Model_Cache::getPageCache();
        $pageCache->setFrameTags($block);
        return $this;
    }

    /**
     * cache skipped blocks content
     *
     * @param $observer
     *
     * @return $this
     */
    public function cacheSkippedBlocks($observer)
    {
        if (!Mage::app()->useCache('po_fpc')) {
            return $this;
        }
        $block = $observer->getBlock();
        $transport = $observer->getTransport();
        if (!Potato_FullPageCache_Model_Cache::getIsCanCache($block->getNameInLayout())) {
            Potato_FullPageCache_Helper_CacheBlock::saveSkippedBlockCache(
                array(
                    'html'           => $transport->getHtml(),
                    'name_in_layout' => $block->getNameInLayout()
                ),
                $block->getNameInLayout()
            );
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function cleanCache()
    {
        $tags = Mage::app()->getRequest()->getParam('types', array());
        if (in_array('po_fpc', $tags)) {
            $this->flushCache();
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function flushCache()
    {
        Potato_FullPageCache_Model_Cache::clean();
        return $this;
    }

    public function flushMediaCache()
    {
        Potato_FullPageCache_Model_Cache::clean();
        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function cacheResponse(Varien_Event_Observer $observer)
    {
        if (!Potato_FullPageCache_Helper_Data::canCache()) {
            if (isset($_GET['___store'])) {
                //set store cookie
                Potato_FullPageCache_Model_Cache::setStoreCookie(Mage::app()->getStore()->getId());//save current store id
            }
            return $this;
        }
        //save current store id
        Potato_FullPageCache_Model_Cache::setStoreCookie(Mage::app()->getStore()->getId());

        //save current customer group id
        Potato_FullPageCache_Model_Cache::setCustomerGroupCookie(Mage::getSingleton('customer/session')->getCustomerGroupId());

        //save mage config
        Potato_FullPageCache_Model_Cache::saveMageConfigXml();

        //save store id
        Potato_FullPageCache_Helper_CacheStore::saveStoreByRequest();
        $response = $observer->getEvent()->getResponse();
        $pageCache = Potato_FullPageCache_Model_Cache::getPageCache();
        $content = $response->getBody();
        $content = str_replace('/' . Mage::getSingleton('core/session')->getFormKey() . '/', '/PO_FPC_FORM_KEY/', $content);
        //save response body
        $pageCache->save($content, null, Potato_FullPageCache_Helper_Data::getCacheTags());

        //set response
        $response->setBody($response->getBody());
        return $this;
    }

    /**
     * save current cms - used for cache tags
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function registerCmsPage(Varien_Event_Observer $observer)
    {
        if (Mage::app()->useCache('po_fpc')) {
            Mage::register('current_cms', $observer->getEvent()->getPage(), true);
        }
        return $this;
    }

    /**
     * update remove cached router blocks by product events
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function updateProductCache(Varien_Event_Observer $observer)
    {
        if (Mage::app()->useCache('po_fpc')) {
            //get product id
            $productId = (int) Mage::app()->getRequest()->getParam('product');
            if ($observer->getProduct()) {
                $productId = $observer->getProduct()->getId();
            } elseif ($observer->getItem()) {
                $productId = $observer->getItem()->getProductId();
            }
            $this->_removeProductCache($productId);
        }
        return $this;
    }

    protected function _removeProductCache($productId)
    {
        //remove cache by tags
        Potato_FullPageCache_Model_Cache::cleanByTags(
            array(
                Potato_FullPageCache_Model_Cache::BLOCK_TAG,
                Potato_FullPageCache_Model_Cache::PRODUCT_TAG,
                $productId
            )
        );
        return $this;
    }

    public function removeProductPageCache(Varien_Event_Observer $observer)
    {
        if (Mage::app()->useCache('po_fpc')) {
            //remove cache by tags
            Potato_FullPageCache_Model_Cache::cleanByTags(
                array(
                    Potato_FullPageCache_Model_Cache::PRODUCT_TAG,
                    $observer->getProduct()->getId()
                )
            );
        }
        return $this;
    }

    /**
     * update remove cached session blocks by product events
     *
     * @return $this
     */
    public function updateSessionBlocksCache()
    {
        if (Mage::app()->useCache('po_fpc')) {
            //remove cache by tags
            Potato_FullPageCache_Model_Cache::cleanByTags(array(Potato_FullPageCache_Model_Processor_Block_Session::getId()));
        }
        return $this;
    }

    public function updateProductCacheByReview(Varien_Event_Observer $observer)
    {
        if (Mage::app()->useCache('po_fpc')) {
            $this->_removeProductCache($observer->getEvent()->getDataObject()->getData('entity_pk_value'));
        }
        return $this;
    }

    /**
     * update cached categories page by event
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function removeCategoryPageCache(Varien_Event_Observer $observer)
    {
        if (Mage::app()->useCache('po_fpc')) {
            //remove cache by tags
            Potato_FullPageCache_Model_Cache::cleanByTags(
                array(
                    Potato_FullPageCache_Model_Cache::CATEGORY_TAG,
                    $observer->getCategory()->getId()
                )
            );
        }
        return $this;
    }

    /**
     * update cached cms page by event
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function removeCmsPageCache(Varien_Event_Observer $observer)
    {
        if (Mage::app()->useCache('po_fpc')) {
            //remove cache by tags
            Potato_FullPageCache_Model_Cache::cleanByTags(
                array(
                    Potato_FullPageCache_Model_Cache::CMS_TAG,
                    $observer->getEvent()->getObject()->getId()
                )
            );
        }
        return $this;
    }

    /**
     * update customer group cookie after customer login logout
     *
     * @return $this
     */
    public function updateCustomerGroupCookie()
    {
        Potato_FullPageCache_Model_Cache::setCustomerGroupCookie(Mage::getSingleton('customer/session')->getCustomerGroupId());
        return $this;
    }

    /**
     * @return $this
     */
    public function cleanProductCache()
    {
        Potato_FullPageCache_Model_Cache::cleanByTags(
            array(Potato_FullPageCache_Model_Cache::BLOCK_TAG, Potato_FullPageCache_Model_Cache::PRODUCT_TAG)
        );
        return $this;
    }

    /**
     * @return $this
     */
    public function cleanCategoryCache()
    {
        Potato_FullPageCache_Model_Cache::cleanByTags(
            array(Potato_FullPageCache_Model_Cache::BLOCK_TAG, Potato_FullPageCache_Model_Cache::CATEGORY_TAG)
        );
        return $this;
    }

    /**
     * @return $this
     */
    public function cleanSystemCache()
    {
        Potato_FullPageCache_Model_Cache::cleanByTags(array(Potato_FullPageCache_Model_Cache::SYSTEM_TAG));
        return $this;
    }
}