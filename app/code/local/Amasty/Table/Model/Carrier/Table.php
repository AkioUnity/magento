<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Table
 */

class Amasty_Table_Model_Carrier_Table extends Mage_Shipping_Model_Carrier_Abstract
{
    protected $_code = 'amtable';

    /**
     * Collect rates for this shipping method based on information in $request
     *
     * @param Mage_Shipping_Model_Rate_Request $data
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request) 
    {
        if (!$this->getConfigData('active')) {
            return false;
        }

        $result = Mage::getModel('shipping/rate_result');

        $collection = Mage::getResourceModel('amtable/method_collection')
            ->addFieldToFilter('is_active', 1)
            ->addStoreFilter($request->getStoreId())
            ->addCustomerGroupFilter($this->getCustomerGroupId($request))
            ->setOrder('pos'); 
                            
        $rates = Mage::getModel('amtable/rate')->findBy($request, $collection);    
        
        $countOfRates = 0; 
        foreach ($collection as $customMethod){
            
            // create new instance of method rate
            $method = Mage::getModel('shipping/rate_result_method');
    
            // record carrier information
            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));

            if (isset($rates[$customMethod->getId()]['cost']))
            {
                // record method information
                $method->setMethod($this->_code . $customMethod->getId());
                $methodTitle = Mage::helper('amtable')->__($customMethod->getLabel($request->getStoreId()));
                $methodTitle = str_replace('{day}', $rates[$customMethod->getId()]['time'], $methodTitle);
                $method->setMethodTitle($methodTitle);

                $method->setCost($rates[$customMethod->getId()]['cost']);
                $method->setPrice($rates[$customMethod->getId()]['cost']);

                $method->setPos($customMethod->getPos());

                // add this rate to the result
                $result->append($method);
                $countOfRates++;
            }

        }
        
        if (($countOfRates == 0) && ($this->getConfigData('showmethod') == 1)){
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        }        

        return $result;
    } 


    public function getAllowedMethods()
    {
        $collection = Mage::getResourceModel('amtable/method_collection')
                ->addFieldToFilter('is_active', 1)
                ->setOrder('pos');
        $arr = array();
        foreach ($collection as $method){
            $methodCode = 'amtable'.$method->getMethodId();
            $arr[$methodCode] = $method->getName();    
        }  
                
        return $arr;
    }
    
    public function getCustomerGroupId($request)
    {
        $allItems = $request->getAllItems();
        if (!$allItems){
            return 0;
        }
        foreach ($allItems as $item)
        {
            return $item->getProduct()->getCustomerGroupId();             
        }

    }
}
