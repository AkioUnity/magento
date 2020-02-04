<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

    class Amasty_Feed_Model_Attribute_Compound_Configurableurl extends Amasty_Feed_Model_Attribute_Compound_Abstract
    {
        function prepareCollection($collection){
            
            $attributesCollection = $this->_feed->loadAttributesCollection();
            $attributesItems = $attributesCollection->getItems();
            
            foreach($this->_feed->getSuperAttributesCollection()->getData() as $superAtttribite){
                
                if (isset($attributesItems[$superAtttribite['attribute_id']])){
                    
                    $atttribite = $attributesItems[$superAtttribite['attribute_id']];
                    
                    $collection->addAttribute($atttribite->getAttributeCode(), $this->_feed->getStoreId());
                    
                }
            }
        }
        
        function getCompoundData($productData){
            $url = '';
            
            if ($productData['parent_id']){
                $url = $this->_feed->getProductUrl($productData['parent_id']);
                
                $attributesCollection = $this->_feed->loadAttributesCollection();
                $attributesItems = $attributesCollection->getItems();
            
                $superAttributes = $this->_feed->getSuperAttributesData($productData['parent_id']);
                
                $params = array();
                
                if (is_array($superAttributes)) {
                    foreach($superAttributes as $superAttribute) {
                        $atttribite = $attributesItems[$superAttribute['attribute_id']];

                        if ($atttribite) {
                            $code = $atttribite->getAttributeCode();

                            $params[] = $superAttribute['attribute_id'] . "=" . $productData[$code];
                        }
                    }
                }
                if (count($params) > 0){
                   $url .= "#" . implode("&", $params);
                }            
            } else {
                $url = $this->_feed->getProductUrl($productData['entity_id']);
            }

            return $url;
        }
    }