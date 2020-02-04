<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Table
 */
class Amasty_Table_Model_Observer
{
    /**
     * Append rule product attributes to select by quote item collection
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_SalesRule_Model_Observer
     */
    public function addProductAttributes(Varien_Event_Observer $observer)
    {
        // @var Varien_Object
        $attributesTransfer = $observer->getEvent()->getAttributes();
        
        $result = array();
        $result['am_shipping_type'] = true;
        $attributesTransfer->addData($result);
        
        return $this;
    }

    public function addMethodComment(Varien_Event_Observer $observer)
    {
        $firstBlock = Mage::getConfig()->getBlockClassName('checkout/onepage_shipping_method_available');
        if (Mage::getConfig()->getModuleConfig('AW_Onestepcheckout')->is('active', 'true')){
            $firstBlock = Mage::getConfig()->getBlockClassName('aw_onestepcheckout/onestep_form_shippingmethod');
        }
        $secondBlock = Mage::getConfig()->getBlockClassName('checkout/cart_shipping');
        $block = $observer->getBlock();
        if ((get_class($block) == $firstBlock) || (get_class($block) == $secondBlock)) {
            $transport = $observer->getTransport();
            if (is_object($transport)) {
                $html = trim($transport->getHtml());
                $html = Mage::getModel('amtable/method')->addComment($html, Mage::app()->getStore()->getId());
                $transport->setHtml($html);
            }
        }
    }
}
