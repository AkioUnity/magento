<?php
/**
 * Copyright 2016 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @category   Google
 * @package    Google_Shoppingconnect
 * @copyright  Copyright 2016 Google Inc.
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache License Version 2.0
 */

/**
 * Top class for the Google Shopping Connect Api extension.
 */
class Google_Shoppingconnect_Model_Api extends Mage_Api_Model_Resource_Abstract
{
    const GOOGLE_SHOPPING_CONNECT_MODULE_NAME = 'Google_Shoppingconnect';
    const ORDER_BY_ATTRIBUTE = 'id';
    const DEFAULT_STORE_VIEW = 0;

    // As in app/code/community/Google/Shoppingconnect/etc/api.xml
    const FAULT_UNKNOWN_STORE_VIEW = 'unknown_store_view';
    const FAULT_BAD_PAGE_SIZE = 'bad_page_size';
    const FAULT_BAD_CURRENT_PAGE = 'bad_current_page';
    const FAULT_MISSING_VERIFICATION_TAG_CONTENT = 'missing_verification_tag_content';
    const FAULT_MISSING_URL = 'missing_url';

    /**
     * Adds the Google website verification meta tag content to be rendered on all pages.
     */
    public function add_verification_tag($info)
    {
        $tagContent = $info['content'];
        $url = $info['url'];
        if ($tagContent === null) {
            $this->_fault(self::FAULT_MISSING_VERIFICATION_TAG_CONTENT);
        }
        if ($url === null) {
            $this->_fault(self::FAULT_MISSING_URL);
        }
        Mage::helper('googleshoppingconnect')->addVerificationTag($tagContent, $url);
        return true;
    }

    /**
     * Returns basic module and shop information.
     */
    public function basic_shop_info()
    {
        return $this->sanitizeArrayForXml($this->get_shop_info(false));
    }

    /**
     * Returns module and shop information.
     */
    public function shop_info()
    {
        return $this->sanitizeArrayForXml($this->get_shop_info(true));
    }

    protected function get_shop_info($returnExtraInfo)
    {
        $result = array(
            'module_version' => $this->getGoogleShoppingConnectVersion(),
            'shop_edition' => Mage::getEdition(),
            'shop_version' => Mage::getVersion(),
        );
        if ($returnExtraInfo) {
            $result = array_merge($result, array(
                'attributes' => $this->getAttributes(),
                'currencies' => $this->getCurrencies(),
                'default_store_view_id' => Mage::app()->getDefaultStoreView()->getId(),
                'store_views' => $this->getStoreViews(),
                'stores' => $this->getStores(),
                'websites' => $this->getWebsites()
            ));
        }
        return $result;
    }

    /**
     * Returns product information.
     */
    public function products($storeViewId, $pageSize, $currentPage, $filters = null)
    {
        if ($pageSize < 1) {
            $this->_fault(self::FAULT_BAD_PAGE_SIZE);
        }
        if ($currentPage < 1) {
            $this->_fault(self::FAULT_BAD_CURRENT_PAGE);
        }
        try {
            $storeView = Mage::app()->getStore($storeViewId);
        } catch (Mage_Core_Model_Store_Exception $e) {
            $this->_fault(self::FAULT_UNKNOWN_STORE_VIEW);
        }
        $products = Mage::getModel('catalog/product')
            ->getCollection()
            ->setStoreId($storeView->getId())
            ->setOrder(self::ORDER_BY_ATTRIBUTE)
            ->addAttributeToSelect('*')
            ->setPageSize($pageSize)
            ->setCurPage($currentPage);
        /** @var $apiHelper Mage_Api_Helper_Data */
        $apiHelper = Mage::helper('api');
        $parsedFilters = $apiHelper->parseFilters($filters);
        try {
            foreach ($parsedFilters as $field => $value) {
                $products->addFieldToFilter($field, $value);
            }
        } catch (Mage_Core_Exception $e) {
            $this->_fault('filters_invalid', $e->getMessage());
        }
        $lastPageNumber = $products->getLastPageNumber();
        if ($lastPageNumber < $currentPage) {
            // Iterating over $products will give us the products from the last valid page, even if
            // the requested page is larger than that. To signal to the caller that it has reached
            // the end of the product list, return an empty result.
            return array();
        }
        $productsById = $this->getProductsById($products);
        $productsInfo = $this->getProductsInformation($productsById, $storeView);
        $extraProductsIds = array();
        $productsInfoById = array();
        foreach ($productsInfo as $productInfo) {
            $extraProductsIds = array_merge($extraProductsIds,
                $productInfo['configurable_products']['parent_product_ids']);
            $extraProductsIds = array_merge($extraProductsIds,
                $productInfo['bundled_products']['parent_product_ids']);
            if (isset($productInfo['grouped_products']['backward_links'])) {
                foreach ($productInfo['grouped_products']['backward_links'] as $linkedProduct) {
                    $extraProductsIds[] = $linkedProduct['linked_product_id'];
                }
            }
            $productsInfoById[$productInfo['id']] = $productInfo;
        }
        $extraProductsIds = array_diff(array_unique($extraProductsIds), array_keys($productsById));
        $extraProducts = Mage::getModel('catalog/product')
            ->getCollection()
            ->setStoreId($storeView->getId())
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('entity_id', array('in' => $extraProductsIds));
        $extraProductsById = $this->getProductsById($extraProducts);
        $productsById = $extraProductsById + $productsById;
        $extraProductsInfo = $this->getProductsInformation($extraProductsById, $storeView);
        foreach ($extraProductsInfo as $extraProductInfo) {
            $productsInfoById[$extraProductInfo['id']] = $extraProductInfo;
        }
        foreach ($productsInfo as &$productInfo) {
            foreach ($productInfo['configurable_products']['parent_product_ids'] as $parentProductId) {
                $parentProduct = $productsById[(int)$parentProductId];
                if (is_null($parentProduct)) {
                    continue;
                }
                $parentProductInfo = $productsInfoById[(int)$parentProductId];
                $attributes = $this->getCustomOptionAttributes($parentProductInfo, $productInfo);
                $parentProduct->addCustomOption('attributes', $attributes);
                // Force recalculation of the final price in getPricesForProduct().
                $parentProduct->setFinalPrice(null);
                $productInfo['configurable_products']['parent_products'][] = array(
                    'parent_product' => $parentProductInfo,
                    'price' => $this->getPricesForProduct($parentProduct, $storeView)
                );
            }
            foreach ($productInfo['bundled_products']['parent_product_ids'] as $parentProductId) {
                $product['bundled_products']['parent_products'][] =
                    $productsInfoById[$parentProductId];
            }
            if (isset($productInfo['grouped_products']['backward_links'])) {
                foreach ($productInfo['grouped_products']['backward_links'] as &$linkedProduct) {
                    $linkedProduct['linked_product'] =
                        $productsInfoById[$linkedProduct['linked_product_id']];
                }
            }
        }
        return $this->sanitizeArrayForXml($productsInfo);
    }

    protected function getProductsById($products)
    {
        $products->addOptionsToResult();
        $result = array();
        foreach ($products as $product) {
            $result[$product->getId()] = $product;
        }
        return $result;
    }

    protected function getProductsInformation($products, $storeView)
    {
        $groupedProductLinks = $this->getGroupedProductLinks();
        $result = array();
        foreach ($products as $product) {
            // To get store view specific values (e.g. url), we need to explicitly set the store
            // view on the product.
            $product->setStoreId($storeView->getId());
            $productInfo = $this->getGeneralInformationForProduct(
                $product, $groupedProductLinks);
            $productInfo['image_attributes'] = $this->getImagesForProduct($product);
            $productInfo['all_attributes'] = $this->getAllAttributesForProduct($product);
            $productInfo['in_stock'] = $this->isProductInStock($product, $storeView);
            $productInfo['prices'] = $this->getPricesForProduct($product, $storeView);
            $productInfo['default_attributes'] = $product->getResource()->getDefaultAttributes();
            $result[] = $productInfo;
        }
        return $result;
    }

    protected function getAllAttributesForProduct($product)
    {
        $allAttributes = array();
        foreach ($product->getAttributes() as $attribute) {
            $allAttributes[] = $this->createAttribute($attribute->getAttributeCode(),
                $product->getData($attribute->getAttributeCode()));
        }

        return $allAttributes;
    }

    protected function getGeneralInformationForProduct($product, $groupedProductLinks)
    {
        $result = array(
            'available' => $product->isAvailable(),
            'bundled_products' => $this->getBundledProductInformation($product),
            'can_be_shown' => Mage::helper('catalog/product')->canShow($product),
            'categories' => $product->getCategoryIds(),
            'configurable_products' => $this->getConfigurableProductInformation($product),
            'description' => $product->getDescription(),
            'enabled' => $product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED,
            'id' => $product->getId(),
            'name' => $product->getName(),
            'options' => $this->getProductOptions($product),
            'rating_summary' => $product->getRatingSummary(),
            'salable' => $product->isSalable(),
            'set' => $product->getAttributeSetId(),
            'short_description' => $product->getShortDescription(),
            'sku' => $product->getSku(),
            'url' => $product->getProductUrl(),
            'type' => $product->getTypeId(),
            'visible_in_catalog' => $product->isVisibleInCatalog(),
            'visible_in_site' => $product->isVisibleInSiteVisibility(),
            'weight' => $product->getWeight(),
        );
        if (isset($groupedProductLinks[$product->getId()])) {
            $result['grouped_products'] = $groupedProductLinks[$product->getId()];
        }
        return $result;
    }

    protected function getProductOptions($product)
    {
        $result = array();
        foreach ($product->getOptions() as $option) {
            $optionInfo = array(
                'default_price' => $option->getDefaultPrice(),
                'default_price_type' => $option->getDefaultPriceType(),
                'default_title' => $option->getDefaultTitle(),
                'option_id' => $option->getOptionId(),
                'price' => $option->getStorePrice(),
                'price_type' => $option->getPriceType(),
                'required' => $option->getIsRequire(),
                'sort_order' => $option->getSortOrder(),
                'store_price' => $option->getStorePrice(),
                'store_price_type' => $option->getStorePriceType(),
                'store_title' => $option->getStoreTitle(),
                'title' => $option->getTitle(),
                'type' => $option->getType(),
            );
            foreach ($option->getValues() as $optionValue) {
                $optionInfo['options'][] = array(
                    'default_price' => $optionValue->getDefaultPrice(),
                    'default_price_type' => $optionValue->getDefaultPriceType(),
                    'default_title' => $optionValue->getDefaultTitle(),
                    'option_id' => $optionValue->getOptionId(),
                    'price' => $optionValue->getPrice(),
                    'price_type' => $optionValue->getPriceType(),
                    'sort_order' => $optionValue->getSortOrder(),
                    'store_price' => $optionValue->getStorePrice(),
                    'store_price_type' => $optionValue->getStorePriceType(),
                    'store_title' => $optionValue->getStoreTitle(),
                    'title' => $optionValue->getTitle(),
                );
            }
            $result[] = $optionInfo;
        }
        return $result;
    }

    protected function getConfigurableProductInformation($product)
    {
        $result = array (
            'parent_product_ids' => Mage::getResourceSingleton('catalog/product_type_configurable')
            ->getParentIdsByChild($product->getId())
        );
        if ($product->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            return $result;
        }
        $configurableType = $product->getTypeInstance(true);
        $result['configurable_attributes'] =
            $configurableType->getConfigurableAttributesAsArray($product);
        $childrenIds = Mage::getResourceSingleton('catalog/product_type_configurable')
            ->getChildrenIds($product->getId());
        if (array_key_exists(0, $childrenIds)) {
            $result['children_product_ids'] = array_keys($childrenIds[0]);
        } else {
            $result['children_product_ids'] = array();
        }
        return $result;
    }

    protected function getBundledProductInformation($product)
    {
        $result = array (
            'parent_product_ids' =>
                Mage::getResourceSingleton('bundle/selection')->getParentIdsByChild($product->getId())
        );
        if ($product->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            return $result;
        }
        $bundleType = $product->getTypeInstance(true);
        $options = $bundleType->getOptionsCollection($product);
        $optionIds = $bundleType->getOptionsIds($product);
        $selectionCollection = $bundleType->getSelectionsCollection($optionIds, $product);
        $options->appendSelections($selectionCollection);
        foreach ($options as $option) {
            $selections = array();
            foreach ($option->getSelections() as $selection) {
                $selections[] = array(
                    'default_quantity' => $selection->getSelectionQty(),
                    'is_default' => $selection->getIsDefault(),
                    'position' => $selection->getPosition(),
                    'product_id' => $selection->getProductId(),
                    'user_defined_quantity' => $selection->getSelectionCanChangeQty(),
                );
            }
            $result['options'][] = array(
                'default_title' => $option->getDefaultTitle(),
                'option_id' => $option->getId(),
                'position' => $option->getPosition(),
                'required' => $option->getRequired(),
                'selections' => $selections,
                'type' => $option->getType(),
            );
        }
        return $result;
    }

    protected function getGroupedProductLinks()
    {
        $links = Mage::getSingleton('catalog/product_link')->useGroupedLinks()->getLinkCollection();
        // Product links are also used for related, up-sells and cross sells. We are only interested
        // in grouped product links.
        $links->addLinkTypeIdFilter();
        $links->joinAttributes();

        $result = array();
        foreach ($links as $link) {
            $result[$link->getProductId()]['forward_links'][] = array(
                'linked_product_id' => $link->getLinkedProductId(),
                'default_quantity' => $link->getQty(),
                'position' => $link->getPosition(),
            );
            $result[$link->getLinkedProductId()]['backward_links'][] = array(
                'linked_product_id' => $link->getProductId(),
                'default_quantity' => $link->getQty(),
                'position' => $link->getPosition(),
            );
        }
        return $result;
    }

    protected function getImagesForProduct($product)
    {
        // Load the image information.
        $product->getResource()->getAttribute('media_gallery')->getBackend()->afterLoad($product);
        // Return the well known images.
        $imageInfo = array(
            'image_url' => $this->getMediaUrl($product->getImage()),
            'small_image_url' => $this->getMediaUrl($product->getSmallImage()),
            'thumbnail_url' => $this->getMediaUrl($product->getThumbnail())
        );
        // Return all images, augmented with the generated url.
        $mediaGallery = array();
        $productMediaGallery = $product->getMediaGallery();
        foreach ($productMediaGallery['images'] as $image) {
            $image['url'] = $this->getMediaUrl($image['file']);
            $mediaGallery[] = $image;
        }
        $imageInfo['media_gallery'] = $mediaGallery;
        return $imageInfo;
    }

    protected function getMediaUrl($image)
    {
        $mediaConfig = Mage::getSingleton('catalog/product_media_config');
        return $image == 'no_selection' ? '' : $mediaConfig->getMediaUrl($image);
    }

    protected function isProductInStock($product, $storeView)
    {
        $stockItem = Mage::getModel('cataloginventory/stock_item');
        $stockItem->setStoreId($storeView->getStoreId());
        $stockItem->getResource()->loadByProductId($stockItem, $product->getId());
        $stockItem->setOrigData();
        return $stockItem->getId() && $stockItem->getIsInStock();
    }

    protected function getPricesForProduct($product, $storeView)
    {
        $result = array();
        foreach ($this->getValidAvailableCurrencyCodes($storeView) as $currencyCode) {
            $basePrice = $this->getPriceWithAndWithoutTaxInCurrency(
                $storeView, $product, $product->getPrice(), $currencyCode);
            $finalPriceBeforeFixedTaxes = $this->getPriceWithAndWithoutTaxInCurrency(
                $storeView, $product, $product->getFinalPrice(), $currencyCode);
            $fixedTaxes = $this->getFixedTaxesForProduct($product, $storeView, $currencyCode);
            $result[] = array (
                'currency_code' => $currencyCode,
                'base_price' => $basePrice,
                'final_price_before_fixed_taxes' => $finalPriceBeforeFixedTaxes,
                'special_price' => $this->getSpecialPrice($product, $storeView, $currencyCode),
                'catalog_rule_prices' => $this->getCatalogRulePrices($product, $storeView, $currencyCode),
                'fixed_taxes' => $fixedTaxes,
                'final_price' => $this->getFinalPrice($storeView, $finalPriceBeforeFixedTaxes, $fixedTaxes),
                'bundled_product_price' => $this->getPricesForBundledProduct(
                    $product, $storeView, $currencyCode, $fixedTaxes)
            );
        }
        return $result;
    }

    protected function getPricesForBundledProduct($product, $storeView, $currencyCode, $fixedTaxes)
    {
        if ($product->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            return null;
        }
        $minPriceWithTax = $product->getPriceModel()->getTotalPrices($product, 'min', true);
        $minPriceWithoutTax = $product->getPriceModel()->getTotalPrices($product, 'min', false);
        $maxPriceWithTax = $product->getPriceModel()->getTotalPrices($product, 'max', true);
        $maxPriceWithoutTax = $product->getPriceModel()->getTotalPrices($product, 'max', false);
        return array (
            'min_price_with_tax_before_fixed_taxes' =>
                $this->currencyConvert($storeView, $minPriceWithTax, $currencyCode),
            'min_price_without_tax_before_fixed_taxes' =>
                $this->currencyConvert($storeView, $minPriceWithoutTax, $currencyCode),
            'max_price_with_tax_before_fixed_taxes' =>
                $this->currencyConvert($storeView, $maxPriceWithTax, $currencyCode),
            'max_price_without_tax_before_fixed_taxes' =>
                $this->currencyConvert($storeView, $maxPriceWithoutTax, $currencyCode),
            'min_price_with_tax' => $storeView->roundPrice(
                $this->currencyConvert($storeView, $minPriceWithTax, $currencyCode)
                    + $fixedTaxes['fixed_tax_with_tax']),
            'min_price_without_tax' => $storeView->roundPrice(
                $this->currencyConvert($storeView, $minPriceWithoutTax, $currencyCode)
                    + $fixedTaxes['fixed_tax_without_tax']),
            'max_price_with_tax' => $storeView->roundPrice(
                $this->currencyConvert($storeView, $maxPriceWithTax, $currencyCode)
                    + $fixedTaxes['fixed_tax_with_tax']),
            'max_price_without_tax' => $storeView->roundPrice(
                $this->currencyConvert($storeView, $maxPriceWithoutTax, $currencyCode)
                    + $fixedTaxes['fixed_tax_without_tax'])
        );
    }

    protected function getFinalPrice($storeView, $finalPriceBeforeFixedTaxes, $fixedTaxes)
    {
        return array(
            'price_with_tax' => $storeView->roundPrice(
                $finalPriceBeforeFixedTaxes['price_with_tax'] + $fixedTaxes['fixed_tax_with_tax']),
            'price_without_tax' => $storeView->roundPrice(
                $finalPriceBeforeFixedTaxes['price_without_tax'] + $fixedTaxes['fixed_tax_without_tax']),
        );
    }

    protected function getValidAvailableCurrencyCodes($storeView)
    {
       // Only currencies that have exchange rates available are valid.
       $currencyRates = Mage::getModel('directory/currency')
           ->getCurrencyRates($storeView->getBaseCurrencyCode(), $storeView->getAvailableCurrencyCodes());
       $currencyCodesWithRates = array_keys($currencyRates);
       $currencyCodesWithRates[] = $storeView->getBaseCurrencyCode();
       return array_unique($currencyCodesWithRates);
    }

    protected function getSpecialPrice($product, $storeView, $currencyCode)
    {
        if (!$product->hasSpecialPrice()) {
            return array();
        }
        $specialPrice = $this->getPriceWithAndWithoutTaxInCurrency(
            $storeView, $product, $product->getSpecialPrice(), $currencyCode);
        return array(
            'special_price' => $specialPrice,
            'special_from_date' => $this->getIsoDateTime($product->getSpecialFromDate(), $storeView),
            'special_to_date' => $this->getIsoDateTime($product->getSpecialToDate(), $storeView),
        );
    }

    /**
     * Returns the DateTime formatted to ISO 8601.
     */
    protected function getIsoDateTime($dateTime, $storeView)
    {
        $storeViewTimezone = new DateTimeZone(
            Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE, $storeView));
        $dateTime = new DateTime($dateTime, $storeViewTimezone);
        return $dateTime->format("c");
    }

    protected function getCatalogRulePrices($product, $storeView, $currencyCode)
    {
        $resourceModel = Mage::getModel('googleshoppingconnect/resource_catalogruleproductprice');
        $ruleProductPrices = $resourceModel->getCatalogRulePrices($product->getId(), $storeView->getWebsiteId());
        $result = array();
        foreach ($ruleProductPrices as $ruleProductPrice) {
            $ruleProductPriceWithAndWithoutTax = $this->getPriceWithAndWithoutTaxInCurrency(
                $storeView, $product, $ruleProductPrice['rule_price'], $currencyCode);
            $result[] = array(
                'price' => $ruleProductPriceWithAndWithoutTax,
                'start_date' => $this->getIsoDateTime($ruleProductPrice['latest_start_date'], $storeView),
                'end_date' => $this->getIsoDateTime($ruleProductPrice['earliest_end_date'], $storeView),
                'rule_date' => $this->getIsoDateTime($ruleProductPrice['rule_date'], $storeView),
            );
        }
        return $result;
    }

    protected function getPriceWithAndWithoutTaxInCurrency($storeView, $product, $price, $currencyCode)
    {
        $prices = $this->getPriceWithAndWithoutTax($product, $price, $storeView);
        return array(
            'price_with_tax' => $this->currencyConvert($storeView, $prices['price_with_tax'], $currencyCode),
            'price_without_tax' => $this->currencyConvert($storeView, $prices['price_without_tax'], $currencyCode)
        );
    }

    protected function currencyConvert($storeView, $price, $currencyCode)
    {
        $baseCurrencyCode = $storeView->getBaseCurrencyCode();
        return $storeView->roundPrice(Mage::helper('directory')->currencyConvert(
            $price, $baseCurrencyCode, $currencyCode, $storeView));
    }

    protected function getPriceWithAndWithoutTax($product, $price, $storeView)
    {
        return array(
            'price_with_tax' => $this->getPriceWithTax($product, $price, $storeView),
            'price_without_tax' => $this->getPriceWithoutTax($product, $price, $storeView)
        );
    }

    protected function getPriceWithTax($product, $price, $storeView)
    {
        $taxHelper = Mage::helper('googleshoppingconnect/tax');
        $priceIncludesTax = $taxHelper->priceIncludesTax($storeView->getId());
        return $taxHelper->getPrice(
            $product, $price, true, false, false, null, $storeView->getId(), $priceIncludesTax);
    }

    protected function getPriceWithoutTax($product, $price, $storeView)
    {
        $taxHelper = Mage::helper('googleshoppingconnect/tax');
        $priceIncludesTax = $taxHelper->priceIncludesTax($storeView->getId());
        return $taxHelper->getPrice(
            $product, $price, false, false, false, null, $storeView->getId(), $priceIncludesTax);
    }

    protected function getFixedTaxesForProduct($product, $storeView, $currencyCode)
    {
        $helper = Mage::helper('weee');
        $fixedTaxWithoutTax = $helper->getAmountForDisplay($product);
        $fixedTaxWithTax = $fixedTaxWithoutTax;
        if ($helper->isTaxable()) {
            $fixedTaxWithTax = $helper->getAmountInclTaxes(
                $helper->getProductWeeeAttributesForRenderer($product, null, null, null, true));
        }
        return array(
            'fixed_tax_without_tax' => $this->currencyConvert(
                $storeView, $fixedTaxWithoutTax, $currencyCode),
            'fixed_tax_with_tax' => $this->currencyConvert(
                $storeView, $fixedTaxWithTax, $currencyCode)
        );
    }

    protected function createAttribute($attributeCode, $attributeValue)
    {
        return array(
            'attribute_code' => $attributeCode,
            'attribute_value' => $attributeValue
        );
    }

    protected function getGoogleShoppingConnectVersion()
    {
        /* @var Varien_Simplexml_Element $moduleConfig */
        $moduleConfig = Mage::getConfig()->getModuleConfig(
            self::GOOGLE_SHOPPING_CONNECT_MODULE_NAME);
        if ($moduleConfig)
        {
            $moduleConfig = $moduleConfig->asArray();
            if(isset($moduleConfig['version']))
                return $moduleConfig['version'];
        }
        return '';
    }

    protected function getStoreViews()
    {
        $storeViews = Mage::app()->getStores();
        $result = array();
        foreach ($storeViews as $storeView) {
            $storeViewInformation = array();
            $storeViewInformation += $this->getGeneneralInformationForStoreView($storeView);
            $storeViewInformation += $this->getCurrencyInformationForStoreView($storeView);
            $storeViewInformation += $this->getContactInformationForStoreView($storeView);
            $storeViewInformation += $this->getTaxInformationForStoreView($storeView);
            $result[] = $storeViewInformation;
        }

        return $result;
    }

    protected function getWebsites()
    {
        $websites = Mage::app()->getWebsites();
        $result = array();
        foreach ($websites as $website) {
            $result[$website->getId()] = array(
                // The currency used to configure the prices in the Magento Admin.
                'base_currency_code'=> $website->getBaseCurrencyCode(),
                'default_store_id' => $website->getDefaultGroupId(),
                'default_store_view_id' => $website->getDefaultStore()->getId(),
                'id' => $website->getId(),
                'is_default' => $website->getIsDefault(),
                'name' => $website->getName(),
            );
        }

        return $result;
    }

    protected function getStores()
    {
        $stores = Mage::app()->getGroups();
        $result = array();
        foreach ($stores as $store) {
            $result[$store->getId()] = array(
                'id' => $store->getId(),
                'name' => $store->getName(),
                'default_store_view_id' => $store->getDefaultStoreId(),
                'root_category_id' => $store->getRootCategoryId(),
                'website_id' => $store->getWebsiteId(),
            );
        }
        return $result;
    }

    protected function getCurrencies()
    {
        $currencyModel = Mage::getModel('directory/currency');
        $currencies = $currencyModel->getConfigAllowCurrencies();
        $defaultCurrencies = $currencyModel->getConfigBaseCurrencies();
        return array(
            'rates' => $currencyModel->getCurrencyRates($defaultCurrencies, $currencies),
        );
    }

    protected function getGeneneralInformationForStoreView($storeView)
    {
        return array(
            'address' => Mage::getStoreConfig('general/store_information/address', $storeView),
            'base_url' => $storeView->getBaseUrl(),
            'code' => $storeView->getCode(),
            'country' => Mage::getStoreConfig(Mage_Core_Helper_Data::XML_PATH_MERCHANT_COUNTRY_CODE, $storeView),
            'default_country' => Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_COUNTRY, $storeView),
            'frontend_name' => $storeView->getFrontendName(),
            'is_active' => $storeView->getIsActive(),
            'locale' => Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE, $storeView),
            'name' => $storeView->getName(),
            'phone' => Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_PHONE, $storeView),
            'root_category_id' => $storeView->getRootCategoryId(),
            'session_timeout' => Mage::getStoreConfig('api/config/session_timeout', $storeView),
            'store_id' => $storeView->getGroupId(),
            'store_in_url' => Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_IN_URL, $storeView),
            'store_view_id' => $storeView->getId(),
            'timezone' => Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE, $storeView),
            'website_id' => $storeView->getWebsiteId(),
        );
    }

    protected function getCurrencyInformationForStoreView($storeView)
    {
        return array(
            // The currency used to configure the prices in the Magento Admin.
            // Should be the same as the one configured at website level.
            'base_currency_code' => $storeView->getBaseCurrencyCode(),
            // The currency used by default to display prices in the frontend.
            'default_currency_code' => $storeView->getDefaultCurrencyCode(),
            // The currencies the user can choose from when seeing prices.
            'available_currency_codes' => $this->getValidAvailableCurrencyCodes($storeView)
        );
    }

    protected function getContactInformationForStoreView($storeView)
    {
        return array(
            'contact_us_email' => Mage::getStoreConfig('contacts/email/recipient_email', $storeView),
            'email_general_contact_email' => Mage::getStoreConfig('trans_email/ident_general/email', $storeView),
            'email_general_contact_name' => Mage::getStoreConfig('trans_email/ident_general/name', $storeView),
            'email_sales_contact_email' => Mage::getStoreConfig('trans_email/ident_sales/email', $storeView),
            'email_sales_contact_name' => Mage::getStoreConfig('trans_email/ident_sales/name', $storeView),
        );
    }

    protected function getTaxInformationForStoreView($storeView)
    {
        $taxHelper = Mage::helper('tax');
        return array(
            'display_only_price_including_tax' => $taxHelper->displayPriceIncludingTax($storeView),
            'display_only_price_excluding_tax' => $taxHelper->displayPriceExcludingTax($storeView),
            'display_price_including_and_excluding_tax' => $taxHelper->displayBothPrices($storeView),
        );
    }

    protected function getAttributes()
    {
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')->getItems();
        $result = array();
        foreach ($attributes as $attribute) {
            $result[] = array (
                'attribute_code' => $attribute->getAttributeCode(),
                'attribute_id' => $attribute->getAttributeId(),
                'default_value' => $attribute->getDefaultValue(),
                'type' => $attribute->getFrontendInput(),
                'is_configurable' => $attribute->getIsConfigurable(),
                'apply_to' => $attribute->getApplyTo(),
                'is_unique' => $attribute->getIsUnique(),
                'is_required' => $attribute->getIsRequired(),
                'default_label' => $attribute->getFrontendLabel(),
                'labels' => $this->getAttributeLabels($attribute),
                'options' => $this->getAttributeOptions($attribute),
            );
        }
        return $result;
    }

    protected function getAttributeLabels($attribute)
    {
        $result = array();
        foreach ($attribute->getStoreLabels() as $storeViewId => $label) {
            $result[] = array(
                'store_view_id' => $storeViewId,
                'label' => $label
            );
        }
        return $result;
    }

    protected function getAttributeOptions($attribute)
    {
        return array(
            'default_attribute_options' => $this->getDefaultAttributeOptions($attribute),
            'store_view_attribute_options' => $this->getStoreViewAttributeOptions($attribute)
        );
    }

    protected function getDefaultAttributeOptions($attribute)
    {
        $attributeDetails = Mage::getSingleton("eav/config")->getAttribute(
            Mage_Catalog_Model_Product::ENTITY, $attribute->getAttributeCode());
        if ($attributeDetails->usesSource()) {
            return $attributeDetails->getSource()->getAllOptions(false);
        } else {
            return array();
        }
    }

    protected function getStoreViewAttributeOptions($attribute)
    {
        $attributeDetails = Mage::getSingleton("eav/config")->getAttribute(
            Mage_Catalog_Model_Product::ENTITY, $attribute->getAttributeCode());
        $storeViews = Mage::app()->getStores();
        $result = array();
        foreach ($storeViews as $storeView) {
            if ($attributeDetails->usesSource()) {
                $optionCollection = $attributeDetails->
                    setStoreId($storeView->getId())->getSource()->getAllOptions(false);
            } else {
                $optionCollection = array();
            }
            $result[] = array(
                'store_view_id' => $storeView->getId(),
                'options' => $optionCollection
            );
        }
        return $result;
    }

    protected function getCustomOptionAttributes($parentProductInfo, $productInfo)
    {
        $attributes = array();
        foreach ($parentProductInfo['configurable_products']['configurable_attributes'] as $configurableAttribute) {
            foreach ($productInfo['all_attributes'] as $attribute) {
                if ($attribute['attribute_code'] == $configurableAttribute['attribute_code']) {
                    $attributes[$configurableAttribute['attribute_id']] = $attribute['attribute_value'];
                }
            }
        }
        return serialize($attributes);
    }

    protected function sanitizeArrayForXml($array) {
        if (!is_array($array)) {
            return;
        }
        $helper = array();
        foreach ($array as $key => $value) {
            $helper[$this->sanitizeValueForXml($key)] = is_array($value) ? $this->sanitizeArrayForXml($value) : $this->sanitizeValueForXml($value);
        }
        return $helper;
    }

    protected function sanitizeValueForXml($value) {
        if (!is_string($value) || empty($value)) {
            return $value;
        }
        $result = "";
        $length = strlen($value);
        for ($i=0; $i < $length; $i++) {
            $current = ord($value{$i});
            if (($current == 0x9) ||
                ($current == 0xA) ||
                ($current == 0xD) ||
                (($current >= 0x20) && ($current <= 0xD7FF)) ||
                (($current >= 0xE000) && ($current <= 0xFFFD)) ||
                (($current >= 0x10000) && ($current <= 0x10FFFF))) {
                $result .= chr($current);
            }
        }
        return $result;
    }
}
