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
 * Action Controller for Google Shopping Connect.
 */
class Google_Shoppingconnect_Adminhtml_GoogleshoppingconnectController
    extends Mage_Adminhtml_Controller_Action
{
    //app/code/community/Google/Shoppingconnect/Helper/Data.php
    const GOOGLE_SHOPPING_CONNECT_HELPER = 'googleshoppingconnect';
    //app/code/community/Google/Shoppingconnect/etc/adminhtml.xml
    const GOOGLE_SHOPPING_CONNECT_ADMIN_ACL = 'system/googleshoppingconnect';

    /**
     * Serves the /admin/googleshoppingconnect/index page, the admin configuration page for the
     * Google Shopping Connect extension.
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Creates API credentials and redirects the user to a Google Merchant Center signup flow,
     * safely encoding the credentials in the URL.
     */
    public function linkAction()
    {
        $helper = Mage::helper(self::GOOGLE_SHOPPING_CONNECT_HELPER);
        list($apiUsername, $apiKey) = $helper->createAlwaysNewApiUser();
        $redirectUrl = $helper->getMerchantCenterRedirectUrl($apiUsername, $apiKey);
        $this->getResponse()->setRedirect($redirectUrl);
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            self::GOOGLE_SHOPPING_CONNECT_ADMIN_ACL);
    }
}
