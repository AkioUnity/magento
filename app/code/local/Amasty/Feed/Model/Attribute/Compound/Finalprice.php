<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

    class Amasty_Feed_Model_Attribute_Compound_Finalprice extends Amasty_Feed_Model_Attribute_Compound_Defaultprice
    {
        protected $_code = 'final_price';

        function hasFilterCondition()
        {
            return true;
        }

        function validateFilterCondition($productData, $operator, $valueCode){
            return Amasty_Feed_Model_Field_Condition::compare($operator, $productData['final_price'], $valueCode);
        }
    }