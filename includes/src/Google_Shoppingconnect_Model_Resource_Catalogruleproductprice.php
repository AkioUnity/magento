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
 * Extension of Mage_CatalogRule_Model_Resource_Rule_Product_Price to provide querying by product
 * and website id.
 */
class Google_Shoppingconnect_Model_Resource_Catalogruleproductprice
    extends Mage_CatalogRule_Model_Resource_Rule_Product_Price
{
    public function getCatalogRulePrices($productId, $websiteId)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()->from($this->getMainTable(),
                array('earliest_end_date', 'latest_start_date', 'rule_price', 'rule_date'))
            ->where('website_id = ?', $websiteId)
            ->where('customer_group_id = ?', Mage_Customer_Model_Group::NOT_LOGGED_IN_ID)
            ->where('product_id = ?', $productId);
        $result = [];
        $query = $this->_getReadAdapter()->query($select);
        while ($row = $query->fetch()) {
            $result[] = array(
                'rule_price' => $row['rule_price'],
                'rule_date' => $row['rule_date'],
                'latest_start_date' => $row['latest_start_date'],
                'earliest_end_date' => $row['earliest_end_date']
            );
        }
        return $result;
    }
}
