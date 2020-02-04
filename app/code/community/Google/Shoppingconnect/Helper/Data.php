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

set_include_path(get_include_path() . PATH_SEPARATOR . MAGENTO_ROOT.'/lib/phpseclibgoogleshopping');
require_once('phpseclibgoogleshopping/Crypt/RSA.php');
require_once('phpseclibgoogleshopping/Math/BigInteger.php');

/**
 * Helper class for Google Shopping Connect. Contains the main business logic for the admin page
 * and API user setup.
 */
class Google_Shoppingconnect_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_API_ROLE_ID = 'googleshoppingconnect/api_role_id';
    const XML_PATH_VERIFICATION_TAG = 'googleshoppingconnect/verification_tag';

    const API_USER_BASE_USERNAME = 'google_shopping_connect';
    const API_USER_FIRSTNAME = 'Google Shopping Connect';
    const API_ROLE_NAME = 'Google Shopping Connect';

    const API_KEY_CHARACTER_COUNT = 32;

   const PUBLIC_KEY = "-----BEGIN PUBLIC KEY-----
MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEA7eItU6k1qhesPeR3p0++
YtJV+IZVLkOKAJe/GQNNYvf7HLBs54EW12l6qpOuf6NOooChg7a2e5oMyYms4CiK
K0i6YxP8E1lXfU+GDAsXIYQXPf1PNsbrHRIqplD6KtPRm3LRRff0Lo1wc8NC3Mat
ldf8yKUQmw0f/u4vnf6fRB98A/fFSjGLX9+59kxmcJzIw1ta5BOoIZr/F4nXTdXi
s4o0uiusT+MGbYMsp6hqSZU/6P/yHrG8vK4x4TDvFwQ5d3v1Af0SWIq42CvCbozn
yYfOxzXigwCkpynCrclaoHHLXdGNMdfCAC67/ntF3wT5j6EHDgqHWlsWkkOWlJNB
K2UGJs9wLig9SQsaKYuYzT8xgS34YD9paVTlWV9rhd9czqJF66F5uB4/ANvsuFSP
1uC1B3onV2M4hmYvmnWaaPWdFv3LR6rT29ZvD38QWQfVacbw0OlK26zouFJkpLam
yj+r6op7wvnMW8ZsQwOtwaN2UlONdX+qq9d8YDyZYUj+P5VE3gKwcMpRHsWKgUgI
UmiavxNEl2MgSJ4/3mMl4aa2IdNQS1CY0G+hRHYLpjfug5SAy08dN7p4fYizlVSF
KYr60j97fqaz0In6Gskbbe9ll1wqbKc4UxnuVQo5s7W1Hr0ngzJ6+yo8sJdfPjfl
BcLGw8G4R0uzhhUwl2KC7vkCAwEAAQ==
-----END PUBLIC KEY-----";

    /**
     * Creates a new API user and returns a pair of username and key.
     */
    public function createAlwaysNewApiUser()
    {
        $apiUserCollection = Mage::getResourceModel('api/user_collection')
            ->addFieldToFilter('username', array('like' => self::API_USER_BASE_USERNAME.'%'));
        $usernames = array();
        foreach($apiUserCollection as $apiUser) {
            $usernames[] = $apiUser['username'];
        }
        $counter = 1;
        while (in_array(self::API_USER_BASE_USERNAME.$counter, $usernames)) {
            $counter++;
        }
        $username = self::API_USER_BASE_USERNAME.$counter;
        $apiKey = $this->getRandomApiKey();
        $this->createApiUser($apiKey, $username);
        return array($username, $apiKey);
    }

    /**
     * Returns a random API key that is deemed secure enough. Does not actually save the API key.
     */
    public function getRandomApiKey()
    {
        $random = '';
        /* There are no O/0 in the codes in order to avoid confusion */
        $chars = "123456789ABCDEFGHIJKLMNPQRSTUVWXYZ";
        $min = new Math_BigInteger(0);
        $max = new Math_BigInteger(strlen($chars) - 1);
        for ($i = 0; $i < self::API_KEY_CHARACTER_COUNT; $i++) {
            $random .= $chars[$min->random($min, $max)->toString()];
        }
        return $random;
    }

    /**
     * Returns the API base url.
     */
    public function getApiPath()
    {
        return Mage::getUrl(Mage::app()->getFrontController()
            ->getRouter('standard')->getFrontNameByRoute('api'));
    }

    /**
     * Adds a website verification meta tag content for the given URL.
     */
    public function addVerificationTag($tagContent, $url)
    {
        $verificationTags = self::getVerificationTags();
        if ($verificationTags == null) {
            $verificationTags = array();
        }
        $verificationTags[$tagContent] = $url;
        Mage::getModel('core/config')->saveConfig(
            self::XML_PATH_VERIFICATION_TAG, json_encode($verificationTags));
    }

    /**
     * Returns the website verification meta tag contents as a map from tag to URL.
     */
    public function getVerificationTags()
    {
        $result = json_decode(Mage::getStoreConfig(self::XML_PATH_VERIFICATION_TAG), true);
        if (is_null($result)) {
            return array();
        }
        return $result;
    }

    public function getMerchantCenterRedirectUrl($apiUsername, $apiKey)
    {
        $rsa = new Crypt_RSA();
        $rsa->loadKey(self::PUBLIC_KEY);

        $rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_OAEP);
        $rsa->setHash('sha256');
        $rsa->setMGFHash('sha256');

        $encryptedKey = $rsa->encrypt($apiKey);
        return "https://merchants.google.com/PlatformsSignup?platform=magento&path=".
            urlencode($this->getApiPath()).
            "&username=".urlencode($apiUsername).
            "&encrypted_key=".urlencode(base64_encode($encryptedKey));
    }

    protected function createApiUser($apiKey, $username)
    {
        $apiRole = $this->loadApiRole();
        if (!$apiRole->getId()) {
            $apiRole = $this->createApiRole();
        }

        $apiUser = Mage::getModel('api/user');
        $apiUser->setData(array(
            'username' => $username,
            'firstname' => self::API_USER_FIRSTNAME,
            'api_key' => $apiKey,
            'api_key_confirmation' => $apiKey,
            'is_active' => 1,
        ));
        $apiUser->save()->load($apiUser->getId());

        $this->setApiUserRoleRelations($apiUser, $apiRole->getId());
    }

    protected function loadApiRole()
    {
        $apiRoleId = Mage::getStoreConfig(self::XML_PATH_API_ROLE_ID);
        $apiRole = Mage::getModel('api/role');
        return $apiRole->load($apiRoleId);
    }

    protected function createApiRole()
    {
        $apiRole = Mage::getModel('api/roles')
            ->setName(self::API_ROLE_NAME)
            ->setPid(false)
            ->setRoleType('G')
            ->save();
        $this->setRoleResources($apiRole->getId());
        Mage::getModel('core/config')->saveConfig(self::XML_PATH_API_ROLE_ID, $apiRole->getId());
        return $apiRole;
    }

    protected function setRoleResources($apiRoleId)
    {
        Mage::getModel('api/rules')
            ->setRoleId($apiRoleId)
            ->setResources(array(
                'googleshoppingconnect/add_verification_tag',
                'googleshoppingconnect/shop_info',
                'googleshoppingconnect/products',
                'googleshoppingconnect',
                'catalog/category',
                'catalog/category/tree'
            ))
            ->saveRel();
    }

    protected function setApiUserRoleRelations($apiUser, $apiRoleId)
    {
        $apiUser->setRoleIds(array($apiRoleId))
            ->setRoleUserId($apiUser->getUserId())
            ->saveRelations();
    }
}
