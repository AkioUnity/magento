<?php

require_once(Mage::getModuleDir('controllers','Mage_Adminhtml').DS. 'Sales'.DS. 'Order' .DS.'ShipmentController.php');

class Biztech_Fedex_Adminhtml_Sales_Order_ShipmentController extends Mage_Adminhtml_Sales_Order_ShipmentController
{
     protected function _createShippingLabel(Mage_Sales_Model_Order_Shipment $shipment)
    {
        

        if (!$shipment) {
            return false;
        }
        $carrier = $shipment->getOrder()->getShippingCarrier();
        if (!$carrier->isShippingLabelsAvailable()) {
            return false;
        }
        $shipment->setPackages($this->getRequest()->getParam('packages'));

        $response = Mage::getModel('shipping/shipping')->requestToShipment($shipment);

        if ($response->hasErrors()) {
            Mage::throwException($response->getErrors());
        }
        if (!$response->hasInfo()) {
            return false;
        }
        $labelsContent = array();
        $trackingNumbers = array();
        $info = $response->getInfo();

        foreach ($info as $inf) {
            if (!empty($inf['tracking_number']) && !empty($inf['label_content'])) {

                

                $labelsContent[] = $inf['label_content'];
                // $returnlabelsContent[] = $inf['return_shipping_label'];
                $etdLabelContent[] = $inf['etd_label_content'];
                $trackingNumbers[] = $inf['tracking_number'];
            }
        }

        $outputPdf = $this->_combineLabelsPdf($labelsContent);

        // $outputPdfReturn = $this->_combineLabelsPdf($returnlabelsContent);
        
        
        
        $shipment->setShippingLabel($outputPdf->render());
        
        // $shipment->setReturnShippingLabel($outputPdfReturn->render());
        $shipment->setEtdLabelContent($etdLabelContent[0]);


        $carrierCode = $carrier->getCarrierCode();
        $carrierTitle = Mage::getStoreConfig('carriers/'.$carrierCode.'/title', $shipment->getStoreId());
        if ($trackingNumbers) {
            foreach ($trackingNumbers as $trackingNumber) {
                $track = Mage::getModel('sales/order_shipment_track')
                        ->setNumber($trackingNumber)
                        ->setCarrierCode($carrierCode)
                        ->setTitle($carrierTitle);
                $shipment->addTrack($track);
            }
        }
        return true;
    }
}
