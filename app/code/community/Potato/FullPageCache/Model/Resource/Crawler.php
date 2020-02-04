<?php

class Potato_FullPageCache_Model_Resource_Crawler extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('core/url_rewrite', 'url_rewrite_id');
    }

    /**
     * @param $storeId
     * @param $limit
     * @param $offset
     *
     * @return mixed
     */
    public function getRequestPaths($storeId, $limit, $offset)
    {
        if (@class_exists('Enterprise_UrlRewrite_Model_Resource_Url_Rewrite', false)) {
            return $this->_getEERequestPaths($storeId, $limit, $offset);
        }
        $select =
            $this->_getReadAdapter()->select()
                ->from(
                    $this->getTable('core/url_rewrite')
                    ,array('store_id', 'request_path')
                )
                ->where('store_id=?', $storeId)
                ->where('is_system=1')
                ->limit($limit, $offset)
        ;
        return $this->_getReadAdapter()->fetchAll($select);
    }

    /**
     * @param $storeId
     * @param $limit
     * @param $offset
     *
     * @return mixed
     */
    protected function _getEERequestPaths($storeId, $limit, $offset)
    {
        $store = Mage::app()->getStore($storeId);

        $rootCategoryId = $store->getRootCategoryId();

        $selectProduct = $this->_getReadAdapter()->select()
            ->from(array('url_product_default' => $this->getTable('enterprise_catalog/product')),
                array(''))
            ->joinInner(array('url_rewrite' => $this->getTable('enterprise_urlrewrite/url_rewrite')),
                'url_rewrite.url_rewrite_id = url_product_default.url_rewrite_id',
                array('request_path', 'entity_type')
            )
            ->joinInner(array('cp' => $this->getTable('catalog/category_product_index')),
                'url_product_default.product_id = cp.product_id',
                array('category_id')
            )
            ->where('url_rewrite.entity_type = ?', Enterprise_Catalog_Model_Product::URL_REWRITE_ENTITY_TYPE)
            ->where('cp.store_id = ?', (int) $storeId)
            ->where('cp.category_id != ?', (int) $rootCategoryId)
            ->limit($limit, $offset)
        ;

        $selectCategory = $this->_getReadAdapter()->select()
            ->from(array('url_rewrite' => $this->getTable('enterprise_urlrewrite/url_rewrite')),
                array(
                    'request_path',
                    'entity_type',
                    'category_id' => new Zend_Db_Expr('NULL'),
                )
            )
            ->where('url_rewrite.store_id = ?', $storeId)
            ->where('url_rewrite.entity_type = ?', Enterprise_Catalog_Model_Category::URL_REWRITE_ENTITY_TYPE)
            ->limit($limit, $offset)
        ;

        $selectPaths = $this->_getReadAdapter()->select()
            ->union(array('(' . $selectProduct . ')', '(' . $selectCategory . ')'))
        ;
        return $this->_getReadAdapter()->fetchAll($selectPaths);
    }
}