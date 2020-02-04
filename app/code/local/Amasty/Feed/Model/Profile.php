<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */  
class Amasty_Feed_Model_Profile extends Amasty_Feed_Model_Filter
{

    const STATE_READY    = 0;
    const STATE_WAITING  = 1;
    const STATE_PROGRESS = 2;
    const STATE_ERROR    = 3;
    
    const MAX_ERRORS = 2;
    
    const DEBUG_MODE = true;
    
    const TYPE_CSV = 0;
    const TYPE_XML = 1;
    const TYPE_TXT = 2;
    
    const DELIVERY_TYPE_DLD = 0;
    const DELIVERY_TYPE_FTP = 2;
    const DELIVERY_TYPE_SFTP = 3;

    const COMPRESS_NONE = '';
    const COMPRESS_ZIP = 'zip';
    const COMPRESS_GZ = 'gz';
    const COMPRESS_BZ = 'bz2';
    
    public function _construct()
    {    
        $this->_init('amfeed/profile');
    }
    
    protected $_productCollection;
    protected $_productParentCollection;
    protected $_productChildCollection;
    protected $_productSuperAttributesCollection;
    protected $_attributesCollection;
    
    protected $_productGallery;
    protected $_productUrls;
    
    protected $_productParentData;
    protected $_productChildData;
    protected $_superAttributesData;
    protected $_fields;
    protected $_lines2fields;
    protected $_resultData;
    protected $_attributes = array();
    protected $_attributesOptions = array();
    protected $_attributesTypes = array();
    protected $_stepSize = 100;
    protected $_customFields = array();
    protected $_categoriesWithRoots = array();
    protected $_categories = array();
    protected $_categoriesLast = array();
    protected $_categoriesSeparator = '/';
    protected $_attributeHelper;
    protected $_currencyRates;
    protected $_categoriesMappers = array();
    protected $_store;
    
    public function getStepSize()
    {
        $batchSize = Mage::getStoreConfig('amfeed/system/batch_size');
        return $batchSize ? $batchSize : $this->_stepSize;
    }


    function getProductCollection(){
        
        if (!$this->_productCollection){
            $this->_productCollection = Mage::getResourceModel('amfeed/product_collection');

            $this->_productCollection->init($this);
        }
        
        return $this->_productCollection;
    }
    
    function getProductParentCollection(){
        
        if (!$this->_productParentCollection){
            $this->_productParentCollection = Mage::getResourceModel('amfeed/product_collection');

            $this->_productParentCollection->init($this);
        }
        
        return $this->_productParentCollection;
    }
    
    function getProductChildCollection(){
        
        if (!$this->_productChildCollection){
            $this->_productChildCollection = Mage::getResourceModel('amfeed/product_collection');

            $this->_productChildCollection->init($this);
        }
        
        return $this->_productChildCollection;
    }
    
    function getSuperAttributesCollection(){
        
        if (!$this->_productSuperAttributesCollection){
            $this->_productSuperAttributesCollection = Mage::getResourceModel('catalog/product_type_configurable_attribute_collection');
        }
        
        return $this->_productSuperAttributesCollection;
    }
    
    function getProductChildData($parentId){
        if (!$this->_productChildData){
            $collectionData = $this->getProductChildCollection()->getData();
            foreach($collectionData as $item){
                
                if (!isset($this->_productChildData[$item['parent_id']])){
                    $this->_productChildData[$item['parent_id']] = array();
                }
                $this->_productChildData[$item['parent_id']][] = $item;
            }
        }
        
        return isset($this->_productChildData[$parentId]) ? $this->_productChildData[$parentId] : null;
    }
    
    function getProductParentData($parentId){
        if (!$this->_productParentData){
            $collectionData = $this->getProductParentCollection()->getData();
            foreach($collectionData as $item){
                $this->_productParentData[$item['entity_id']] = $item;
            }
        }
        
        return isset($this->_productParentData[$parentId]) ? $this->_productParentData[$parentId] : null;
    }
    
    function getSuperAttributesData($productId){
        if (!$this->_superAttributesData){
            
            $collectionData = $this->getSuperAttributesCollection()->getData();
            foreach($collectionData as $item){
                
                if (!isset($this->_superAttributesData[$item['product_id']])){
                    $this->_superAttributesData[$item['product_id']] = array();
                }
                
                $this->_superAttributesData[$item['product_id']][] = $item;                
            }
        }
        
        return isset($this->_superAttributesData[$productId]) ? $this->_superAttributesData[$productId] : null;
    }
    
    protected function _prepareProductCollection()
    {
        $this->getProductCollection()->addParentIdToSelect();
        
        $this->_prepereCollection($this->getProductCollection());
        
        $offset = $this->getExportStep() * $this->getStepSize();
        
        $this->getProductCollection()->getSelect()
                ->limit( $this->getStepSize(), $offset );
    }
    
    protected function _prepereCollection($collection){
        $fields = $this->_getFields();
        
        if (isset($fields['type'])) {
            foreach ($fields['type'] as $idx => $type) {
                if ($type == "attribute"){
                    $code = $fields['attr'][$idx];

                    if ($this->_getAttributeHelper()->isCompoundAttribute($code)){
                        $compoundAttribute = $this->_getCompoundAttribute($code);
                        $compoundAttribute->prepareCollection($collection);
                    }
                }  else if ($type == "custom_field"){
                    $customField = $this->getCustomField($fields['custom'][$idx]);
                    if ($customField) {
                        foreach($customField->getAdvencedAttributes() as $code) {
                            if ($this->_getAttributeHelper()->isCompoundAttribute($code)){
                                $compoundAttribute = $this->_getCompoundAttribute($code);
                                $compoundAttribute->prepareCollection($collection);
                            }
                        }
                    }
                } else if  ($type == "category"){
                    $collection->joinCategories();
                }
            }
        }
        foreach($this->_attributes as $code => $val){
            $collection->addAttribute($code, $this->getStoreId());            
        }
        
        $collection->addConditions();
    }
    
        protected function _prepareProductParentCollection(){
        
        $this->_prepereCollection($this->getProductParentCollection());
        
        $idsSelect = "select DISTINCT parent_id from (" . $this->getProductCollection()->getSelect()->__toString() . ") as tmp";

        $this->getProductParentCollection()->getSelect()->reset(Zend_Db_Select::WHERE);
        
        $from = $this->getProductParentCollection()->getSelect()->getPart(Zend_Db_Select::FROM);
        
        $from['products'] = array(
                'joinType' => 'inner join',
                'schema' => null,
                'tableName' => new Zend_Db_Expr('(' . $idsSelect . ')'),
                'joinCondition' => 'e.entity_id = parent_id'
            );
        
        $this->getProductParentCollection()->getSelect()->setPart(Zend_Db_Select::FROM, $from);
        
//        $this->getProductParentCollection()->getSelect()->where("e.entity_id in (" .
//            $idsSelect
//        . ")");

    }
    
    
    protected function _prepareProductChildCollection(){
        
        $this->getProductChildCollection()->addParentIdToSelect();
        
        $this->getProductChildCollection()->addFieldToFilter("type_id", array(
            "in" => array(
                'configurable',
                'group'
            )
        ));
        
        $this->_prepereCollection($this->getProductChildCollection());
        
        $idsSelect = "select DISTINCT entity_id from (" . $this->getProductCollection()->getSelect()->__toString() . ") as tmp";

        $this->getProductChildCollection()->getSelect()->reset(Zend_Db_Select::WHERE);
        
        $this->getProductChildCollection()->getSelect()->where("relation_table.parent_id in (" .
            $idsSelect
        . ")");
    }
    
    protected function _prepareSuperAttributesCollection(){
        $idsSelect = "select DISTINCT parent_id from (" . $this->getProductCollection()->getSelect()->__toString() . ") as tmp";
        
        $this->getSuperAttributesCollection()->getSelect()->where("product_id in (" .
            $idsSelect
        . ")");
    }
    
    function getFields(){
        return $this->_getFields();
    }
    
    function getLines2fields()
    {
        return $this->_lines2fields;
    }
    
    protected function _getFields()
    {
        if (!$this->_fields) {
            if (($this->getType() == self::TYPE_CSV) || ($this->getType() == self::TYPE_TXT)) 
            {
                $this->_fields = unserialize($this->getCsv());
            } else if ($this->getType() == self::TYPE_XML) {
                $feedXML = Mage::helper('amfeed')->parseXml($this->getXmlBody());
                $this->_fields = $feedXML["fields"];
                $this->_lines2fields = $feedXML["lines2fields"];
            }
        }
        
        return $this->_fields;
    }
    
    protected function _prepareCompoundAttributeData($code, &$productData){
        $compoundAttribute = $this->_getCompoundAttribute($code);
        
        $productData[$code] = $compoundAttribute->getCompoundData($productData);
    }
    
    function getAttributeValue($code, $productData){
        $ret = null;
        
        if ($this->_getAttributeHelper()->isCompoundAttribute($code)){
            $this->_prepareCompoundAttributeData($code, $productData);
        }
        
        if (isset($this->_attributesOptions[$code])) {
            $value = $productData[$code];

            if ($this->_attributesTypes[$code] == 'multiselect'){
                if (!empty($value)) {
                    $arrValue = explode(",", $value);
                    $arrRet = array();
                    foreach($arrValue as $value){
                        $arrRet[] = isset($this->_attributesOptions[$code][$value]) ? $this->_attributesOptions[$code][$value] : $value;
                    }
                    $ret = implode(",", $arrRet);
                }

            } else {
                $ret = isset($this->_attributesOptions[$code][$value]) ? $this->_attributesOptions[$code][$value] : '';
            }

        } else if (isset($productData[$code])){
            $ret = $productData[$code];
        }
        
        return $ret;
    }

    /**
     * prepare attribute value for output
     *
     * @param string $value
     * @param string $code attribute code
     *
     * @return string
     */
    protected function _modifyAttribute($value, $code)
    {
        switch ($code) {
            case 'image':
            case 'small_image':
            case 'thumbnail':
                $mediaConfig = Mage::getSingleton('catalog/product_media_config');

                if ($this->getDefaultImage() && ($value == "no_selection" || !$value)) {
                    // if no image selected. Get default image URL
                    $value = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)
                        . 'amfeed/images/' . $this->getId() . '.jpg';
                } else {
                    if ($value && $value != "no_selection") {
                        $value = str_replace('https://', 'http://', $mediaConfig->getMediaUrl($value));
                    } else {
                        $value = '';
                    }
                }
        }

        return $value;
    }


    protected function _getValue($fields, $idx, $productData){
        $value = null;
        
        switch($fields['type'][$idx]){
            case "attribute":
                $attributeValue = $this->getAttributeValue($fields['attr'][$idx], $productData);
                $value = $this->_modifyAttribute($attributeValue, $fields['attr'][$idx]);
                break;
            case "custom_field":
                $customField = $this->getCustomField($fields['custom'][$idx]);
                
                if ($customField){
                    $value = $customField->loadValue($productData);
                    $value = Mage::helper('cms')->getBlockTemplateProcessor()->filter($value);
                }
                break;
            case "text":
                $value = $fields['txt'][$idx];
                break;
            case "images":
                $value = $this->_getImage($fields['images'][$idx], $fields['image_format'][$idx], $productData);
                break;
            case "category":
                $mappedCategory = $this->_getCompoundAttribute('mapped_category');
                $value = $mappedCategory->getCategoryValue($productData, $this->_getCategoryMapper($fields['category'][$idx]));

                break;
        }
        return trim($value);
    }

    protected function _getCategoryMapper($code)
    {
        if (!isset($this->_categoriesMappers[$code])){
            $category = Mage::getModel('amfeed/category')->load($code, 'code');
            $this->_categoriesMappers[$code] = Mage::getModel('amfeed/category_mapping')->getCategoriesMappingCollection($category);
        }

        return $this->_categoriesMappers[$code];
    }

    protected function _getImage($field, $format, $productData){
        $ret = null;
        
        $productId = $productData["entity_id"];
        
        if (isset($this->_productGallery[$productId])){
            foreach($this->_productGallery[$productId] as $order => $image){
                if ("image_" . $order == $field){
                    switch ($format){
                        case "135x135":
                            $ret = Mage::helper('catalog/image')->init(Mage::getModel('catalog/product'), 'small_image', $image['file'])->resize(135)->__toString();
                            break;
                        case "265x265":
                            $ret = Mage::helper('catalog/image')->init(Mage::getModel('catalog/product'), 'small_image', $image['file'])->resize(265)->__toString();
                            break;
                        case "75x75":

                            $ret = Mage::helper('catalog/image')->init(Mage::getModel('catalog/product'), 'small_image', $image['file'])->resize(75)->__toString();

                            break;
                        case "base":
                            $mediaConfig = Mage::getSingleton('catalog/product_media_config');
                            
                            $ret = $mediaConfig->getMediaUrl($image['file']);
                            break;
                    }

                    $ret = str_replace('https://', 'http://', $ret);
                    break;
                }
            }
        }

        return $ret;
    }
    
    protected function _modifyValue(&$value, $fields, $idx){
        $this->_format($value, $fields['format'][$idx]);
        $this->_length($value, $fields['length'][$idx]);
        $this->_concat($value, $fields['before'][$idx], $fields['after'][$idx]);

        switch ($fields['format'][$idx]) {
            case 'as_is':
            case 'price':
                break;
            default :
                if ($this->getType() == self::TYPE_XML && !empty($value) &&
                    $fields['type'][$idx] != 'images') {
                    $value = '<![CDATA[' . $value . ']]>';
                }
            break;
        }
    }
    
    
    protected function _concat(&$value, $before, $after){
        
        if (!empty($before))
            $value = $before . $value;
        
        if (!empty($after))
            $value = $value . $after;
    }
    
    protected function _format(&$value, $format){
        switch ($format) {
            case 'as_is':
                break;
            case 'strip_tags':
                $value = strtr($value, array("\n" => '', "\r" => ''));
                $value = strip_tags($value);
                break;
            case 'html_escape':
                $value = htmlspecialchars($value);
                break;
            case 'date':
                if ($this->getFrmDate() && !empty($value)) {
                    $value = date($this->getFrmDate(), strtotime($value));
                }
                break;
            case 'price':
                if ($this->getFrmPrice() !== null && $this->getFrmPrice() !== '') {

                    $decPoint = $this->getFrmPriceDecPoint();
                    $thPoint  = $this->getFrmPriceThousandsSep();
                    if ($decPoint === null) {
                        $decPoint = '';
                    }
                    if ($thPoint === null) {
                        $thPoint = '';
                    }

                    if ($value > 0) {
                        $value = $value * $this->getCurrencyRate();
                        $value = number_format($value, intval($this->getFrmPrice()), $decPoint, $thPoint);
                    }
                }
                break;
            case 'lowercase':
                $value = function_exists("mb_strtolower") ?
                            mb_strtolower($value, "UTF-8") :
                            strtolower($value);
                break;
            case 'integer':
                $value = intval($value);
                break;
        }
    }
    
    protected function _length(&$value, $limit)
    {
        if (!empty($limit)) {
            $value = function_exists("mb_substr") ?
                        mb_substr($value, 0, $limit, "UTF-8") :
                        substr($value, 0, $limit);
        }

    }
        
    protected function _generateData(){
        $useParentIfEmpty = $this->getFrmUseParent() == 1;
        
        $fields = $this->_getFields();
        
        foreach($this->getProductCollection()->getData() as $productData){
            $record = array();
            if (isset($fields['type'])) {
                foreach ($fields['type'] as $idx => $field) {
                    $useParent = $fields['parent'][$idx] == 'yes';
                    $value = "";

                    if ($useParent && !empty($productData['parent_id'])){

                        $parentProductData = $this->getProductParentData($productData['parent_id']);
                        if ($parentProductData){
                            $value = $this->_getValue($fields, $idx, $parentProductData);
                        }
                    }

                    if (empty($value)){
                        $value = $this->_getValue($fields, $idx, $productData);
                    }

                    if (!$useParent && empty($value) && $useParentIfEmpty
                            && !empty($productData['parent_id'])){
                        
                        $parentProductData = $this->getProductParentData($productData['parent_id']);
                        if ($parentProductData){
                            $value = $this->_getValue($fields, $idx, $parentProductData);
                        }
                    }

		    if (!empty($value)) {
                    	$this->_modifyValue($value, $fields, $idx);
		    }

                    $record[] = $value;
                }

                $this->_resultData[] = $record;
            }
        }
    }
    
    protected function _getAttributeHelper(){
        if (!$this->_attributeHelper){
            $this->_attributeHelper = Mage::helper("amfeed/attribute");
        }
        return $this->_attributeHelper;
    }
    
    protected function _getCompoundAttribute($code){
        return $this->_getAttributeHelper()
                ->getCompoundAttribute($code)
                ->init($this);
    }
    
    protected function _loadAttributes(){
        $attrCollection = $this->loadAttributesCollection();
        $attributes = array();
        foreach($attrCollection as $attribute){
            $attributes[$attribute->getAttributeCode()] = $attribute;
        }
                
        $fields = $this->_getFields();
        if (isset($fields['type'])) {
            foreach ($fields['type'] as $idx => $type) {
                if ($type == "attribute"){
                    $code = $fields['attr'][$idx];
                    
                    if (isset($attributes[$code])) {
                        $this->_loadAttribute($code);
                    }

                } else if ($type == "custom_field"){
                    $customField = $this->getCustomField($fields['custom'][$idx]);

                    if ($customField) {
                        foreach($customField->getAdvencedAttributes() as $attr) {
                            $this->_loadAttribute($attr);
                        }
                    }
                }
            }
        }
    }
    
    protected function _loadAttribute($code){
        if ($this->_getAttributeHelper()->isCompoundAttribute($code)){

            $compoundAttribute = $this->_getCompoundAttribute($code);

            foreach($compoundAttribute->getAttributesCodes() as $compoundCode)
            {
                $this->_attributes[$compoundCode] = true;
            }
         } else {
             $this->_attributes[$code] = true;
         }
    }
    
    function getAttribute($id){
        $ret = null;
        
        foreach($this->_attributes as $attribute){
            if ($attribute->getId() == $id){
                $ret = $attribute;
                break;
            }
        }
        
        
        return $ret;
    }
    
    function loadAttributesCollection(){
        if (!$this->_attributesCollection) {
            $this->_attributesCollection = Mage::getResourceModel('catalog/product_attribute_collection')
                    ->addVisibleFilter()
                    ->addStoreLabel($this->getStoreId());
        }
        return $this->_attributesCollection;
    }
    
    protected function _initAttributes()
    {
        $this->_loadAttributes();
        
        foreach ($this->loadAttributesCollection() as $attribute) {
            $code = $attribute->getAttributeCode();
            
            if (array_key_exists($code, $this->_attributes)) {
                $this->_attributesTypes[$code] = $attribute->getFrontendInput();
                switch ($attribute->getFrontendInput()) {
                    case 'select':
                    case 'multiselect':
                        $valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                            ->setAttributeFilter($attribute->getId())
                            ->setStoreFilter($this->getStoreId(), true)
                            ->load();
                        if ($valuesCollection->getSize() > 0) {
                            foreach ($valuesCollection as $item) {
                                $selectOptions[] = array('value' => $item->getId(), 'label' => $item->getValue());
                            }
                        } else {
                            $selectOptions = $attribute->getFrontend()->getSelectOptions();
                        }
                        
                        $this->_attributesOptions[$code] = array();
                        
                        foreach($selectOptions as $option){
                            $this->_attributesOptions[$code][$option['value']] =
                                $option['label'];
                        }

                        break;
                }
                
                
            }
        }
    }
    
    function getResultData(){
        return $this->_resultData;
    }
    
    protected function _write(){
        $type = 'csv';
        
        if ($this->getType() == self::TYPE_XML) {
            $type = 'xml';
        }
        
        $writer = Mage::getSingleton('amfeed/writer_' . $type); 
        $writer->init($this);
        $writer->write();
            
    }
    
    protected function _beforeGenerate(){
        $exportKey = $this->getExportKey();
        
        $filePath = $this->getMainPath() . $exportKey;

        if ($this->getExportStep() === NULL || !file_exists($filePath)) {
            
            $this->setStatus(Amasty_Feed_Model_Profile::STATE_PROGRESS);
            $this->setExportKey(uniqid("_"));
            $this->setExportStep(0);
            $this->save();
        }
    }
    
    protected function _afterGenerate(){
        
        if ($this->getExportStep() == 0){
            $this->setInfoCnt(count($this->_resultData));
            $this->setInfoTotal($this->_productCollection->getCountProducts());
        } else {
            $this->setInfoCnt($this->getInfoCnt() + count($this->_resultData));
        }
        
        if (count($this->_resultData) < $this->getStepSize()){
            @rename($this->getMainPath() . $this->getExportKey(), $this->getMainPath());
            
            $this->setExportKey(NULL);
            $this->setExportStep(NULL);
            
            $this->setGeneratedAt(date('Y-m-d H:i:s'));
            
            $this->setDelivered(0);
            if ($this->getDeliveryType() == self::DELIVERY_TYPE_FTP) {
                $this->_ftpUpload();
            } else if ($this->getDeliveryType() == self::DELIVERY_TYPE_SFTP) {
                $this->_sftpUpload();
            }
            
            $this->setStatus(Amasty_Feed_Model_Profile::STATE_READY);
        } else {
            $this->setExportStep($this->getExportStep() + 1);
        }
        
        $this->save();
    }
    
    public function unlink(){
        @unlink($this->getMainPath() . $this->getExportKey());    
        $this->setExportKey(NULL);
        $this->setExportStep(NULL);
    }
    
    function getCustomField($code){
        if (!isset($this->_customFields[$code])){
            $this->_customFields[$code] = Mage::getModel("amfeed/field")->load($code, 'code');
            $this->_customFields[$code]->init($this);
        }
        return $this->_customFields[$code];
    }
    
    protected function _prepareGallery(){
        
        $hasImages = FALSE;
        $fields = $this->_getFields();
        if (isset($fields['type'])) {
            foreach ($fields['type'] as $idx => $type) {
                if ($type == "images"){
                    $hasImages = TRUE;
                    break;
                }
            }
        }

        if ($hasImages) {
            $attribute = Mage::getModel('eav/entity_attribute')->loadByCode("catalog_product", "media_gallery");

            if ($attribute->getId()) {
                $gallery = Mage::getResourceModel("amfeed/gallery");

                 foreach($gallery->loadGallery($attribute->getId(), $this->getStoreId(), $this->getProductCollection()) as $image){
                     if (array_key_exists('disabled_default', $image) && $image['disabled_default'])
                         continue;

                     if (array_key_exists('disabled', $image) && $image['disabled'])
                         continue;
                 
                     if (!isset($this->_productGallery[$image["product_id"]])){
                         $this->_productGallery[$image["product_id"]] = array(0 => null);
                     }
                     
                     $this->_productGallery[$image["product_id"]][] = $image;
                 }
                 
                 foreach($gallery->loadGallery($attribute->getId(), $this->getStoreId(), $this->getProductParentCollection()) as $image){
                     if (array_key_exists('disabled_default', $image) && $image['disabled_default'])
                         continue;

                     if (array_key_exists('disabled', $image) && $image['disabled'])
                         continue;
                 
                     if (!isset($this->_productGallery[$image["product_id"]])){
                         $this->_productGallery[$image["product_id"]] = array(0 => null);
                     }
                     
                     $this->_productGallery[$image["product_id"]][] = $image;
                 }

            } else {
                throw new Exception("Media gallery attribute not found");
            }
        }
    }
    
    protected function _prepareUrls(){

        $urlProductData = $this->getProductCollection()->getUrlData($this->getStoreId(), $this->getFrmDontUseCategoryInUrl() == 0);
        $urlProductParentData = $this->getProductParentCollection()->getUrlData($this->getStoreId(), $this->getFrmDontUseCategoryInUrl() == 0);
        
        
        foreach($urlProductData as $productId => $url){
            $this->_productUrls[$productId] = $url;
        }
        
        foreach($urlProductParentData as $productId => $url){
            $this->_productUrls[$productId] = $url;
        }
    }
    
    function getProductUrl($productId){
        $ret = null;
        
        $path = isset($this->_productUrls[$productId]) ?
                $this->_productUrls[$productId] :
                'catalog/product/view/id/' . $productId;

        return Mage::getModel('core/url')->getBaseUrl() . $path;
    }

    protected function _prepareCategories()
    {
        $collection = Mage::getResourceModel('catalog/category_collection')->addNameToResult();
        /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection */
        foreach ($collection as $category) {
            $structure = explode('/', $category->getPath());
            $pathSize  = count($structure);
            if ($pathSize > 1) {
                $path = array();
                for ($i = 1; $i < $pathSize; $i++) {
                    $item = $collection->getItemById($structure[$i]);
                    if ($item) {
                        $path[] = $item->getName();
                    }

                }
                $rootCategoryName = array_shift($path);
                if (!isset($this->_categoriesWithRoots[$rootCategoryName])) {
                    $this->_categoriesWithRoots[$rootCategoryName] = array();
                }
                $index = implode($this->_categoriesSeparator, $path);
                $this->_categoriesWithRoots[$rootCategoryName][$category->getId()] = $index;
                if ($pathSize > 2) {
                    $this->_categories[$category->getId()] = $index;
                    $this->_categoriesLast[$category->getId()] = $category->getName();
                }
            }
        }   
    }
    
    public function getProductCategories(){
        return $this->_categories;
    }
    
    public function getProductCategoriesLast(){
        return $this->_categoriesLast;
    }
       
    protected function _prepareCurrencyRates(){
        /**
         * Get the base currency
         */
        $baseCurrencyCode = Mage::app()->getBaseCurrencyCode();

        /**
         * Get all allowed currencies
         * returns array of allowed currency codes
         */
        $allowedCurrencies = Mage::getModel('directory/currency')
                                    ->getConfigAllowCurrencies();

        /**
         * Get the currency rates
         * returns array with key as currency code and value as currency rate
         */
        $this->_currencyRates = Mage::getModel('directory/currency')
                                ->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));

    }

    public function getCurrencyRate(){
        $ret = 1;
        if (isset($this->_currencyRates[$this->getCurrency()])){
            $ret = $this->_currencyRates[$this->getCurrency()];
        }
        return $ret;
    }
       
    public function generate()
    {

        $this->_beforeGenerate();
        
        $oldStore = Mage::app()->getStore();
        
        Mage::app()->setCurrentStore($this->getStoreId());
        
        $this->_initAttributes();
        
        $this->_prepareProductCollection();
        
        $this->_prepareProductParentCollection();
        
        $this->_prepareProductChildCollection();
        
        $this->_prepareSuperAttributesCollection();
        
        $this->_prepareCurrencyRates();
        
        $this->_prepareGallery();
        $this->_prepareCategories();
        
        $this->_prepareUrls();
        
        $this->_generateData();
        
        $this->_write();
        
        $this->_afterGenerate();
        
        $this->save();
        
        Mage::app()->setCurrentStore($oldStore);
        
        return count($this->_resultData) < $this->getStepSize() || count($this->_resultData) == 0;
    }
    
    protected function _getRemotePath(){
        $remotePath = $this->getFtpFolder();
        if ('/' != substr($remotePath, -1, 1) && '\\' != substr($remotePath, -1, 1)) {
            $remotePath .= '/';
        }
        $remoteFileName = $this->getResponseFilename();
        $remotePath .= $remoteFileName;
        
        return $remotePath;
    }
    
    protected function _ftpUpload()
    {
        if (false !== strpos($this->getFtpHost(), ':')) {
            list($ftpHost, $ftpPort) = explode(':', $this->getFtpHost());
        } else {
            $ftpHost = $this->getFtpHost();
            $ftpPort = 21;
        }

        $ftp = @ftp_connect($ftpHost, $ftpPort, 10);
        if (!$ftp) {
            throw new Exception(Mage::helper('amfeed')->__('Can not connect the FTP server %s:%s.', $ftpHost, $ftpPort));                
        }

        $ftpLogin = @ftp_login($ftp, $this->getFtpUser(), $this->getFtpPass());
        if (!$ftpLogin) {
            throw new Exception(Mage::helper('amfeed')->__('Can not log in to the server with user `%s` and password `%s`.', $this->getFtpUser(), $this->getFtpPass()));
        }
        
        if ($this->getFtpIsPassive()) {
            ftp_pasv($ftp, true);
        }
        $remotePath = $this->_getRemotePath();
        
        $upload = @ftp_put($ftp, $remotePath, $this->geOutputPath(), FTP_ASCII);
        if (!$upload) {
            throw new Exception(Mage::helper('amfeed')->__('Can not upload the file to the folder %s. Please check write permissions', $remotePath));
        }
        ftp_close($ftp);
   
        $this->setDelivered(1);
        $this->setDeliveryAt(date('Y-m-d H:i:s'));

        return $this;
    }
    
    protected function _sftpUpload(){
        $srcFile = $this->geOutputPath();
        $dstFile = $this->_getRemotePath();
        
        if (false !== strpos($this->getFtpHost(), ':')) {
            list($ftpHost, $ftpPort) = explode(':', $this->getFtpHost());
        } else {
            $ftpHost = $this->getFtpHost();
            $ftpPort = 22;
        }
        
        $ch = curl_init();

        $fp = fopen($srcFile, 'r');

        curl_setopt($ch, CURLOPT_URL, 'sftp://'.$this->getFtpHost().$dstFile);
        
        curl_setopt($ch, CURLOPT_USERPWD, $this->getFtpUser().':'.$this->getFtpPass());

        curl_setopt($ch, CURLOPT_UPLOAD, 1);

        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);

        curl_setopt($ch, CURLOPT_INFILE, $fp);

        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($srcFile));

        curl_exec ($ch);

        $error_no = curl_errno($ch);


        if ($error_no == 0) {

            //File uploaded succesfully
            $this->setDelivered(1);
            $this->setDeliveryAt(date('Y-m-d H:i:s'));

        } else {
            throw new Exception(curl_error($ch));
        }
        
        curl_close ($ch);

        return $this;
    }
    
    protected function _oldVersionConditon(){
        return $this->condition_serialized === NULL 
            && is_array($this->cond_advanced);
    }
    
    public function getCondition(){
        $ret = array();
        if ($this->_oldVersionConditon()){ // $this->cond_advanced OLD FIELD, COMPATIBLITY FIX
            
            $ind = 1;
            if (isset($this->cond_advanced['attr'])){
            foreach($this->cond_advanced['attr'] as $order => $code){
                
                $ret[$ind] = array(
                    'condition' => array(
                        'attribute' => array(),
                        'operator' => array(),
                        'value' => array(),
                        'type' => array(),
                        'other' => array()
                    )
                );

                $attribute = Mage::getResourceModel('catalog/product')
                        ->getAttribute($code);
                
                
                $ret[$ind]['condition']['attribute'][$order] = $this->cond_advanced['attr'][$order];
                $ret[$ind]['condition']['operator'][$order] = $this->cond_advanced['op'][$order];
                $ret[$ind]['condition']['type'][$order] = self::$_TYPE_ATTRIBUTE;
                
                $value = $this->cond_advanced['val'][$order];
                
                if ($attribute && $attribute->getFrontendInput() == 'select'){
                    $allOptions = $attribute->getSource()->getAllOptions();
                    $options = array();
                    foreach($allOptions as $option){
                       $options[$option['value']] = $option['label'];
                    }
                    
                    if (in_array($value, $options)){
                        $ind = array_search($value, $options);
                        $value = $ind;
                    }
                } 
                
                $ret[$ind]['condition']['value'][$order] = $value;
                
                $ind++;
            }
            }
            
        } else {
            $ret = parent::getCondition();
        }
        return $ret;
    }
    
    public function hasAdvancedCondition(){
        
        return $this->_oldVersionConditon() ||
                parent::hasAdvancedCondition();
    }
    
    public function sendTo(){
        
        $sendTo = $this->getSendTo();
        if (!empty($sendTo)){
            $downloadUrl = Mage::getUrl('amfeed/main/get', array('file' => $this->getFilename()));
            
            ini_set('SMTP', Mage::getStoreConfig('system/smtp/host'));
            ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port'));

            $mail = new Zend_Mail('utf-8');

            $mail->setBodyHTML("<a href='" . $downloadUrl . "'>" . $this->getFilename() . "</a>");

            $mail->setSubject('=?utf-8?B?' . base64_encode($this->getFilename() . " " . Mage::getModel('core/date')->date('Y-m-d H:i:s')) . '?=');

            $emails = explode(",", $sendTo);
            foreach($emails as $email){
                $email = trim($email);
                
                if (Zend_Validate::is($email, 'EmailAddress')){
                    
                    $mail->addTo($email);
                    
                    try {
                    if ((string)Mage::getConfig()->getNode('modules/Aschroder_SMTPPro/active') == 'true') {
                        $transport = Mage::helper('smtppro')->getTransport();
                        $mail->send($transport);
                    } else {
                        $mail->send();
                    }
                 }
                 catch (Exception $e) {
                    Mage::logException($e, null, 'amfeed.log');
                    return false;
                 }
                }
            }
        }
    }
    
    public function getMainPath()
    {
        return Mage::helper('amfeed')->getDownloadPath('feeds', $this->getRealFilename());
    }

    public function geOutputPath()
    {
        return Mage::helper('amfeed')->getDownloadPath('feeds', $this->getOutputFilename());
    }

    public function getUrl()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'amfeed/' . $this->getFilename() . $this->getFileExt();
    }
    
    protected function _beforeSave()
    {
        if ($id = $this->getId()) {
            $temp = Mage::getModel('amfeed/profile')->load($id);
            $tempPath = Mage::helper('amfeed')->getDownloadPath('feeds', $temp->getRealFilename());
            if (file_exists($tempPath)) {
                $deleted = false;
                if (($temp->getType() != $this->getType())) {
                    Mage::helper('amfeed')->deleteFile($tempPath);
                    $deleted = true;
                }
                if (!$deleted && ($temp->getRealFilename() != $this->getRealFilename())) {
                    rename($tempPath, $this->getMainPath());
                }
            }
        }
        
        return parent::_beforeSave();
    }
    
    public function getFileExt($feed = null)
    {
        if (!$feed) {
            $feed = $this;
        }
        $fileExt = '.xml';
        if ($feed->getType() == self::TYPE_CSV) {
            $fileExt = '.csv';
        }
        if ($feed->getType() == self::TYPE_TXT) {
            $fileExt = '.txt';
        }
        return $fileExt;
    }
    
    protected function _afterSave()
    {
        if ($this->getFrmImageUrl()) {
            if (isset($_FILES['upload_image']['error']) && UPLOAD_ERR_OK == $_FILES['upload_image']['error'])
            {
                try {
                    // trying to upload image
                    $uploader = new Varien_File_Uploader('upload_image');
                    $uploader->setFilesDispersion(false);
                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                    $uploader->save(Mage::helper('amfeed')->getDownloadPath('images'), $this->getId() . '.jpg');
                } catch (Exception $e) {
                    throw new Exception(Mage::helper('amfeed')->__('An error occurred while saving the feed: %s', $e->getMessage()));
                }
                unset($_FILES['upload_image']);
                $this->setDefaultImage(1)->save();
            }
        } else {
            $path = Mage::helper('amfeed')->getDownloadPath('images', $this->getId() . '.jpg');
            if (file_exists($path)) {
                Mage::helper('amfeed')->deleteFile($path);
                $this->setDefaultImage(0)->save();
            }
        }
        
        return parent::_afterSave();
    }
    
    protected function _beforeDelete()
    {
        if ($this->getDefaultImage()) { // delete default image
            $path = Mage::helper('amfeed')->getDownloadPath('images', $this->getId() . '.jpg');
            Mage::helper('amfeed')->deleteFile($path);
            $this->setDefaultImage(0)->save();
        }
        $path = $this->getMainPath();
        if (file_exists($path)) { // delete feed file
            Mage::helper('amfeed')->deleteFile($path);
        }
        
        return parent::_beforeDelete();
    }
    
    public function getGeneratedAt()
    {
        $v = $this->getData('generated_at');
        if ('0000-00-00 00:00:00' == $v)
            $v = '';
            
        return $v;
    } 
    
    public function getDeliveryAt()
    {
        $v = $this->getData('delivery_at');
        if ('0000-00-00 00:00:00' == $v)
            $v = '';
            
        return $v;
    } 

    function _replaceSystemPlaceholder($ret){

        $nowRegex = "/%now\[[^\]]*\]%/";
        
        preg_match_all($nowRegex, $ret, $nowMathes);
        
        $replace = array();
        
        if (isset($nowMathes[0])){
            foreach ($nowMathes[0] as $match) {
                $replace[$match] = $this->_getNowSystemPlaceholder($match);
            }
        }
        
        $ret = strtr($ret, $replace);
        
        return $ret;
    }
    
    function _getNowSystemPlaceholder($placeholder){
        $format = strtr($placeholder, array(
            "%now[" => "",
            "]%" => "",
        ));
        
        return date($format);
    }
    
    function getXmlHeader(){
        $ret = parent::getXmlHeader();
        
        $ret = $this->_replaceSystemPlaceholder($ret);
        
        return $ret;
    }
    
    function getXmlFooter(){
        $ret = parent::getXmlFooter();
        
        $ret = $this->_replaceSystemPlaceholder($ret);
        
        return $ret;
    }
    
    function getXmlBody(){
        $ret = parent::getXmlBody();
        
        $ret = $this->_replaceSystemPlaceholder($ret);
        
        return $ret;
    }

    function duplicate(){
        $this->setFeedId(null);
        $this->setTitle($this->getTitle() . ' - duplicate');
        $this->setFilename($this->getFilename() . '_duplicate');
        $this->setGeneratedAt(null);
        $this->setDeliveryAt(null);
        $this->save();
    }

    public function getDownloadUrl()
    {
        $defaultStoreId = Mage::app()
            ->getWebsite(true)
            ->getDefaultGroup()
            ->getDefaultStoreId();

        $store = Mage::app()->getStore($defaultStoreId);

        $downloadUrl = $store->getUrl('amfeed/main/get', array(
            'file' => $this->getFilename()
        ));

        return $downloadUrl;
    }

    public function getRealFilename()
    {
        return 'export_' . $this->getId();
    }

    public function getOutputFilename()
    {
        return $this->compress();
    }

    public function getResponseFilename()
    {
        $filename = $this->getFilename() . $this->getFileExt();

        if ($this->getCompress() !== self::COMPRESS_NONE) {
            $filename .= '.' . $this->getCompress();
        }

        return $filename;
    }


    public function getStore()
    {
        if (!$this->_store) {
            $this->_store = Mage::app()->getStore($this->getStoreId());
        }
        return $this->_store;
    }

    public function compress()
    {
        $filename = $this->getRealFilename();
        $outputFilename = $filename;
        $compressor = null;

        if ($this->getCompress() === self::COMPRESS_ZIP) {
            $compressor = new Amasty_Feed_Model_Compressor_Zip();
        } else if ($this->getCompress() === self::COMPRESS_GZ) {
            $compressor =  new Mage_Archive_Gz();
        } else if ($this->getCompress() === self::COMPRESS_BZ) {
            $compressor =  new Mage_Archive_Bz();
        }

        if ($compressor){
            $outputFilename .= '.' . $this->getCompress();
        }

        if ($compressor && file_exists(Mage::helper('amfeed')->getDownloadPath('feeds', $filename)))
        {
            $packFilename = 'export'.$this->getFileExt();

            rename(
                Mage::helper('amfeed')->getDownloadPath('feeds', $filename),
                Mage::helper('amfeed')->getDownloadPath('feeds', $packFilename)
            );

            $compressor->pack(
                Mage::helper('amfeed')->getDownloadPath('feeds', $packFilename),
                Mage::helper('amfeed')->getDownloadPath('feeds', $outputFilename)
            );

            unlink(Mage::helper('amfeed')->getDownloadPath('feeds', $packFilename));
        }

        return $outputFilename;
    }
}
