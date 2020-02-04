<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */  
class Amasty_Feed_Model_Mysql4_Product_Collection extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
{
    protected $_feed;
    
    function getFeed(){
        return $this->_feed;
    }


    protected $_skipAttributes = array(
        "sku", "tax_class_id"
    );
    
    public function init($feed){
        $this->_feed = $feed;
    }
    
    protected function _prepareConditionValue($value, $order){
        $condVal = $value['condition']['value'][$order];
        
        $operator = $value['condition']['operator'][$order];
        
        if ($operator == 'like' || $operator == 'nlike'){
            
            if (strpos($condVal, "%") === FALSE){
                $condVal = "%" . $condVal . "%";
            }
        }
        
        $repl = array(
            "%now%" => date("Y-m-d H:i:s"),
            "%today%" => date("Y-m-d")
        );
        
        return strtr($condVal, $repl);
    }
    
    protected function _getDummyCollection($config){
        
        $hlrAttribute = Mage::helper("amfeed/attribute");
        
        $attributesFields = array();
                
        $dummyCollection = Mage::getResourceModel('amfeed/product_collection');

        foreach($config['condition']['type'] as $order => $type){
            $code = $config['condition']['attribute'][$order];
            $operator = $config['condition']['operator'][$order];
            $condVal = $this->_prepareConditionValue($config, $order);

            if ($operator == 'isempty'){
                
                $operator = 'null';
                $condVal = true;
                
            } else if ($operator == 'isnotempty') {
                $operator = 'notnull';
                $condVal = true;
            }

            if ($type == Amasty_Feed_Model_Filter::$_TYPE_ATTRIBUTE){

                $attributesFields[] = array(
                    'attribute' => $code, 
                    $operator => $condVal
                );
                
            } else if ($type == Amasty_Feed_Model_Filter::$_TYPE_OTHER){
                $attribute = $hlrAttribute->getCompoundAttribute($code);
                $attribute->prepareCondition($dummyCollection, $operator, $condVal, $attributesFields);
            }
        }
        $dummyCollection->addAttributeToFilter($attributesFields, null, 'left');
        
        return $dummyCollection;
    }

    protected function _addBaseConditions(){
        
        $storeId = $this->getFeed()->getStoreId();
        
        if ($this->getFeed()->getCondDisabled())
        {
            $this->addAttribute("status", $storeId);
            
            $this->addAttributeToFilter(array(array(
                    "attribute" => 'status',
                    'eq' => 1
            )), null);
        }
        
        if ($this->getFeed()->getCondStock()) {
            Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($this);
        }
        
        $this->setStoreId($storeId);
        $this->addStoreFilter($storeId);

//        if (!Mage::app()->isSingleStoreMode()) {
//            $this->joinField('amfilter_store_id', 'catalog/category_product_index', 'store_id', 'product_id=entity_id', '{{table}}.store_id = ' . $storeId, 'inner');
//        }

        $this->_addProdTypeCondition();
        $this->_addAttributeSetsCondition();
    }
    
    protected function _addProdTypeCondition(){
        $types = array();
        
        $condType = $this->getFeed()->getCondType();
        
        if (!empty($condType)){
            $types = explode(",", $condType);
        }
        
        if (count($types) > 0) {
            $this->addFieldToFilter("type_id", array(
                "in" => $types
            ));
        }
    }
    
    protected function _addAttributeSetsCondition(){
       $attributeSets = array();
        
       $condAttributeSets = $this->getFeed()->getCondAttributeSets();
       
       if (!empty($condAttributeSets)){
            $attributeSets = explode(",", $condAttributeSets);
       }
       
       if (count($attributeSets) > 0) {
            $this->addFieldToFilter("attribute_set_id", array(
                "in" => $attributeSets
            ));
       }
    }
    
    function addConditions(){
        
        $this->_addBaseConditions();
        
        $condition = $this->getFeed()
                ->getCondition();
        
        $from = array();
        $where = array();
        foreach($condition as $config){
            if (is_array($config['condition']) &&
                    is_array($config['condition']['type'])){
        
                $dummyCollection = $this->_getDummyCollection($config);
                
                $from = array_merge($from, $dummyCollection->getSelect()->getPart(Zend_Db_Select::FROM));
                $where = array_merge($where, $dummyCollection->getSelect()->getPart(Zend_Db_Select::WHERE));
            }
        }
        
        $from = array_merge($from, $this->getSelect()->getPart(Zend_Db_Select::FROM));
        
        $this->getSelect()->setPart(Zend_Db_Select::FROM, $from);
        
        foreach($where as $w){
            $this->getSelect()->where($w);
        }
        $this->getSelect()->group('e.entity_id');
    }

    function addAttribute($code, $storeId){

        if (!$this->_checkJoin($code) && !in_array($code, $this->_skipAttributes))
        {
            switch ($code){
//                case "special_price":
                case "minimal_price":
                case "price":
                    $this->joinPrice();
                    break;
                default:
                    $this->joinAttribute($code, 'catalog_product/' . $code, 'entity_id', null, 'left', $storeId);
                    break;
            }
        }
        
    }
    
    public function addParentIdToSelect()
    {
        $this->getSelect()
             ->joinLeft(array('relation_table' => $this->getTable('catalog/product_relation')),
                'relation_table.child_id = e.entity_id',
                        array('parent_id' => 'relation_table.parent_id'));
    }
    
    protected function _checkJoin($alias){
        $magentoVersion = Mage::getVersion();
        if (version_compare($magentoVersion, '1.6', '>=')) {
            $from = $this->getSelect()->getPart(Zend_Db_Select::FROM);
            return isset($from['at_' . $alias]);
        } else {
            $from = $this->getSelect()->getPart(Zend_Db_Select::FROM);
            return isset($from['_table_' . $alias]);
        }
    }
    
    public function joinCategories(){
        if (!$this->_checkJoin('category_id')){
            $this->joinField('category_id', 'catalog/category_product', 'category_id', 'product_id=entity_id', null, 'left');
            $this->getSelect()->columns("GROUP_CONCAT(at_category_id.category_id) as category_ids");
//            $this->joinField('category_id', 'catalog/category_product_index', 'category_id', 'product_id = entity_id', '{{table}}.store_id = ' . $this->getStoreId(), 'left');
        }
    }
    
    public function joinIsInStock(){
        if (!$this->_checkJoin('amfeed_is_in_stock')){
            $this->joinTable(
                array('at_amfeed_is_in_stock' => 'cataloginventory/stock_item'),
                    'product_id=entity_id',
                array(
                    'is_in_stock', 'manage_stock', 'use_config_manage_stock',
                    'qty_increments',  'use_config_qty_increments',
                    'enable_qty_increments', 'use_config_enable_qty_inc'
                ),
                null, 'left'
            );
        }
    }
    
    public function joinQty(){
        if (!$this->_checkJoin('qty')){
            $this->joinField('qty',
                 'cataloginventory/stock_item',
                 'qty',
                 'product_id=entity_id',
                 '{{table}}.stock_id=1',
                 'left');
        }
    }
    
    public function joinPrice(){
        if (!$this->_checkJoin('attribute_price')){
            $this->joinAttribute('attribute_price', 'catalog_product/price' , 'entity_id', null, 'left', $this->getStoreId());
            
            $this->addPriceData();
        }
    }
    
    protected function _productLimitationPrice($joinLeft = false)
    {
        $defaultPrice = null;
        foreach($this->getSelect()->getPart(Zend_Db_Select::COLUMNS) as $columnEntry){

            list($correlationName, $column, $alias) = $columnEntry;

            if ($alias == 'attribute_price'){

                if (strpos(strtolower($column), 'if') === FALSE){
                $defaultPrice = $correlationName . '.' . $column;
                } else {
                    $defaultPrice = $column;
                }

            }

        }

        $filters = $this->_productLimitationFilters;
        if (empty($filters['use_price_index']) || !$defaultPrice) {
            return $this;
        }

        $connection = $this->getConnection();
        $select     = $this->getSelect();
        $joinCond   = join(' AND ', array(
            'price_index.entity_id = e.entity_id',
            $connection->quoteInto('price_index.website_id = ?', $filters['website_id']),
            $connection->quoteInto('price_index.customer_group_id = ?', $filters['customer_group_id'])
        ));

        $fromPart = $select->getPart(Zend_Db_Select::FROM);
        if (!isset($fromPart['price_index'])) {
            $minExpr = $this->getCheckSql('price_index.min_price IS NOT NULL',
                            'price_index.min_price', $defaultPrice);

            $maxExpr = $this->getCheckSql('price_index.max_price IS NOT NULL',
                            'price_index.max_price', $defaultPrice);

            $maxExpr = $this->getCheckSql('price_index.max_price IS NOT NULL',
                            'price_index.max_price', $defaultPrice);

            $finalExpr = $this->getCheckSql('price_index.final_price IS NOT NULL',
                            'price_index.final_price', $defaultPrice);

            $priceExpr = $this->getCheckSql('price_index.price IS NOT NULL',
                            'price_index.price', $defaultPrice);

            $colls  = array(
                'tax_class_id',
                'final_price' => $finalExpr,
                'min_price' => $minExpr,
                'max_price' => $maxExpr,
                'default_price' => $priceExpr,
                'price' => $priceExpr
            );

            $tableName = array('price_index' => $this->getTable('catalog/product_index_price'));
            if ($joinLeft) {
                $select->joinLeft($tableName, $joinCond, $colls);
            } else {
                $select->join($tableName, $joinCond, $colls);
            }
            // Set additional field filters
            foreach ($this->_priceDataFieldFilters as $filterData) {
                $select->where(call_user_func_array('sprintf', $filterData));
            }
        } else {
            $fromPart['price_index']['joinCondition'] = $joinCond;
            $select->setPart(Zend_Db_Select::FROM, $fromPart);
        }
        //Clean duplicated fields
        $magentoVersion = Mage::getVersion();
        if (version_compare($magentoVersion, '1.6', '>=')) {
            $helper = Mage::getResourceHelper('core');
            $helper->prepareColumnsList($select);
        } else {
            Mage::getModel('amfeed/version')->prepareColumnsList($select);
        }


        return $this;
    }

    public function addPriceData($customerGroupId = null, $websiteId = null)
    {
        
        $this->_productLimitationFilters['use_price_index'] = true;

        $customerGroupId = 0;
        /*****************NOT WORKING BY CRON************************/
//        if (!isset($this->_productLimitationFilters['customer_group_id']) && is_null($customerGroupId)) {
//            $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
//        }
        
        if (!isset($this->_productLimitationFilters['website_id']) && is_null($websiteId)) {
            $websiteId       = Mage::app()->getStore($this->getStoreId())->getWebsiteId();
        }

        if (!is_null($customerGroupId)) {
            $this->_productLimitationFilters['customer_group_id'] = $customerGroupId;
        }
        if (!is_null($websiteId)) {
            $this->_productLimitationFilters['website_id'] = $websiteId;
        }

        $this->_applyProductLimitations();

        return $this;
    }
    
    protected function _productLimitationJoinPrice()
    {
        return $this->_productLimitationPrice(true);
    }
    
    public function joinTaxPercents($store)
    {
        $countryId = Mage::getStoreConfig('general/country/default', $store->getStoreId());
        $this->joinPrice();
            
        if (!$this->_checkJoin('tax_table')){

            $this->getSelect()
                ->joinLeft(array('tax_table' => $this->getTable('tax/tax_calculation')),
                           'tax_table.product_tax_class_id = price_index.tax_class_id',
                           array())
                ->joinLeft(array('rate_table' => $this->getTable('tax/tax_calculation_rate')),
                           'rate_table.tax_calculation_rate_id = tax_table.tax_calculation_rate_id',
                           array('tax_percents' => 'rate_table.rate'));
            $this->getSelect()->where('rate_table.tax_country_id = ?', $countryId);
        }
    }

    public function getUrlDataL17($storeId, $useCategory)
    {
        $urlRewrites = array();

        $ids = "select DISTINCT entity_id from (" . $this->getSelect()->__toString() . ") as tmp";

        $select = $this->getConnection()->select()
            ->from($this->getTable('core/url_rewrite'), array('product_id', 'request_path'))
            ->where('store_id = ?', $storeId)
            ->where('is_system = ?', 1)
            ->order('category_id ' . self::SORT_ORDER_DESC);

        $from = $select->getPart(Zend_Db_Select::FROM);

        $from['products'] = array(
            'joinType' => 'inner join',
            'schema' => null,
            'tableName' => new Zend_Db_Expr('(' . $ids . ')'),
            'joinCondition' => 'products.entity_id = product_id'
        );

        $select->setPart(Zend_Db_Select::FROM, $from);

        if (!$useCategory){
            $select->where('category_id IS NULL');
        }

        foreach ($this->getConnection()->fetchAll($select) as $row) {
            if (!isset($urlRewrites[$row['product_id']])) {
                $urlRewrites[$row['product_id']] = $row['request_path'];
            }
        }

        return $urlRewrites;
    }

    public function getUrlData($storeId, $useCategory)
    {
        if (version_compare(Mage::getVersion(), '1.9.0.0', '<=')
            || !class_exists('Mage_Catalog_Model_Factory')) {
            return $this->getUrlDataL17($storeId, $useCategory);
        }

        $factory = Mage::getSingleton('catalog/factory');

        $urlRewrites = array();

        $ids = "select DISTINCT entity_id from (" . $this->getSelect()->__toString() . ") as tmp";

        $store = Mage::app()->getStore($this->getStoreId());

        $select = $this->getConnection()->select()
            ->from(array('main_table' => $this->getTable('catalog/product')), array('entity_id'))
            ->join(
                array('w' => $this->getTable('catalog/product_website')),
                'main_table.entity_id = w.product_id',
                array()
            )
            ->where('w.website_id=?', $store->getWebsiteId());


        $storeId = (int)$store->getId();

        /** @var $urlRewrite Mage_Catalog_Helper_Product_Url_Rewrite_Interface */
        $urlRewrite = $factory->getProductUrlRewriteHelper();
        $urlRewrite->joinTableToSelect($select, $storeId);

        $from = $select->getPart(Zend_Db_Select::FROM);



        $from['products'] = array(
            'joinType' => 'inner join',
            'schema' => null,
            'tableName' => new Zend_Db_Expr('(' . $ids . ')'),
            'joinCondition' => 'products.entity_id = main_table.entity_id'
        );

        if (isset($from['url_rewrite']) && $useCategory){
            $from['url_rewrite']['joinCondition'] = str_replace("url_rewrite.category_id IS NULL AND", "", $from['url_rewrite']['joinCondition']);
        }


        $select->setPart(Zend_Db_Select::FROM, $from);

        $entities = array();

        foreach ($this->getConnection()->fetchAll($select) as $row) {

            $entity = new Varien_Object();
            $entity->setId($row['entity_id']);
            $entity->setUrl(!empty($row['request_path']) ? $row['request_path'] : 'catalog/product/view/id/' . $entity->getId());

            $entities[$entity->getId()] = $entity;
        }


        $products = new Varien_Object();
        $products->setItems($entities);
        Mage::dispatchEvent('sitemap_products_generating_before', array(
            'collection' => $products
        ));

        foreach ($products->getItems() as $item) {
            if (!isset($urlRewrites[$item->getId()])) {
                $urlRewrites[$item->getId()] = $item->getUrl();
            }
        }

        return $urlRewrites;
    }

    public function getCountProducts()
    {
        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::GROUP);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);
        $countSelect->columns('COUNT(DISTINCT e.entity_id)');
        $total = $this->getConnection()->fetchOne($countSelect);
        return intval($total);
    }
    
    public function isEnabledFlat()
    {
        return false;
    }

    public function getCheckSql($expression, $true, $false)
    {
        if ($expression instanceof Zend_Db_Expr || $expression instanceof Zend_Db_Select) {
            $expression = sprintf("IF((%s), %s, %s)", $expression, $true, $false);
        } else {
            $expression = sprintf("IF(%s, %s, %s)", $expression, $true, $false);
        }

        return new Zend_Db_Expr($expression);
    }

//    protected function _productLimitationJoinWebsite()
//        {
//
//        }
}