<?php
class AcimaCredit_AcimaCheckout_Model_Observer {
    
    public function filterStatus(Varien_Event_Observer $observer) {
        $order = $observer->getEvent()->getOrder();

        $payment = $order->getPayment();
        $paymentMethod = $payment->getMethodInstance()->getCode();
        
        if ($paymentMethod === 'acimacheckout' && $order->getState() == Mage_Sales_Model_Order::STATE_COMPLETE) {

            $historyComment = $order->getAllStatusHistory();

            $checkoutToken = '';
            $leaseId = '';

            $regexpCheckoutToken = '/Acima Credit - Checkout Token: (.*)/i';
            $regexpLeaseId = '/Acima Credit - Lease ID: (.*)/i';

            foreach($historyComment as $comment) {

                $comment = $comment->getComment();

                preg_match($regexpCheckoutToken, $comment, $matchCheckoutToken);
                preg_match($regexpLeaseId, $comment, $matchLeaseID);

                if (isset($matchCheckoutToken[1])) {
                    $checkoutToken = $matchCheckoutToken[1];
                }

                if (isset($matchLeaseID[1])) {
                    $leaseId = $matchLeaseID[1];
                }
            }

            if ($checkoutToken && $leaseId) {
                $this->finalizeTransaction($order, $checkoutToken, $leaseId);
            }
        }
    }

    private function finalizeTransaction($order, $checkoutToken, $leaseId) {
        $orderInfo = array();
        $orderInfo['details'] = $this->parseOrder($order);
        $orderInfo['checkoutToken'] = $checkoutToken;

        $this->callAPI($orderInfo, $leaseId);
    }

    private function parseOrder($order) {
        $orderData = $order->getData();
        $products = $order->getAllVisibleItems();
    
        $data = array();
    
        $data['id'] = $order->getId();
        
        $data['lineItems'] = array();
    
        foreach($products as $product){
            array_push($data['lineItems'], array(
                'productName' => preg_replace('/\r|\n/', '', $product->getName()),
                'quantity' => (int) $product->getData('qty_ordered'),
                'unitPrice' => (int) ($product->getPrice() * 100),
                'productId' => $product->getId()
            ));
        }
    
        $data['shipping'] = (int) ($orderData['base_shipping_amount'] * 100);
        $data['discounts'] = (int) ($orderData['base_discount_amount'] * 100);
        $data['salesTax'] = (int) ($orderData['base_tax_amount'] * 100);
    
        return $data;
    }

    private function callAPI($data, $leaseId) {
        Mage::log('[AcimaCredit] call_api finalize');
        $curl = curl_init();
        
        $api_token = Mage::getStoreConfig('payment/acimacheckout/apikey');
        $header = array();
        $header[] = 'Content-type: application/json';
        $header[] = 'Api-Token: ' . $api_token;
    
        $url = $this->formatURL(Mage::getStoreConfig('payment/acimacheckout/apiurl')) . '/merchants/' . Mage::getStoreConfig('payment/acimacheckout/merchantid') . '/leases/' . $leaseId . '/finalize';
        $request_body = json_encode($data);
    
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request_body);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        $body = curl_exec($curl);
        $result = json_decode($body);
    
        if (!isset($result->result)) {
            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            Mage::log('[AcimaCredit] call_api url = ' . $url . ' token = ' . $api_token . ' body = ' . $request_body);
            Mage::log('[AcimaCredit] call_api error');
            Mage::log('status = ' . $status);
            Mage::log('body = ' . $body);
        }
      }

    private function formatURL($url) {
        return trim($url, '/');
    }
}