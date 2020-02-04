<?php
class Biztech_Fedex_Helper_Data extends Mage_Core_Helper_Abstract
{	
	public function getAllStoreDomains() {
        $domains = array();
        foreach (Mage::app()->getWebsites() as $website) {
            $url = $website->getConfig('web/unsecure/base_url');
            if ($domain = trim(preg_replace('/^.*?\/\/(.*)?\//', '$1', $url))) {
                $domains[] = $domain;
            }
            $url = $website->getConfig('web/secure/base_url');
            if ($domain = trim(preg_replace('/^.*?\/\/(.*)?\//', '$1', $url))) {
                $domains[] = $domain;
            }
        }
        return array_unique($domains);
    }
    public function getFormatUrl($url) {
        $input = trim($url, '/');
        if (!preg_match('#^http(s)?://#', $input)) {
            $input = 'http://' . $input;
        }
        $urlParts = parse_url($input);
        $domain = preg_replace('/^www\./', '', $urlParts['host'] . $urlParts['path']);
        return $domain;
    }
	public function checkKey($k, $s = '') {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, sprintf('http://www.appjetty.com/extension/licence.php'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'key=' . urlencode($k) . '&domains=' . urlencode(implode(',', $this->getAllStoreDomains())) . '&sec=magento-fedex-smart-shipping');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $content = curl_exec($ch);
        //$res = Zend_Json::decode($content);
        $res = json_decode($content);
        $modulestatus = new Mage_Core_Model_Config();
        $enc = Mage::helper('core');
        if (empty($res)) {
            $modulestatus->saveConfig('fedex/activation/key', "");
            $modulestatus->saveConfig('fedex/fedex_general/enabled', 0);
            $data = Mage::getStoreConfig('fedex/activation/data');
            $groups = array(
                'activation' => array(
                    'fields' => array(
                        'data' => array(
                            'value' => $data
                        ),
                        'websites' => array(
                            'value' => ''
                        )
                    )
                )
            );
            Mage::getModel('adminhtml/config_data')
                    ->setSection('fedex')
                    ->setGroups($groups)
                    ->save();
            Mage::getConfig()->reinit();
            Mage::app()->reinitStores();
            return;
        }
        $data = '';
        $web = '';
        $en = '';

         if (isset($res->dom) && intval($res->c) > 0 && intval($res->suc) == 1) {
       // if (isset($res['dom']) && intval($res['c']) > 0 && intval($res['suc']) == 1) {
            $data = $enc->encrypt(base64_encode(json_encode($res)));
            if (!$s) {
                $params = Mage::app()->getRequest()->getParam('groups');
                if (isset($params['activation']['fields']['websites']['value'])) {
                    $s = $params['activation']['fields']['websites']['value'];
                }
            }
           // $en = $res['suc'];
            $en = $res->suc;
            if (isset($s) && $s != null) {
                $web = $enc->encrypt($data . implode(',', $s) . $data);
            } else {
                $web = $enc->encrypt($data . $data);
            }
        } else {
            $modulestatus->saveConfig('fedex/activation/key', "");
            $modulestatus->saveConfig('fedex/fedex_general/enabled', 0);
        }
        $groups = array(
            'activation' => array(
                'fields' => array(
                    'data' => array(
                        'value' => $data
                    ),
                    'websites' => array(
                        'value' => (string) $web
                    ),
                    'en' => array(
                        'value' => $en
                    ),
                    'installed' => array(
                        'value' => 1
                    )
                )
            )
        );
        Mage::getModel('adminhtml/config_data')
                ->setSection('fedex')
                ->setGroups($groups)
                ->save();
        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();
    }
	public function getDataInfo() {
        $data = Mage::getStoreConfig('fedex/activation/data');
        return json_decode(base64_decode(Mage::helper('core')->decrypt($data)));
    }

    public function getAllWebsites() {

        if (!Mage::getStoreConfig('fedex/activation/installed')) {
            return array();
        }
        $data = Mage::getStoreConfig('fedex/activation/data');
        $web = Mage::getStoreConfig('fedex/activation/websites');
        $websites = explode(',', str_replace($data, '', Mage::helper('core')->decrypt($web)));
        $websites = array_diff($websites, array(""));
        return $websites;
    }
    public function isEnable() {
        $websiteId = Mage::app()->getWebsite()->getId();

        $isenabled = Mage::getStoreConfig('carriers/fedex/active');
        if ($isenabled) {
            if ($websiteId) {
                $websites = $this->getAllWebsites();
                $key = Mage::getStoreConfig('fedex/activation/key');
                if ($key == null || $key == '') {
                    return false;
                } else {
                    $en = Mage::getStoreConfig('fedex/activation/en');
                    if ($isenabled && $en && in_array($websiteId, $websites)) {
                        return true;
                    } else {
                        return false;
                    }
                }
            } else {
                $en = Mage::getStoreConfig('fedex/activation/en');
                if ($isenabled && $en) {
                    return true;
                }
            }
        }
    }
    public function checkfedexonepage(){
        if (Mage::helper('fedex')->isEnable() && Mage::getStoreConfig('carriers/fedex/enable_addressvalidationfront')){
            return 'fedex/checkout/onepage.phtml';
        } else {
            return 'checkout/onepage.phtml';
        }
    }
    public function checkfedexonepageshipping(){
        if (Mage::helper('fedex')->isEnable() && Mage::getStoreConfig('carriers/fedex/enable_addressvalidationfront')){
            return 'fedex/checkout/onepage/shipping.phtml';
        } else {
            return 'checkout/onepage/shipping.phtml';
        }
    }
    public function checkfedexonepagebilling(){
        if (Mage::helper('fedex')->isEnable() && Mage::getStoreConfig('carriers/fedex/enable_addressvalidationfront')){
            return 'fedex/checkout/onepage/billing.phtml';
        } else {
            return 'checkout/onepage/billing.phtml';
        }
    }
}