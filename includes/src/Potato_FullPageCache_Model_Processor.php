<?php

class Potato_FullPageCache_Model_Processor
{
    const DEFAULT_HTTP_PORT  = 80;
    const DEFAULT_HTTPS_PORT = 443;

    /**
     * @param $content
     *
     * @return string
     */
    public function extractContent($content)
    {
        $cache = Potato_FullPageCache_Model_Cache::getOutputCache(Potato_FullPageCache_Model_Cache::PO_FPC_CONFIG_CACHE_ID);
        if (!$this->getIsAllowed() || $content || !$cache->test()) {
            return $content;
        }
        if (Potato_FullPageCache_Helper_Data::isUpdater()) {
            $pageId = Mage::app()->getRequest()->getParam('po_fpc_page_id', null);
            unset($_GET['po_fpc_page_id']);
            try {
                $content = $this->_extractBlocks($pageId);
                Mage::app()->getResponse()->setHeader('Content-type', 'application/javascript', true);
                return $content;
            } catch (Exception $e) {
                Mage::log($e->getMessage(), 1, 'po_fpc_except.log');
                Potato_FullPageCache_Model_Cache::removeMageConfigXmlCache();
                return false;
            }
        }
        $pageCache = Potato_FullPageCache_Model_Cache::getPageCache();
        if ($pageCache->test()) {
            try {
                $content = $pageCache->extractContent();
            } catch (Exception $e) {
                Mage::log($e->getMessage(), 1, 'po_fpc_except.log');
                $pageCache->delete();
                return false;
            }
        }
        return $content;
    }

    static function isManaFilter()
    {
        if (Mage::registry('mana_filter') ||  Mage::registry('m_original_request_uri')) {
            return true;
        }
        if (isset($_GET['m-ajax'])) {
            Mage::register('mana_filter', true, true);
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    static function getIsAllowed()
    {
        if (isset($_COOKIE['NO_CACHE']) || isset($_GET['no_cache']) || isset($_GET['___store']) ||
            isset($_GET['___from_store']) || self::isManaFilter() || !Mage::app()->useCache('po_fpc')
        ) {
            return false;
        }
        return true;
    }

    protected function _extractBlocks($pageId)
    {
        $pageCache = Potato_FullPageCache_Model_Cache::getPageCache();
        if (!$pageCache->test($pageId)) {
            return '    ';
        }
        $pageCache->load($pageId);

        //magento v 1.7 for correct work Mage_Core_Helper_Url::getCurrentUrl()
        $this->_setRequestUri();
        if (!$blocks = $pageCache->extractBlocks()) {
            return '    ';
        }
        $response = 'var json = ' . Mage::helper('core')->jsonEncode(array('blocks' => $blocks)) . ';
            if (json.blocks)
            {
                for(key in json.blocks) {
                    if ($("po_fpc_" + key)) {
                        $("po_fpc_" + key).replace(json.blocks[key]);
                        json.blocks[key].evalScripts();
                    } else {
                        console.log("not found block po_fpc_" + key);
                    }
                }
            }
            $$("form").each(function(el){
                el.action = el.action.replace(/PO_FPC_FORM_KEY/g, "' . Mage::getSingleton('core/session')->getFormKey() . '");
            });
            $$("button").each(function(el){
                if (el.getAttribute("onclick")) {
                    el.setAttribute("onclick", el.getAttribute("onclick").replace(/PO_FPC_FORM_KEY/g, "' . Mage::getSingleton('core/session')->getFormKey() . '"));
                }
            });
            $$("a").each(function(el){
                if (el.getAttribute("href")) {
                    el.setAttribute("href", el.getAttribute("href").replace(/PO_FPC_FORM_KEY/g, "' . Mage::getSingleton('core/session')->getFormKey() . '"));
                }
            });
            ';
        return $response;
    }

    /**
     * magento v 1.7 for correct work Mage_Core_Helper_Url::getCurrentUrl()
     * @return $this
     */
    protected function _setRequestUri()
    {
        $request = Mage::app()->getRequest();
        $port = $request->getServer('SERVER_PORT');
        if ($port) {
            $defaultPorts = array(
                self::DEFAULT_HTTP_PORT,
                self::DEFAULT_HTTPS_PORT
            );
            $port = (in_array($port, $defaultPorts)) ? '' : ':' . $port;
        }
        $url = $request->getScheme() . '://' . $request->getHttpHost() . $port;
        $_SERVER['REQUEST_URI'] = str_replace($url, '', @$_SERVER['HTTP_REFERER']);
        return $this;
    }
}