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
 * Wrapper over the built-in Tax Helper to make Mage_Tax_Helper_Data:getPrice() ignore store view
 * settings and always convert to with or without tax as instructed in the arguments.
 */
class Google_Shoppingconnect_Helper_Tax extends Mage_Tax_Helper_Data
{
    /**
     * @see Mage_Tax_Helper_Data::getPrice()
     */
    public function needPriceConversion($store = null)
    {
        return true;
    }
}
