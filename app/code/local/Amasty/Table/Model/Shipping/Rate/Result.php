<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Table
 */


class Amasty_Table_Model_Shipping_Rate_Result extends Mage_Shipping_Model_Rate_Result
{
    public function sortRatesByPrice()
    {
        if (!is_array($this->_rates) || !count($this->_rates)) {
            return $this;
        }

        /* @var $rate Mage_Shipping_Model_Rate_Result_Method */
        foreach ($this->_rates as $i => $rate) {
            if (is_numeric($rate->getPos())) {
                $tmp[$i] = $rate->getPos();
            } else {
                $tmp[$i] = $rate->getPrice();
            }
        }

        natsort($tmp);

        foreach ($tmp as $i => $price) {
            $result[] = $this->_rates[$i];
        }

        $this->reset();
        $this->_rates = $result;
        return $this;
    }
}