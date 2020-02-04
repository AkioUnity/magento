<?php
class Biztech_Fedex_Block_Adminhtml_Sales_Order_View_Tab_FedexShipment
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _construct()
    {
        
        parent::_construct();
        $this->setTemplate('fedex/shipment.phtml');
    }

    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    public function getTabLabel()
    {
        return Mage::helper('sales')->__('Fedex Shipment');
    }

    public function getTabTitle()
    {
        return Mage::helper('sales')->__('Fedex Shipment');
    }

    public function canShowTab()
    {
        if ($this->getOrder()->getIsVirtual()) {
            return false;
        }
        return true;
    }

    public function isHidden()
    {
        $active = (int)Mage::getStoreConfig('carriers/fedex/active');
        if($active == 1)
        {
            $address = $this->getOrder()->getShippingAddress();
            $country = $address->getCountry();
            $order = $this->getOrder();
            if($this->getOrder()->getState() != 'canceled' && !$this->getOrder()->getTotalCanceled() && !$order->getTotalRefunded() && $this->getOrder()->hasInvoices() && $this->getOrder()->hasShipments())
            {
                return false;
            }
        }
        return true;
    }

    public function getGenerateShipmentEparcelUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/shipmentTracking/generateShipmentEparcel', array('order_id' => $this->getOrder()->getId()));
    }

    public function getDownloadShippingLabelFileEparcelUrl($shipment_id)
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/shipmentTracking/downloadShippingLabelFileEparcel', array('order_id' => $this->getOrder()->getId(), 'shipment_id' => $shipment_id));
    }
}