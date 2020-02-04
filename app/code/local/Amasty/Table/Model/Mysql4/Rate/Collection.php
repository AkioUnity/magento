<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Table
 */
class Amasty_Table_Model_Mysql4_Rate_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('amtable/rate');
    }

    public function addAddressFilters($request)
    {
        $this->addFieldToFilter('country', array(
            array(
                'like' => $request->getDestCountryId(),
            ),
            array(
                'eq' => '0',
            ),
            array(
                'eq' => '',
            ),
        ));

        $this->addFieldToFilter('state', array(
            array(
                'like' => $request->getDestRegionId(),
            ),
            array(
                'eq' => '0',
            ),
            array(
                'eq' => '',
            ),
        ));

        $this->addFieldToFilter('city', array(
            array(
                'like' => $request->getDestCity(),
            ),
            array(
                'eq' => '',
            ),
        ));

        $inputZip = $request->getDestPostcode();
        if (Mage::getStoreConfig('carriers/amtable/numeric_zip')) {
            $zipData = Mage::helper('amtable')->getDataFromZip($inputZip);
            $district = $zipData['district'];
            $area = $zipData['area'];
            $this->addFieldToFilter('num_zip_from', array(
                array(
                    'lteq' => $district,
                ),
                array(
                    'eq' => '0',
                ),
            ));
            $this->addFieldToFilter('num_zip_to', array(
                array(
                    'gteq' => $district,
                ),
                array(
                    'eq' => '0',
                ),
            ));

            if ((!empty($area) && $area != '*')
                || $request->getCountryId() == 'GB'
            ) {
                $districtArea = $area . $district;

                $this->getSelect()->where(
                    '(((((((
                    `zip_from` = \'' . $districtArea . '\') OR (`zip_from` = \'' . $inputZip . '\') OR (`zip_from` = \'\'))) 
                     OR ( zip_from <> zip_to AND(((`zip_from` REGEXP \'^' . $area . '[0-9]+\') OR (`zip_from` = \'\'))))))))');

                $this->getSelect()->order(new Zend_Db_Expr('FIELD(zip_from, \'' . $districtArea.'\', \'' . $inputZip .'\') DESC'));
            }

            //to prefer rate with zip
            $this->setOrder('num_zip_from', 'DESC');
            $this->addOrder('num_zip_to', 'DESC');
        } else {
            $this->getSelect()->where("? LIKE zip_from OR zip_from = ''", $inputZip);
        }

        return $this;
    }

    public function addMethodFilters($methodIds)
    {
        $this->addFieldToFilter('method_id', array('in' => $methodIds));

        return $this;
    }

    public function addTotalsFilters($totals, $shippingType)
    {
        $this->addFieldToFilter('price_from', array('lteq' => $totals['not_free_price']));
        $this->addFieldToFilter('price_to', array('gteq' => $totals['not_free_price']));
        $this->addFieldToFilter('weight_from', array('lteq' => $totals['not_free_weight']));
        $this->addFieldToFilter('weight_to', array('gteq' => $totals['not_free_weight']));
        $this->addFieldToFilter('qty_from', array('lteq' => $totals['not_free_qty']));
        $this->addFieldToFilter('qty_to', array('gteq' => $totals['not_free_qty']));
        $this->addFieldToFilter('shipping_type', array(
            array(
                'eq' => $shippingType,
            ),
            array(
                'eq' => '',
            ),
            array(
                'eq' => '0',
            ),
        ));
        return $this;

    }
}
