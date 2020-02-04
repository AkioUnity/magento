<?php

class Potato_FullPageCache_Model_Cache_Page extends Potato_FullPageCache_Model_Cache_Default
{
    //new block content for replace
    protected $_contentForReplace = null;

    protected $_content = '';

    //page layout xml
    protected $_layout  = false;

    //page request string
    protected $_request = array();

    //init layout flag
    protected $_isLayoutInitCompleteFlag = false;

    //page headers array
    protected $_headers = false;

    const SESSION_NAMESPACE = 'frontend';

    /**
     * generate cache id
     *
     * @return null|string md5(Potato_FullPageCache_Helper_Data::getRequestHash()
     * + store id + currency + customer group + optional(HTTP_USER_AGENT + user params) )
     */
    public function getId()
    {
        if (null === $this->_id) {
            $uri = Potato_FullPageCache_Helper_Data::getRequestHash();
            if (isset($_COOKIE[Potato_FullPageCache_Model_Cache::STORE_COOKIE_NAME])) {
                $uri .= $_COOKIE[Potato_FullPageCache_Model_Cache::STORE_COOKIE_NAME];
            } else {
                $storeId = Potato_FullPageCache_Helper_CacheStore::loadStoreByRequest();
                if ($storeId) {
                    $uri .= $storeId;
                }
            }
            if (isset($_COOKIE[Potato_FullPageCache_Model_Cache::CURRENCY_COOKIE_NAME])) {
                $uri .= $_COOKIE[Potato_FullPageCache_Model_Cache::CURRENCY_COOKIE_NAME];
            }
            if (isset($_COOKIE[Potato_FullPageCache_Model_Cache::CUSTOMER_GROUP_ID_COOKIE_NAME])) {
                $uri .= $_COOKIE[Potato_FullPageCache_Model_Cache::CUSTOMER_GROUP_ID_COOKIE_NAME];
            } else {
                $uri .= Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
            }
            if (Potato_FullPageCache_Helper_Config::getIsCanUseUserAgent()) {
                $uri .= $_SERVER['HTTP_USER_AGENT'];
            }
            $userParams = Potato_FullPageCache_Model_Cache::getIncludeToPageCacheId();
            if (!empty($userParams)) {
                foreach ($userParams as $param) {
                    try {
                        if (trim($param)) {
                            $uri .= @call_user_func(trim($param));
                        }
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                }
            }
            $this->_id = md5($uri);
        }
        return parent::getId();
    }

    /**
     * save cache
     *
     * @param       $content
     * @param null  $id
     * @param array $tags
     * @param bool  $lifetime
     *
     * @return $this
     */
    public function save($content, $id = null, $tags = array(), $lifetime = false)
    {
        //get request
        $request = Mage::app()->getRequest();

        //remove block contents
        $content = $this->_removeBlocksContent($content);

        //gZip content
        $content = $this->_gzcompress($content);

        //prepare data
        $data = array(
            'content' => $content,
            'headers' => Mage::app()->getResponse()->getHeaders(),
            'layout'  => $this->_gzcompress(Mage::app()->getLayout()->getXmlString()),
            'request' => array(
                'module_name'     => $request->getModuleName(),
                'controller_name' => $request->getControllerName(),
                'action_name'     => $request->getActionName(),
                'params'          => $request->getParams(),
                'request_uri'     => $request->getRequestUri(),
                'path_info'       => $request->getPathInfo(),
                'alias'           => $request->getAlias(Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS)
            )
        );

        //get action cache lifetime
        $actionConfig = Potato_FullPageCache_Model_Cache::getActionConfig();
        if (array_key_exists('lifetime', $actionConfig)) {
            $lifetime = (int)$actionConfig['lifetime'];
        }

        //prepare cache tags
        if (isset($actionConfig['tags'])) {
            $tags = array_merge($tags, @explode(',', $actionConfig['tags']));
        }
        //return save result
        return parent::save($data, $this->getId(), $tags, $lifetime);
    }

    /**
     * init magento layout
     *
     * @return $this
     */
    protected function _loadLayout()
    {
        if ($this->_layout) {
            Mage::getSingleton('core/layout')->setXml(@simplexml_load_string($this->_layout, 'Mage_Core_Model_Layout_Element'));
        }
        return $this;
    }

    /**
     * update block html from cache
     *
     * @param $cachedBlock
     *
     * @return $this
     */
    protected function _updateBlockFromCache($cachedBlock)
    {
        //get block processor
        $blockProcessor = Potato_FullPageCache_Model_Cache::getBlockCacheProcessor($cachedBlock['name_in_layout']);
        $cachedHtml = $cachedBlock['html'];
        if ($blockProcessor) {
            //get caches html from processor
            $cachedHtml = $blockProcessor->getPreparedHtmlFromCache($cachedHtml);
        }
        return $cachedHtml;
    }

    /**
     * load and render cached page
     *
     * @return string
     */
    public function extractContent()
    {
        $this
            ->load()
        ;

        //init response headers
        if ($this->_headers) {
            foreach ($this->_headers as $header) {
                Mage::app()->getResponse()->setHeader($header['name'], $header['value'], true);
            }
        }

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '/'
            . 'po_fpc/updater/blocks?po_fpc_page_id=' . $this->getId()
        ;
        $this->_content = str_replace('</body>', '<script type="text/javascript">'
            . 'document.write(\'\x3Cscript defer type="text/javascript" src="' . $url . '">\x3C/script>\');</script></body>',
            $this->_content
        );
        return $this->_content;
    }

    protected function _removeBlocksContent($content)
    {
        $actionBlocks = Potato_FullPageCache_Model_Cache::getActionBlocks();
        foreach ($actionBlocks as $index => $blockData) {
            $content = $this->_removeBlockContent($index, $content);
        }
        $sessionBlocks = Potato_FullPageCache_Model_Cache::getSessionBlocks();
        foreach ($sessionBlocks as $index => $blockData) {
            $content = $this->_removeBlockContent($index, $content);
        }
        return $content;
    }

    protected function _removeBlockContent($index, $content)
    {
        $htmlForReplace = '<div class="po-fpc-updater" id="po_fpc_' . $index . '"></div>';
        $this->_contentForReplace = $htmlForReplace;
        $replaceResult = @preg_replace_callback("/<!--\[{$index}\]-->(.*?)<!--\[{$index}\]-->/ims",
            array($this, '_replaceContent'), $content
        );
        if ($replaceResult) {
            $content = $replaceResult;
        }
        return $content;
    }

    public function extractBlocks()
    {
        if (!$this->initCurrentStore() || !$this->_initMageConfig()) {
            return false;
        }
        //emulate request
        $oldRequest = Potato_FullPageCache_Helper_Data::emulateRequest($this->_request);
        Mage::getSingleton('core/session', array('name' => self::SESSION_NAMESPACE))->start();

        //init design
        Mage::app()->loadArea(Mage_Core_Model_App_Area::AREA_FRONTEND);

        //init request (for correct redirects from controller)
        Potato_FullPageCache_Helper_Data::initRequest();

        //dispatch event for collect visitor data
        Mage::dispatchEvent('controller_action_predispatch', array('controller_action' =>  Mage::app()->getFrontController()->getAction()));

        $blocks = $this->_getUpdatedRouterBlocks() + $this->_getUpdatedSessionBlocks();

        //update customer group cookie
        Mage::dispatchEvent('po_fpc_blocks_update_after', array('blocks' => &$blocks));

        $this->_dispatchRouterEvents();

        //dispatch event for save visitor data
        Mage::dispatchEvent('controller_action_postdispatch', array('controller_action'=> Mage::app()->getFrontController()->getAction()));
        //restore request
        Potato_FullPageCache_Helper_Data::emulateRequest($oldRequest);
        return $blocks;
    }

    /**
     * update routers blocks
     *
     * @return bool
     */
    protected function _getUpdatedRouterBlocks()
    {
        $blocksData = array();
        //get cached blocks
        $actionCachedBlocks = Potato_FullPageCache_Helper_CacheBlock::getActionBlockCache();

        //get blocks from config
        $actionBlocks = Potato_FullPageCache_Model_Cache::getActionBlocks();
        foreach ($actionBlocks as $index => $blockData) {
            if (!$this->_getIsBlockOnPage($index)) {
                continue;
            }
            //get block processor
            $blockProcessor = Potato_FullPageCache_Model_Cache::getBlockCacheProcessor($index);
            if ($blockProcessor->getIsIgnoreCache() || !array_key_exists($index, $actionCachedBlocks)) {
                //update block cache if cache not exists or block processor ignore it
                if ($content = $this->_updateBlock($index)) {
                    $blocksData[$index] = $content;
                }
                continue;
            }
            //update block from cache
            $cachedBlock = $actionCachedBlocks[$index];
            $blocksData[$index] = $this->_updateBlockFromCache($cachedBlock);
        }
        return $blocksData;
    }

    /**
     * check block on current page
     *
     * @param $blockIndex
     *
     * @return bool
     */
    protected function _getIsBlockOnPage($blockIndex)
    {
        return strpos($this->_content, 'id="po_fpc_' . $blockIndex.'"') !== false;
    }

    /**
     * update session blocks
     *
     * @return bool
     */
    protected function _getUpdatedSessionBlocks()
    {
        $blocksData = array();
        //get blocks from config
        $sessionBlocks = Potato_FullPageCache_Model_Cache::getSessionBlocks();

        foreach ($sessionBlocks as $index => $blockData) {
            if (!$this->_getIsBlockOnPage($index)) {
                continue;
            }
            $blockProcessor = Potato_FullPageCache_Model_Cache::getBlockCacheProcessor($index);
            $sessionBlockCache = $blockProcessor->load($index);
            if ($sessionBlockCache && !$blockProcessor->getIsIgnoreCache()) {
                $blocksData[$index] = $this->_updateBlockFromCache($sessionBlockCache);
                continue;
            }
            if ($content = $this->_updateBlock($index)) {
                $blocksData[$index] = $content;
            }
        }
        return $blocksData;
    }

    /**
     * update block html from saved layout
     *
     * @param $index
     *
     * @return bool
     */
    protected function _updateBlock($index)
    {
        if (!$this->_initLayout()) {
            //return false if mage config not init
            return false;
        }
        //get block processor
        $blockProcessor = Potato_FullPageCache_Model_Cache::getBlockCacheProcessor($index);

        //get block from layout
        $block = Mage::app()->getLayout()->getBlock($index);
        if ($blockProcessor && $block) {
            //get updated content from block processor
            $blockHtml = $blockProcessor->getBlockHtml($block);

            //save block cache
            Potato_FullPageCache_Helper_CacheBlock::saveSkippedBlockCache(
                array(
                    'html'           => $blockHtml,
                    'name_in_layout' => $block->getNameInLayout()
                ),
                $index
            );
        }
        if (Potato_FullPageCache_Helper_Data::isDebugModeEnabled()) {
            $blockHtml = '<div style="border:1px solid green;width:auto;height:auto;"><div style="color:green;">'
                . $block->getNameInLayout() . '</div>' . $blockHtml . '</div>'
            ;
        }
        Mage::dispatchEvent('po_fpc_block_updated', array('html' => &$blockHtml));
        return $blockHtml;
    }

    /**
     * init mage page
     *
     * @return bool
     */
    protected function _initLayout()
    {
        //check flag
        if ($this->_isLayoutInitCompleteFlag) {
            return true;
        }
        if (@class_exists('Olegnax_Ajaxcart_Model_Observer', false)) {
            //compatibility with Olegnax_Ajaxcart
            $_currentUrl = Mage::helper('core/url')->getCurrentUrl();
            if (strpos($_currentUrl, Olegnax_Ajaxcart_Model_Observer::AJAXCART_ROUTE ) === false) {
                Mage::getSingleton('core/session', array('name' => 'frontend'))->setData('oxajax_referrer', $_currentUrl);
            }
        }
        //init Mage_Core_Model_Layout
        $this->_loadLayout();

        $layout = Mage::app()->getLayout();

        //init router data before generate blocks
        $this->_beforeLayoutGenerateBlocks();

        //generate layout blocks
        $layout->generateBlocks();

        //init router data after generate blocks
        $this->_afterLayoutGenerateBlocks();

        //set flag
        $this->_isLayoutInitCompleteFlag = true;
        return true;
    }

    /**
     * some skipped block can be required some specific actions before generate layout
     *
     * @return $this
     */
    protected function _beforeLayoutGenerateBlocks()
    {
        $actionConfig = Potato_FullPageCache_Model_Cache::getActionConfig();
        if (array_key_exists('processor', $actionConfig)) {
            Mage::getModel($actionConfig['processor'])->beforeLayoutGenerateBlocks();
        }
        return $this;
    }

    protected function _dispatchRouterEvents()
    {
        $actionConfig = Potato_FullPageCache_Model_Cache::getActionConfig();
        if (array_key_exists('processor', $actionConfig)) {
            Mage::getModel($actionConfig['processor'])->dispatchEvents();
        }
        return $this;
    }

    /**
     * some skipped block can be required some specific actions after generate layout
     *
     * @return $this
     */
    protected function _afterLayoutGenerateBlocks()
    {
        $actionConfig = Potato_FullPageCache_Model_Cache::getActionConfig();
        if (array_key_exists('processor', $actionConfig)) {
            Mage::getModel($actionConfig['processor'])->afterLayoutGenerateBlocks();
        }
        return $this;
    }

    /**
     * @param null | string $id
     *
     * @return mixed |string
     */
    public function load($id = null)
    {
        $_result = parent::load($id);
        $this->_content = $this->_gzuncompress($_result['content']);
        $this->_layout = $this->_gzuncompress($_result['layout']);
        $this->_request = $_result['request'];
        $this->_headers = $_result['headers'];
        return $this;
    }

    /**
     * @param Mage_Core_Block_Abstract $block
     *
     * @return $this
     */
    public function setFrameTags($block)
    {
        $block->setFrameTags('!--[' . $block->getNameInLayout() . ']--', '!--[' . $block->getNameInLayout() . ']--');
        return $this;
    }

    /**
     * @param array $matches
     *
     * @return mixed
     */
    protected function _replaceContent($matches)
    {
        if (null !== $this->_contentForReplace) {
            $matches[0] = $this->_contentForReplace;
            $this->_contentForReplace = null;
        }
        return $matches[0];
    }

    /**
     * @return bool
     */
    protected function _initMageConfig()
    {
        //init session
        Mage::getSingleton('core/translate')->init('frontend');
        //load events
        Mage::app()->loadAreaPart(Mage_Core_Model_App_Area::AREA_GLOBAL, Mage_Core_Model_App_Area::PART_EVENTS);
        Mage::app()->loadAreaPart(Mage_Core_Model_App_Area::AREA_FRONTEND, Mage_Core_Model_App_Area::PART_EVENTS);
        //not required for crawler
        if ($_SERVER['HTTP_USER_AGENT'] != Potato_FullPageCache_Model_Crawler::USER_AGENT) {
            //need for load magento product/catalog rewrites and correct controller redirect
            Mage::getModel('core/url_rewrite')->rewrite();
        }
        return true;
    }

    public function initCurrentStore()
    {
        if (!Potato_FullPageCache_Model_Cache::loadMageConfig()) {
            return false;
        }

        $storeId = Potato_FullPageCache_Helper_CacheStore::loadStoreByRequest();
        if ($_COOKIE && isset($_COOKIE[Potato_FullPageCache_Model_Cache::STORE_COOKIE_NAME])) {
            $storeId = $_COOKIE[Potato_FullPageCache_Model_Cache::STORE_COOKIE_NAME];
        }
        if (!$storeId) {
            return false;
        }
        //init current store
        Mage::app()->setCurrentStore($storeId);
        if (Mage::app()->getStore()->isAdmin()) {
            //if is admin - no caching
            return false;
        }
        return true;
    }
}