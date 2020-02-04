<?php

class Potato_FullPageCache_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * required for correct layout generate blocks
     *
     * @return bool
     */
    static function initRequest()
    {
        $request = Mage::app()->getRequest();
        $request->setPathInfo();
        $request->rewritePathInfo(null);
        $action = new Mage_Core_Controller_Front_Action($request, Mage::app()->getResponse());
        Mage::app()->getFrontController()->setAction($action);
        return true;
    }

    /**
     * Check is debug mode enabled
     *
     * @return bool
     */
    static function isDebugModeEnabled()
    {
        $debugIpAddresses = Potato_FullPageCache_Helper_Config::getDebugIpAddresses();
        $clientIp = $_SERVER['REMOTE_ADDR'];
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $clientIp = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $clientIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        if (Potato_FullPageCache_Helper_Config::getIsDebugEnabled() &&
            (empty($debugIpAddresses) || in_array($clientIp, $debugIpAddresses))
        ) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    static function getRequestHash()
    {
        $uri = '';
        if (isset($_SERVER['HTTP_HOST'])) {
            $uri = $_SERVER['HTTP_HOST'];
        } else if (isset($_SERVER['SERVER_NAME'])) {
            $uri = $_SERVER['SERVER_NAME'];
        }
        if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
            $uri .= $_SERVER['HTTP_X_REWRITE_URL'];
        } else if (isset($_SERVER['REQUEST_URI'])) {
            $uri .= $_SERVER['REQUEST_URI'];
        } else if (!empty($_SERVER['IIS_WasUrlRewritten']) && !empty($_SERVER['UNENCODED_URL'])) {
            $uri .= $_SERVER['UNENCODED_URL'];
        } else if (isset($_SERVER['ORIG_PATH_INFO'])) {
            $uri .= $_SERVER['ORIG_PATH_INFO'];
            if (!empty($_SERVER['QUERY_STRING'])) {
                $uri .= $_SERVER['QUERY_STRING'];
            }
        }
        return md5($uri);
    }

    /**
     * @return array
     */
    static function getCacheTags()
    {
        if (Mage::registry('current_product')) {
            return array(Potato_FullPageCache_Model_Cache::PRODUCT_TAG, Mage::registry('current_product')->getId());
        }
        if (Mage::registry('current_category')) {
            return array(Potato_FullPageCache_Model_Cache::CATEGORY_TAG, Mage::registry('current_category')->getId());
        }
        if (Mage::registry('current_cms')) {
            return array(Potato_FullPageCache_Model_Cache::CMS_TAG, Mage::registry('current_cms')->getId());
        }
        return array();
    }

    /**
     * @return bool
     */
    static function canCache()
    {
        return Potato_FullPageCache_Model_Processor::getIsAllowed() && !Mage::app()->getStore()->isAdmin() &&
            Potato_FullPageCache_Model_Cache::getIsAllowedAction()
        ;
    }

    static function isUpdater()
    {
        return Mage::app()->getRequest()->getParam('po_fpc_page_id', null) ? true : false;
    }

    /**
     * @param array $requestData
     *
     * @return array
     */
    static function emulateRequest($requestData)
    {
        $request = Mage::app()->getRequest();
        $oldRequest = array(
            'module_name'     => $request->getModuleName(),
            'controller_name' => $request->getControllerName(),
            'action_name'     => $request->getActionName(),
            'params'          => $request->getParams(),
            'request_uri'     => $request->getRequestUri(),
            'path_info'       => $request->getPathInfo(),
            'alias'           => $request->getAlias(Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS)
        );
        if (array_key_exists('module_name', $requestData)) {
            $request->setModuleName($requestData['module_name']);
        }
        if (array_key_exists('controller_name', $requestData)) {
            $request->setControllerName($requestData['controller_name']);
        }
        if (array_key_exists('action_name', $requestData)) {
            $request->setActionName($requestData['action_name']);
        }
        if (array_key_exists('params', $requestData)) {
            $request->setParams($requestData['params']);
        }
        if (array_key_exists('request_uri', $requestData)) {
            $request->setRequestUri($request->getBaseUrl() . $requestData['request_uri']);
        }
        if (array_key_exists('path_info', $requestData)) {
            $request->setPathInfo($requestData['path_info']);
        }
        if (array_key_exists('alias', $requestData)) {
            $request->setAlias(Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS, $requestData['alias']);
        }
        return $oldRequest;
    }
}