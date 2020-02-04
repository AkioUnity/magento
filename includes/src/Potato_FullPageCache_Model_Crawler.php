<?php

class Potato_FullPageCache_Model_Crawler extends Mage_Core_Model_Abstract
{
    const USER_AGENT = 'MagentoCrawler';
    const LIMIT = 500;

    protected $_curl = null;

    /**
     *
     */
    protected function _construct()
    {
        $this->_init('po_fpc/crawler');
    }

    /**
     * @param $storeInfo
     *
     * @return $this
     */
    protected function _executeRequests($storeInfo)
    {
        $storeId = $storeInfo['store_id'];
        $options = array(
            CURLOPT_USERAGENT      => self::USER_AGENT,
            CURLOPT_SSL_VERIFYPEER => 0,
        );
        $threads = Potato_FullPageCache_Helper_Config::getAutoGenerationThreadNumber($storeId);
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            //apache crashed if $threads > 1
            $threads = 1;
        }
        if (!empty($storeInfo['cookie'])) {
            $options[CURLOPT_COOKIE] = $storeInfo['cookie'];
        }
        $urls = array();
        $offset = 0;
        while ($rewrites = $this->_getResource()->getRequestPaths($storeId, self::LIMIT, $offset)) {
            foreach ($rewrites as $rewriteRow) {
                if (@class_exists('Enterprise_UrlRewrite_Model_Resource_Url_Rewrite', false)) {
                    $url = $this->_getUrlByRewriteRow($rewriteRow, $storeInfo['base_url'], $storeId);
                    $urls[] = $url;
                } else {
                    $urls[]= $storeInfo['base_url'] . $this->_encodeUrlPath($rewriteRow['request_path']);
                }
                if (count($urls) == $threads) {
                    $this->_getCurl()->multiRequest($urls, $options);
                    $urls = array();
                }
            }
            $offset += self::LIMIT;
        }
        if (!empty($urls)) {
            $this->_getCurl()->multiRequest($urls, $options);
        }
        return $this;
    }

    /**
     * @param $rewriteRow
     * @param $baseUrl
     * @param $storeId
     *
     * @return string
     * @throws Exception
     */
    protected function _getUrlByRewriteRow($rewriteRow, $baseUrl, $storeId)
    {
        switch ($rewriteRow['entity_type']) {
            case Enterprise_Catalog_Model_Product::URL_REWRITE_ENTITY_TYPE:
                $url = $baseUrl . Mage::helper('enterprise_catalog')->getProductRequestPath(
                    $rewriteRow['request_path'], $storeId, $rewriteRow['category_id']
                );
                break;
            case Enterprise_Catalog_Model_Category::URL_REWRITE_ENTITY_TYPE:
                $url = $baseUrl . Mage::helper('enterprise_catalog')->getCategoryRequestPath(
                    $rewriteRow['request_path'], $storeId
                );
                break;
            default:
                throw new Exception('Unknown entity type ' . $rewriteRow['entity_type']);
                break;
        }
        return $url;
    }

    /**
     * @return $this
     */
    public function process()
    {
        if (!Mage::app()->useCache('po_fpc') || Potato_FullPageCache_Helper_Config::getIsCanUseUserAgent()) {
            return $this;
        }
        try {
            foreach ($this->_getStoresInfo() as $storeInfo) {
                if (!Potato_FullPageCache_Helper_Config::getIsAutoGenerationEnabled($storeInfo['store_id'])) {
                    continue;
                }
                $this->_executeRequests($storeInfo);
            }
        } catch(Exception $e) {
            Mage::printException($e);
        }
        return $this;
    }

    /**
     * @return null|Varien_Http_Adapter_Curl
     */
    protected function _getCurl()
    {
        if (null === $this->_curl) {
            $this->_curl = new Varien_Http_Adapter_Curl();
        }
        return $this->_curl;
    }

    /**
     * @return array
     */
    protected function _getStoresInfo()
    {
        $baseUrls = array();
        foreach (Mage::app()->getStores() as $store) {
            $baseUrl			= Mage::app()->getStore($store)->getBaseUrl();
            $defaultCurrency	= Mage::app()->getStore($store)->getDefaultCurrencyCode();
            $cookie = 'store=' . $store->getId() . ';group=' . Mage_Customer_Model_Group::NOT_LOGGED_IN_ID . ';';
            $baseUrls[]= array(
                'store_id' => $store->getId(),
                'base_url' => $baseUrl,
                'cookie'   => $cookie
            );
            $currencies = $store->getAvailableCurrencyCodes(true);
            foreach ($currencies as $currencyCode) {
                if ($currencyCode != $defaultCurrency) {
                    $baseUrls[]= array(
                        'store_id' => $store->getId(),
                        'base_url' => $baseUrl,
                        'cookie'   => $cookie . 'currency=' . $currencyCode . ';'
                    );
                }
            }
        }
        return $baseUrls;
    }

    /**
     * @param $path
     *
     * @return string
     */
    protected function _encodeUrlPath($path)
    {
        return implode('/', array_map('rawurlencode', explode('/', $path)));
    }
}