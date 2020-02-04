<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */  
class Amasty_Feed_Model_Product_Collection extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
{
    protected $_addPrice = array();
    protected $_addUrl = false;
    protected $_addQty = false;
    protected $_addParentId = false;
    protected $_addIsInStock = false;
    protected $_addCategory = false;
    protected $_addTax = false;
    protected $_addStockAvailability = false;
    
    
    protected $_qtyConds = array();
    protected $_catConds = array();
    protected $_priceConds = array();
    protected $_taxConds = array();
    
    protected $_joined = array();
    
    protected function _joinCustomAttribute($attribute, $storeId){
        if (!in_array($attribute, $this->_joined)){
            $this->_joined[] = $attribute;
            $this->joinAttribute($attribute, 'catalog_product/' . $attribute, 'entity_id', null, 'left', $storeId);
        }
    }
    
    
    protected function _parseAttributeField($fields, $key, $storeId, $fieldAttr, &$attr, &$notJoin){
        
        if ('qty' == $fields[$fieldAttr][$key]) {
            $this->_addQty = true;
        } elseif ('parent_id' == $fields[$fieldAttr][$key]) {
            $this->_addParentId = true;
        } elseif ('url' == $fields[$fieldAttr][$key]) {
            $this->_addUrl = true;
        } elseif (in_array($fields[$fieldAttr][$key], array('final_price', 'min_price', 'tier_price', 'group_price'))) {
                $this->_addPrice[] = $fields[$fieldAttr][$key];
        } elseif ('tax_percents' == $fields[$fieldAttr][$key]) {
            $this->_addTax = true;
        } elseif ('is_in_stock' == $fields[$fieldAttr][$key]) {
            $this->_addIsInStock = true;
        } elseif (in_array($fields[$fieldAttr][$key], array('category_id', 'category_name', 'categories'))) {
            $this->_addCategory = true;
        } elseif ('stock_availability' == $fields[$fieldAttr][$key]) {
            $this->_addStockAvailability = true;
        } else if ('sale_price_effective_date' == $fields[$fieldAttr][$key]){
            $this->_joinCustomAttribute('special_from_date', $storeId);
//            if (!in_array($fields[$fieldAttr][$key], 'special_from_date')){
//                $attr[] = 'special_from_date';
//                $this->joinAttribute('special_from_date', 'catalog_product/special_from_date', 'entity_id', null, 'left', $storeId);
//            }
            $this->_joinCustomAttribute('special_to_date', $storeId);
//            if (!in_array($fields[$fieldAttr][$key], 'special_to_date')){
//                $attr[] = 'special_to_date';
//                $this->joinAttribute('special_to_date', 'catalog_product/special_to_date', 'entity_id', null, 'left', $storeId);
//            }
            
            
        } elseif ( !in_array($fields[$fieldAttr][$key], $notJoin)) {
            
            
            if ($fields[$fieldAttr][$key] == 'price' || $fields[$fieldAttr][$key] == 'special_price'){
                $this->_addPrice[] = 'min_price';
            }
            
            $this->_joinCustomAttribute($fields[$fieldAttr][$key], $storeId);
//            $attr[] = $fields[$fieldAttr][$key];
//            $this->joinAttribute($fields[$fieldAttr][$key], 'catalog_product/' . $fields[$fieldAttr][$key], 'entity_id', null, 'left', $storeId);
        }
        if (!in_array('price', $this->_addPrice)) {
            $this->_addPrice[] = 'price';
        }
        if (!in_array('min_price', $this->_addPrice)) {
            $this->_addPrice[] = 'min_price';
        }
    }
    
    protected function _parseCustomField($fields, $key, $storeId, &$notJoin){
                    $field = Mage::getModel('amfeed/profile')->getCustomField($fields['custom'][$key]);
                    
        
                            
        if (!in_array($field->getBaseAttr(), $notJoin)){
                    if ('qty' == $field->getBaseAttr()) {
                        $this->_addQty = true;
                    } elseif ('parent_id' == $field->getBaseAttr()) {
                        $this->_addParentId = true;
                    } elseif ('url' == $field->getBaseAttr()) {
                        $this->_addUrl = true;
                    } elseif (in_array($field->getBaseAttr(), array('final_price', 'min_price', 'tier_price'))) {
                        $this->_addPrice[] = $field->getBaseAttr();
                    } elseif ('tax_percents' == $field->getBaseAttr()) {
                        $this->_addTax = true;
                    } elseif ('is_in_stock' == $field->getBaseAttr()) {
                        $this->_addIsInStock = true;
                    } elseif (in_array($field->getBaseAttr(), array('category_id', 'category_name', 'categories'))) {
                        $this->_addCategory = true;
                    } elseif ('stock_availability' == $field->getBaseAttr()) {
                        $this->_addStockAvailability = true;
                    } else if ('sale_price_effective_date' == $field->getBaseAttr()){
                        $this->_joinCustomAttribute('special_from_date', $storeId);
                        $this->_joinCustomAttribute('special_to_date', $storeId);
                    } elseif ($field->getBaseAttr() && ('created_at' !== $field->getBaseAttr())) {
                        $this->_joinCustomAttribute($field->getBaseAttr(), $storeId);
                    } elseif (!$field->getBaseAttr()){
                        $regex = "#{(.*?)}#";
                        preg_match_all($regex, $field->getDefaultValue(), $mathes);
                        $attributes = $mathes[1];
                        foreach($attributes as $placeholder){
                            $attribute = Mage::helper('amfeed')->getCustomFieldAttribute($placeholder);
                            $attributeObj = $this->getAttribute($attribute);
                            if ($attributeObj){
                        if (!in_array($attribute, $notJoin))
                                $this->_joinCustomAttribute($attribute, $storeId);
                            }
                        }
                    }
        }
        
        
        $defValue = $field->getDefaultValue();
        
        $regex = "#{(.*?)}#";
        
        preg_match_all($regex, $defValue, $mathes);
        
        if (count($mathes[1]) > 0){
            $attributes = $mathes[1];
            foreach($attributes as $attribute){
                
                if (!in_array($attribute, $notJoin)){
                    
                    $this->_joinCustomAttribute($attribute, $storeId);
                }
            }
        }
        
        $this->_joinCustomAttributes($field, $storeId, $notJoin);
        
    }
    
    protected function _joinCustomAttributes($field, $storeId, $notJoin){
        $advancedAttributes = $field->getAdvencedAttributes();
        
        foreach($advancedAttributes as $attribute){
            if (!in_array($attribute, $notJoin)){
                $this->_joinCustomAttribute($attribute, $storeId);
            }
        }
        
        if ($field->hasCategory()){
            if (!array_key_exists('category_exact_id', $this->_joinFields)){
                $this->joinField('category_exact_id', 'catalog/category_product', 'category_id', 'product_id = entity_id', null, 'left');
            }
        }
        
    }
            
    public function parseFields($fields, $storeId)
    {
        $attr = array();
        $notJoin = array('entity_id', 'sku', 'created_at', 'type_id');
        $types = $fields['type'];
        foreach ($types as $key => $type) {
            switch ($type) {
                case 'attribute':
                    $this->_parseAttributeField($fields, $key, $storeId, 'attr', $attr, $notJoin);
                    break;
                case 'custom_field':
                    
                    $this->_parseCustomField($fields, $key, $storeId, $notJoin);
                    
                    break;
            }
        }
        
    }
    
    public function addBaseFilters($storeId, $disabled, $stock, $prodTypes)
    {
        $this->setStoreId($storeId);
        $this->addStoreFilter($storeId);
        
        if ($storeId){
//            $this->joinField('store_id', 'catalog_category_product_index', 'store_id', 'product_id=entity_id', '{{table}}.store_id = ' . $storeId, 'inner');
        }


        // exclude disabled products
        if ($disabled) {
//            $this->addAttributeToSelect('status');
//            $this->addFieldToFilter('status', array('eq' => '1'));
//            $this->addAttributeToFilter(
//                'status',
//                array('eq' => 1)
//            );
            $this->addAttributeToFilter(array(array(
                    "attribute" => 'status',
                    'eq' => 1
            )), null, 'inner');

        }
        
        
        // exclude `Out of Stock` products
        if ($stock) {
            $stockId = Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID;
            $websiteId = Mage::app()->getStore()->getWebsiteId();
            
            $this->joinTable(
                'cataloginventory/stock_status',
                'product_id=entity_id', 
                array("stock_status" => "stock_status"),
                null ,
                'left'
            );
            
            $this->addFieldToFilter('stock_status',array(
                "eq" => 1
            ));
            
        } elseif ($this->_addIsInStock) {
            
        }
        
        // product types filter
        $where = '';
        foreach ($prodTypes as $prodType) {
            if (0 == strlen($where)) {
                $where .= '( e.type_id = \''.$prodType.'\' )';
            } else {
                $where .= ' or ( e.type_id = \''.$prodType.'\' )';
            }
        }
        $this->getSelect()->where($where);
        $this->getSelect()->group('e.entity_id');
    }
    
    protected function _prepareConditionValue($value, $order){
        $condVal = $value['condition']['value'][$order];
        
        $repl = array(
            "%now%" => date("Y-m-d H:i:s"),
            "%today%" => date("Y-m-d")
        );
        
        return strtr($condVal, $repl);
    }
    
    public function parseAndAddAdvancedFilters($condition)
    {
        $from = array();
        $where = array();
        foreach($condition as $value){
            if (is_array($value['condition']) &&
                    is_array($value['condition']['type'])){
                
                $attributesFields = array();
                
                $dummyCollection = Mage::getModel('catalog/product')->getCollection();
                
                foreach($value['condition']['type'] as $order => $type){
                    $code = $value['condition']['attribute'][$order];
                    $operator = $value['condition']['operator'][$order];
                    $condVal = $this->_prepareConditionValue($value, $order);
                    
                    $condValEmpty = isset($value['condition']['empty']) ?
                        $value['condition']['empty'][$order] : null;
                    
                    if ($condValEmpty == 1){
                        if ($operator == 'eq'){
                            $operator = 'null';
                            $condVal = true;
                        } else {
                            $operator = 'notnull';
                            $condVal = true;
                        }
                    }
                    
                    if ($type == Amasty_Feed_Model_Filter::$_TYPE_ATTRIBUTE){
                        
                        $attributesFields[] = array(
                        'attribute' => $code, 
                        $operator => $condVal
                    );
                    } else if ($type == Amasty_Feed_Model_Filter::$_TYPE_OTHER){
                         
                        switch($code){
                            case Amasty_Feed_Block_Adminhtml_Control_Profile::$_OTHER_CONDITION_CATEGORY:
                                
                                if (!array_key_exists('category_index_id', $dummyCollection->_joinFields)){
                                    $dummyCollection->joinField('category_index_id', 'catalog/category_product_index', 'category_id', 'product_id = entity_id', null, 'left');
                                }
                                
                                $attributesFields[] = array(
                                    'attribute' => 'category_index_id', 
                                    $operator => $condVal
                                );
                               
                                break;
                            case Amasty_Feed_Block_Adminhtml_Control_Profile::$_OTHER_CONDITION_QTY:
                                if (!array_key_exists('qty_filter', $dummyCollection->_joinFields)){
                                    $dummyCollection
                                        ->joinField(
                                            'qty_filter',
                                            'cataloginventory/stock_item',
                                            'qty',
                                            'product_id=entity_id',
                                            '{{table}}.stock_id=1',
                                            'left'
                                        );
                                }
                                
                                $attributesFields[] = array(
                                    'attribute' => 'qty_filter', 
                                    $operator => $condVal
                                );
                                break;
                }
                    }
                }
                
                
                $dummyCollection->addAttributeToFilter($attributesFields, null, 'left');
                
                $from = array_merge($from, $dummyCollection->getSelect()->getPart(Zend_Db_Select::FROM));
                $where = array_merge($where, $dummyCollection->getSelect()->getPart(Zend_Db_Select::WHERE));
            }
        }
        
        $from = array_merge($from, $this->getSelect()->getPart(Zend_Db_Select::FROM));
        
        $this->getSelect()->setPart(Zend_Db_Select::FROM, $from);
        
        foreach($where as $w){
            $this->getSelect()->where($w);
    }
    }
    
    
    public function addUrlToSelect($storeId, $useCategory)
    {
        $urlRewrites = null;
        if ($this->_cacheConf) {
            if (!($urlRewrites = Mage::app()->loadCache($this->_cacheConf['prefix'] . 'urlrewrite'))) {
                $urlRewrites = null;
            } else {
                $urlRewrites = unserialize($urlRewrites);
            }
        }

        if (!$urlRewrites) {
            $productIds = array();
            foreach($this->getItems() as $item) {
                $productIds[] = $item->getEntityId();
            }
            if (!count($productIds)) {
                return;
            }

            $select = $this->getConnection()->select()
                ->from($this->getTable('core/url_rewrite'), array('product_id', 'request_path'))
                ->where('store_id = ?', $storeId)
                ->where('is_system = ?', 1)
//                ->where('category_id = ? OR category_id IS NULL', $this->_urlRewriteCategory)
                ->where('product_id IN(?)', $productIds)
                ->order('category_id ' . self::SORT_ORDER_DESC); // more priority is data with category id
            if (!$useCategory){
                $select->where('category_id IS NULL');
            }
            
            $urlRewrites = array();

            foreach ($this->getConnection()->fetchAll($select) as $row) {
                if (!isset($urlRewrites[$row['product_id']])) {
                    $urlRewrites[$row['product_id']] = $row['request_path'];
                }
            }

            if ($this->_cacheConf) {
                Mage::app()->saveCache(
                    serialize($urlRewrites),
                    $this->_cacheConf['prefix'] . 'urlrewrite',
                    array_merge($this->_cacheConf['tags'], array(Mage_Catalog_Model_Product_Url::CACHE_TAG)),
                    $this->_cacheLifetime
                );
            }
        }

        foreach($this->getItems() as $item) {
            if (isset($urlRewrites[$item->getEntityId()])) {
                $item->setData('request_path', $urlRewrites[$item->getEntityId()]);
            } else {
                $item->setData('request_path', false);
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
    
    public function addPriceToSelect($storeId)
    {
        $joinType = 'left';
        $joinConds = '';
        
        if ($this->_priceConds) {
            $joinType = 'inner';
            foreach ($this->_priceConds as $code => $arrayConds) {
                foreach ($arrayConds as $op => $val) {
                    $joinConds .= ' AND ' . $this->_getConditionSql('{{table}}.' . $code, array($op => $val));
                }
            }
        }
        $joinFields = array();
        
        $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
        $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        if (!empty($this->_addPrice)) {
            foreach($this->_addPrice as $field){
                $joinFields[] = 'price_tbl.'.$field;
            }
        } else {
            $joinFields['tax_class_id'] = 'price_tbl.tax_class_id';
        }
        
        if (is_null($customerGroupId)) {
            $customerGroupId = 0;
        }
                        
        $this->joinTable(array('price_tbl' => 'catalog/product_index_price'),
            'entity_id=entity_id',
            $joinFields,
            '{{table}}.website_id = \'' . $websiteId . '\' and {{table}}.customer_group_id = \'' . $customerGroupId . '\'' . $joinConds,
            $joinType);
    }
    
    public function addTaxPercentsToSelect($storeId)
    {
        if (empty($this->_addPrice) && empty($this->_priceConds)) {
            $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
            $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
            if (is_null($customerGroupId)) {
                $customerGroupId = 0;
            }
            $this->joinTable(
                array('price_tbl' => 'catalog/product_index_price'),
                'entity_id=entity_id',
                array('tax_class_id'),
                '{{table}}.website_id = \'' . $websiteId . '\' and {{table}}.customer_group_id = \'' . $customerGroupId . '\'',
                'left');
        }
        
        $this->getSelect()
             ->joinLeft(array('tax_table' => $this->getTable('tax/tax_calculation')),
                        'tax_table.product_tax_class_id = price_tbl.tax_class_id',
                        array())
             ->joinLeft(array('rate_table' => $this->getTable('tax/tax_calculation_rate')),
                        'rate_table.tax_calculation_rate_id = tax_table.tax_calculation_rate_id',
                        array('tax_percents' => 'rate_table.rate'));
        $joinConds = array();
        if ($this->_taxConds) {
            foreach ($this->_taxConds as $code => $arrayConds) {
                foreach ($arrayConds as $op => $val) {
                    $joinConds[] = ' AND ' . $this->_getConditionSql('rate_table.rate', array($op => $val));
                }
            }
        }
        $where = array_merge($this->getSelect()->getPart(Zend_Db_Select::WHERE), $joinConds);
        $this->getSelect()->setPart(Zend_Db_Select::WHERE, $where);
    }
    
    public function addQtyToSelect()
    {
        $joinType = 'joinLeft';
        
        if (empty($this->_qtyConds)) {
            $this->_qtyConds = null;
            $joinType = 'joinLeft';
        } else {
            $joinType = 'joinInner';
        }
        
//        $this->getSelect()->$joinType(
//                array('am_stock_item' => $this->getTable('cataloginventory/stock_item')), 
//                'am_stock_item.product_id=e.entity_id', 
//                array('qty', 'IF(am_stock_item.qty = 0, "Out of Stock", "In Stock") as stock_availability')
//                );
//        
//        if (is_array($this->_qtyConds)){
//            var_dump(123);
//            $this->addFieldToFilter('`am_stock_item`.`qty`', $this->_qtyConds);
//        }
        
        $this->joinTable(
                array('am_stock_item' => 'cataloginventory/stock_item'), 
                'product_id=entity_id', 
                array(
                    'am_stock_item' => 'qty',
                    'qty' => 'am_stock_item.qty',
                    'stock_availability' => 'IF(am_stock_item.qty = 0, "Out of Stock", "In Stock")'
//                    'fields' => array('qty', 'IF(am_stock_item.qty = 0, "Out of Stock", "In Stock") as stock_availability')
//                    
                ),
                $this->_qtyConds, 
                $joinType);

        
//        $this->joinTable(array('am_stock_item' => 'cataloginventory/stock_item'), 
//                'product_id=entity_id', 
//                array('qty', 'IF(am_stock_item.qty = 0, "Out of Stock", "In Stock") as stock_availability'),
//                $this->_qtyConds, 
//                $joinType);
        
        
        

        if (count($this->_qtyConds['qty']) > 1) {
            $from = $this->getSelect()->getPart(Zend_Db_Select::FROM);
            $temp = $from['_table_qty']['joinCondition'];
            $from['_table_qty']['joinCondition'] = str_replace(' or ', ' and ', $temp);
            $this->getSelect()->setPart(Zend_Db_Select::FROM, $from);
        }
    }
    
    public function addCategoryToSelect($storeId)
    {
//        if ($this->_catConds) {
            $joinConds = array();
        
        $this->getSelect()
             ->joinLeft(array('cat_prod' => $this->getTable('catalog/category_product')),
                        'e.entity_id = cat_prod.product_id', array('cat_prod_product_id' => 'cat_prod.product_id'));
        
        if (Mage::helper('catalog/category_flat')->isEnabled()) {
            $this->getSelect()
                ->joinLeft(array('cat_flat' => $this->getTable('catalog/category_flat'). '_store_'.$storeId ),
                'cat_flat.entity_id = cat_prod.category_id', array(

                )
            );


                foreach ($this->_catConds as $code => $arrayConds) {
                    foreach ($arrayConds as $op => $val)
                        if ('category_id' == $code) {
                            $joinConds[] = ' AND ' . $this->_getConditionSql('cat_flat.entity_id', array($op => $val));
                        } else {
                            $joinConds[] = ' AND ' . $this->_getConditionSql('cat_flat.name', array($op => $val));
                        }
                }

//                    $joinConds[] = ' and IFNULL(cat_flat.is_active, 0) = 1';

        } else {
        
            $attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_category', 'name');
                $isActiveAttributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_category', 'is_active');
            
            $this->getSelect()
                    ->joinLeft(array('at_is_active' => Mage::getSingleton('core/resource')->getTableName('catalog_category_entity_int')),
                               '(at_is_active.entity_id = cat_prod.category_id) AND ' .
                               '(at_is_active.store_id = ' . $storeId . ') AND ' .
                               '(at_is_active.attribute_id = ' . $isActiveAttributeId . ') ', array('cat_int_entity_id' => 'at_is_active.entity_id' ))
                    ->joinLeft(array('at_is_active_default' => Mage::getSingleton('core/resource')->getTableName('catalog_category_entity_int')),
                               '(at_is_active_default.entity_id = cat_prod.category_id) AND ' .
                               '(at_is_active_default.store_id = 0) AND ' .
                               '(at_is_active_default.attribute_id = ' . $isActiveAttributeId . ') ', array('cat_int_entity_id_default' => 'at_is_active_default.entity_id' ))
                ->joinLeft(array('cat_varchar' => Mage::getSingleton('core/resource')->getTableName('catalog_category_entity_varchar')),
                           '(cat_varchar.entity_id = cat_prod.category_id) AND ' .
                           '(cat_varchar.store_id = ' . $storeId . ') AND ' .
                           '(cat_varchar.attribute_id = ' . $attributeId . ') ', array('cat_varchar_entity_id' => 'cat_varchar.entity_id' ))
                ->joinLeft(array('cat_varchar_def' => Mage::getSingleton('core/resource')->getTableName('catalog_category_entity_varchar')),
                           '(cat_varchar_def.entity_id = cat_prod.category_id) AND ' .
                           '(cat_varchar_def.store_id = 0) AND ' .
                           '(cat_varchar_def.attribute_id = ' . $attributeId . ') ',
                           array(
                           ));

            foreach ($this->_catConds as $code => $arrayConds) {
                foreach ($arrayConds as $op => $val)
                    if ('category_id' == $code) {
                        $joinConds[] = ' AND ' . $this->_getConditionSql('IFNULL(cat_varchar.entity_id, cat_varchar_def.entity_id)', array($op => $val));
                    } else {
                        $joinConds[] = ' AND ' . $this->_getConditionSql('IFNULL(cat_varchar.value, cat_varchar_def.value)', array($op => $val));
                    }
            }

//                $joinConds[] = ' AND (IF(at_is_active.value_id > 0, at_is_active.value, at_is_active_default.value) <> 0)';

        }

        $where = array_merge($this->getSelect()->getPart(Zend_Db_Select::WHERE), $joinConds);
        $this->getSelect()->setPart(Zend_Db_Select::WHERE, $where);
//        }
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
    
    public function initByFeed($feed)
    {
        if ($this->isEnabledFlat()){
            $this->addAttributeToSelect(array('sku', 'type_id'));
        }
        
        if (($feed->getType() == Amasty_Feed_Model_Profile::TYPE_CSV) || ($feed->getType() == Amasty_Feed_Model_Profile::TYPE_TXT)) {
            $fields = unserialize($feed->getCsv());
        }
        
        if ($feed->getType() == Amasty_Feed_Model_Profile::TYPE_XML) {
            $feedXML = Mage::helper('amfeed')->parseXml($feed->getXmlBody());
            
            $fields = $feedXML['fields'];
        }
        
        $this->parseFields($fields, $feed->getStoreId());
        // base filters
        $this->addBaseFilters($feed->getStoreId(), $feed->getCondDisabled(), $feed->getCondStock(), explode(',', $feed->getCondType()));
        
        // advanced filters
        $this->parseAndAddAdvancedFilters($feed->getCondition());
        
        // unusual fields and filters
        
        
//        if ($this->_addParentId) { // add parent id for simple products, which are children of configurable products
            $this->addParentIdToSelect();
//        }
        
        if (!empty($this->_addPrice) || !empty($this->_priceConds)) { // add price
            $this->addPriceToSelect($feed->getStoreId());
        }
        
        if ($this->_addTax) { // add tax percents
            $this->addTaxPercentsToSelect($feed->getStoreId());
        }
        
        if ($this->_addQty || $this->_addStockAvailability) { // add qty
            $this->addQtyToSelect();
        }
        
        if ($this->_addCategory)
            $this->addCategoryToSelect($feed->getStoreId());
        
//        print $this->getSelect();
//        exit(1);
        
        return $this;
    }
}