<?php

class Potato_FullPageCache_Model_Cache
{
    //store id cookie - used in page id and init current store
    const STORE_COOKIE_NAME             = 'store';

    //current currency - used in page id
    const CURRENCY_COOKIE_NAME          = 'currency';

    //current customer group - used in page id
    const CUSTOMER_GROUP_ID_COOKIE_NAME = 'group';

    //config cache id
    const CONFIG_CACHE_ID               = 'PO_FPC_MAGE_CONFIG';

    //cache config id
    const PO_FPC_CONFIG_CACHE_ID        = 'PO_FPC_SELF_CONFIG';

    //config cache lifetime 3 days
    const CONFIG_CACHE_LIFETIME         = 86400;

    //block cache lifetime 3 day
    const BLOCK_CACHE_LIFETIME          = 86400;

    //default block processor class name
    const DEFAULT_BLOCK_PROCESSOR_CLASS = 'Potato_FullPageCache_Model_Processor_Block_Default';

    const SESSION_BLOCK_PROCESSOR_CLASS = 'Potato_FullPageCache_Model_Processor_Block_Session';

    //block cache tag
    const BLOCK_TAG                     = 'block';

    //block cache tag
    const SESSION_BLOCK_TAG             = 'session_block';

    //system cache tag
    const SYSTEM_TAG                    = 'system';

    //product cache tag
    const PRODUCT_TAG                   = 'product';

    //product cache tag
    const CATEGORY_TAG                  = 'category';

    //product cache tag
    const CMS_TAG                       = 'cms';

    //global blocks index
    const BLOCK_TYPE_GLOBAL             = 'global';

    const CACHE_STORE                   = 'store';

    //cached self config
    static private $_cacheConfig        = null;

    /**
     * init page cache
     *
     * @param array $frontendOptions
     * @param array $backendOptions
     *
     * @return Potato_FullPageCache_Model_Cache_Page
     */
    static function getPageCache($frontendOptions = array(), $backendOptions = array())
    {
        $pageCache = new Potato_FullPageCache_Model_Cache_Page(
            array(
                'frontend_options' => $frontendOptions,
                'backend_options' => $backendOptions,
            )
        );
        return $pageCache;
    }

    /**
     * init simple cache instance
     *
     * @param       $name
     * @param array $frontendOptions
     * @param array $backendOptions
     *
     * @return Potato_FullPageCache_Model_Cache_Default
     */
    static function getOutputCache($name, $frontendOptions = array(), $backendOptions = array())
    {
        $outputCache = new Potato_FullPageCache_Model_Cache_Default(
            array(
                'frontend_options' => $frontendOptions,
                'backend_options' => $backendOptions,
            )
        );
        $outputCache->setId($name);
        return $outputCache;
    }

    /**
     * return true if block name not specified in po_fpc.xml
     *
     * @param string $blockName
     *
     * @return bool
     */
    static function getIsCanCache($blockName)
    {
        if (!self::getCacheConfig()) {
            return true;
        }
        return !array_key_exists($blockName, self::getSkippedBlocks());
    }

    /**
     * init and return self config
     *
     * @return Mage_Core_Model_Config
     */
    static function getCacheConfig()
    {
        if (null === self::$_cacheConfig) {
            $cache = self::getOutputCache(self::PO_FPC_CONFIG_CACHE_ID);
            if ($cache->test()) {
                //load from cache
                $config = $cache->load();
                $xml = @simplexml_load_string($config, 'Mage_Core_Model_Config_Element');
                self::$_cacheConfig = new Mage_Core_Model_Config();
                self::$_cacheConfig->setXml($xml);
                return self::$_cacheConfig;
            }
            //collect and save config files po_fpc.xml
            self::$_cacheConfig = Mage::getConfig()->loadModulesConfiguration('po_fpc.xml');

            //save po_fpc global settings for quick access without mage config load
            self::$_cacheConfig->setNode(Potato_FullPageCache_Helper_Config::GENERAL_USE_USER_AGENT,
                (int)Mage::getConfig()->getNode(Potato_FullPageCache_Helper_Config::GENERAL_USE_USER_AGENT)
            );
            self::$_cacheConfig->setNode(Potato_FullPageCache_Helper_Config::GENERAL_MAX_ALLOWED_SIZE,
                (int)Mage::getConfig()->getNode(Potato_FullPageCache_Helper_Config::GENERAL_MAX_ALLOWED_SIZE)
            );
            self::$_cacheConfig->setNode(Potato_FullPageCache_Helper_Config::DEBUG_ENABLED,
                (int)Mage::getConfig()->getNode(Potato_FullPageCache_Helper_Config::DEBUG_ENABLED)
            );
            self::$_cacheConfig->setNode(Potato_FullPageCache_Helper_Config::DEBUG_IP_ADDRESSES,
                (string)Mage::getConfig()->getNode(Potato_FullPageCache_Helper_Config::DEBUG_IP_ADDRESSES)
            );
            $cache->save(self::$_cacheConfig->getXmlString(), null, array(self::SYSTEM_TAG));
        }
        return self::$_cacheConfig;
    }


    /**
     * get user params for cache id
     *
     * @return array
     */
    static function getIncludeToPageCacheId()
    {
        return (array) self::getCacheConfig()->getNode('include_to_page_cache_id');
    }

    /**
     * init Mage config from cache
     *
     * @return bool
     */
    static function loadMageConfig()
    {
        $xml = self::getSavedMageConfigXml();
        if (!$xml || !$xml instanceof Mage_Core_Model_Config_Element) {
            return false;
        }
        //init Mage config from cache
        $config = Mage::getConfig();
        $config->setXml($xml);
        return true;
    }

    /**
     * return true if action specified in po_fpc.xml
     *
     * @return bool
     */
    static function getIsAllowedAction()
    {
        $actionConfig = self::getActionConfig();
        return empty($actionConfig) ? false : true;
    }

    /**
     * return action config
     *
     * @return array
     */
    static function getActionConfig()
    {
        if (!self::getCacheConfig() || !self::getCacheConfig()->getNode('allowed_routers')) {
            return array();
        }
        $request = Mage::app()->getRequest();
        foreach(self::getCacheConfig()->getNode('allowed_routers') as $action) {
            $actionData = $action->asArray();
            $moduleName = $request->getModuleName();
            if (!array_key_exists($moduleName, $actionData)) {
                continue;
            }
            if (!array_key_exists('controllers', $actionData[$moduleName])) {
                continue;
            }
            $controllerName = $request->getControllerName();
            $actionName = $request->getActionName();

            if (!array_key_exists($controllerName, $actionData[$moduleName]['controllers'])) {
                continue;
            }
            $actions = explode(',', $actionData[$moduleName]['controllers'][$controllerName]);
            if (in_array($actionName, $actions) || in_array('*', $actions)) {
                if (array_key_exists('parameters', $actionData[$moduleName]) &&
                    !empty($actionData[$moduleName]['parameters']))
                {
                    return $actionData[$moduleName]['parameters'];
                }
                return $actionData[$moduleName];
            }
        }
        return array();
    }

    /**
     * return skipped block processor instance or false
     *
     * @param $blockName
     *
     * @return bool | instance Potato_FullPageCache_Model_Processor_Block_Default
     */
    static function getBlockCacheProcessor($blockName)
    {
        if (!self::getCacheConfig()) {
            return false;
        }
        $skippedBlocks = self::getSkippedBlocks();
        if (!array_key_exists($blockName, $skippedBlocks)) {
            return false;
        }
        $processor = self::DEFAULT_BLOCK_PROCESSOR_CLASS;
        if (array_key_exists($blockName, self::getSessionBlocks())) {
            $processor = self::SESSION_BLOCK_PROCESSOR_CLASS;
        }
        if (is_array($skippedBlocks[$blockName]) &&
            array_key_exists('processor', $skippedBlocks[$blockName])
        ) {
            $processor = $skippedBlocks[$blockName]['processor'];
        }
        return new $processor;
    }

    /**
     * @return array
     */
    static function getSkippedBlocks()
    {
        return array_merge(self::getSessionBlocks(), self::getActionBlocks());
    }

    /**
     * @return array
     */
    static function getSessionBlocks()
    {
        $sessionBlocks = array();
        if (self::getCacheConfig()) {
            $sessionBlocks = self::getCacheConfig()->getNode('session_blocks')->asArray();
        }
        $result = array();
        foreach ($sessionBlocks as $index => $block) {
            if (is_array($block) && array_key_exists('name_in_layout', $block)) {
                $result[$block['name_in_layout']] = $block;
            } else {
                $result[$index] = $block;
            }
        }
        return $result;
    }

    /**
     * @return array
     */
    static function getActionBlocks()
    {
        $actionBlocks = array();
        if (self::getCacheConfig()) {
            $actionConfig = self::getActionConfig();
            if (array_key_exists('skip_blocks', $actionConfig) && is_array($actionConfig['skip_blocks'])) {
                $actionBlocks = $actionConfig['skip_blocks'];
            }
        }
        $result = array();
        foreach ($actionBlocks as $index => $block) {
            if (is_array($block) && array_key_exists('name_in_layout', $block)) {
                $result[$block['name_in_layout']] = $block;
            } else {
                $result[$index] = $block;
            }
        }
        return $result;
    }

    /**
     * set store id cookie
     *
     * @param $storeId
     *
     * @return $this
     */
    static function setStoreCookie($storeId)
    {
        Mage::getSingleton('core/cookie')->set(self::STORE_COOKIE_NAME, $storeId, true, '/');
        return true;
    }

    /**
     * set customer group id cookie
     *
     * @param $groupId
     *
     * @return $this
     */
    static function setCustomerGroupCookie($groupId)
    {
        Mage::getSingleton('core/cookie')->set(self::CUSTOMER_GROUP_ID_COOKIE_NAME, $groupId, true, '/');
        return true;
    }

    /**
     * @return bool|SimpleXMLElement
     */
    static function getSavedMageConfigXml()
    {
        //get mage config from cache
        $cache = self::getOutputCache(self::CONFIG_CACHE_ID, array('lifetime' => self::CONFIG_CACHE_LIFETIME));
        if ($cache->test()) {
            $data = $cache->load();
            return @simplexml_load_string($data, 'Mage_Core_Model_Config_Element');
        }
        return false;
    }

    /**
     * save mage config
     *
     * @return bool
     */
    static function saveMageConfigXml()
    {
        $cache = self::getOutputCache(self::CONFIG_CACHE_ID, array('lifetime' => self::CONFIG_CACHE_LIFETIME));
        if ($cache->test()) {
            //already saved
            return true;
        }
        $config = Mage::getConfig();
        if (Mage::app()->useCache('config')) {
            //if config cache is enabled - need config re-init
            $config->reinit();
        }
        //save mage config
        $cache->save($config->getXmlString(), null, array(self::SYSTEM_TAG));
        return true;
    }

    /**
     * @return bool
     */
    static function removeMageConfigXmlCache()
    {
        $cache = self::getOutputCache(self::CONFIG_CACHE_ID, array('lifetime' => self::CONFIG_CACHE_LIFETIME));
        if ($cache->test()) {
            $cache->delete();
            return true;
        }
        return false;
    }

    /**
     * @return $this
     */
    static function clean()
    {
        $cache = self::getOutputCache(null);
        $cache->flush();
        Potato_FullPageCache_Helper_CacheStorage::cleanStorageData();
        return true;
    }

    /**
     * @param $tags
     *
     * @return $this
     */
    static function cleanByTags($tags)
    {
        $cache = self::getPageCache();
        $ids = Potato_FullPageCache_Helper_CacheStorage::getIdsByTags($tags);
        foreach ($ids as $id) {
            Potato_FullPageCache_Helper_CacheStorage::unregisterCache($id);
            $cache->getFrontend()->remove($id);
        }
        return true;
    }

    /**
     * clean expired cache
     *
     * @return bool
     */
    static function cleanExpire()
    {
        $cache = self::getOutputCache(null);
        foreach($cache->getFrontend()->getIds() as $id) {
            if ($cache->getFrontend()->getMetadatas($id) === false) {
                Potato_FullPageCache_Helper_CacheStorage::unregisterCache($id);
                $cache->getFrontend()->remove($id);
            }
        }
        return true;
    }
}