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
 * Renders the Google website verification meta tags.
 */
class Google_Shoppingconnect_Block_Verification extends Mage_Core_Block_Template
{
    //app/code/community/Google/Shoppingconnect/Helper/Data.php
    const GOOGLE_SHOPPING_CONNECT_HELPER = 'googleshoppingconnect';

    /**
     * Returns a list of Google website verification meta tags content.
     *
     * @return array
     */
    protected function getVerificationMetaTagsContent()
    {
        return array_keys(Mage::helper(self::GOOGLE_SHOPPING_CONNECT_HELPER)->getVerificationTags());
    }

    /**
     * Renders the Google website verification html snippets.
     *
     * @return string
     */
    protected function _toHtml()
    {
        $verificationTag = $this->getVerificationMetaTagsContent();
        if (empty($verificationTag)) {
            return '';
        }
        return parent::_toHtml();
    }
}
