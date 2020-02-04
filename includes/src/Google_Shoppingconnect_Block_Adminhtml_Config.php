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
 * Top class for the Google Shopping Connect admin page.
 */
class Google_Shoppingconnect_Block_Adminhtml_Config extends Mage_Adminhtml_Block_Widget_Container
{
    // The following points to linkAction in GoogleshoppingconnectController.php.
    const LINK_URL = '*/googleshoppingconnect/link';
    // The following helper exists at app/code/community/Google/Shoppingconnect/Helper/Data.php
    const GOOGLE_SHOPPING_CONNECT_HELPER = 'googleshoppingconnect';

    /**
     * Returns the Google website verification URLs.
     *
     * @return array
     */
    protected function getVerificationUrls()
    {
        return array_unique(array_values(
            Mage::helper(self::GOOGLE_SHOPPING_CONNECT_HELPER)->getVerificationTags()));
    }

    protected function getVerifiedWebsitesText()
    {
        return $this->__('A meta tag has been added to the HTML on the sites listed below. This step lets Google verify domain ownership on your sites.');
    }

    protected function getIntroText()
    {
        return $this->__('%sGoogle Shopping%s helps businesses tap into the power of customer intent to reach the right people with relevant products ads when it matters the most. Use this extension to upload your store and product data to Google Merchant Center and make it available to Google Shopping and other Google services, allowing you to reach millions of new customers searching for what you sell.', '<a href="https://www.youtube.com/watch?v=xIil1YlBMOw">', '</a>');
    }

    protected function getConnectText()
    {
        return $this->__('Connect your Magento account to Merchant Center to begin importing your product data through the store API. After you’ve linked your Magento and Merchant Center accounts, any store information that’s updated in your Magento store will also be updated in Merchant Center.');
    }

    protected function getCallToActionText() {
        return $this->__('Get started by clicking %s below. A new Magento API account will be automatically generated and your store information and credentials will be submitted to Merchant Center through a secure connection. If you don\'t already have a Merchant Center account, follow the sign up prompts to create a new account. Note: If a link is not created, your API information will be deleted after 60 days.', $this->__('Launch'));
    }

    protected function getLearnMoreText()
    {
        return $this->__('Learn More');
    }

    /**
     * Returns the "Launch" button HTML.
     *
     * @return string
     */
    protected function getLaunchButtonHtml()
    {
        $linkUrl = Mage::getSingleton('adminhtml/url')->getUrl(self::LINK_URL);
        return self::getButton(
            array(
                'label'     => $this->__('Launch'),
                'onclick'   => 'window.open(\'' . $linkUrl . '\', \'_blank\')',
            ));
    }

    protected function getButton($data)
    {
        return $this->getLayout()->createBlock('adminhtml/widget_button', '', $data)->toHtml();
    }
}
