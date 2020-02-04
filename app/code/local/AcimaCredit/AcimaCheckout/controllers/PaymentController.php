<?php
class AcimaCredit_AcimaCheckout_PaymentController extends Mage_Core_Controller_Front_Action 
{
  public function gatewayAction() 
  {
    if ($this->getRequest()->get('orderId') && $this->getRequest()->get('acimacredit_lease_id') && $this->getRequest()->get('acimacredit_checkout_token'))
    {
      $arr_querystring = array(
        'flag' => 1, 
        'orderId' => $this->getRequest()->get('orderId'),
        'leaseId' => $this->getRequest()->get('acimacredit_lease_id'),
        'checkoutToken' => $this->getRequest()->get('acimacredit_checkout_token')
      );
       
      Mage_Core_Controller_Varien_Action::_redirect('acimacheckout/payment/response', array('_secure' => false, '_query'=> $arr_querystring));
    }
  }
   
  public function redirectAction() 
  {
    $this->loadLayout();
    $block = $this->getLayout()->createBlock('Mage_Core_Block_Template','acimacheckout',array('template' => 'acimacheckout/redirect.phtml'));
    $this->getLayout()->getBlock('content')->append($block);
    $this->renderLayout();
  }

  public function failureAction() {
    $orderId = $this->getRequest()->get('orderId');
    
    $session = Mage::getSingleton('checkout/session');
    $cart = Mage::getSingleton('checkout/cart');
    $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
    if ($order->getId())
    {
      $session->getQuote()->setIsActive(false)->save();
      $session->clear();
      try 
      {
        $order->setActionFlag(Mage_Sales_Model_Order::ACTION_FLAG_CANCEL, true);
        $order->cancel()->save();
      } 
      catch (Mage_Core_Exception $e) 
      {
        Mage::logException($e);
      }

      $items = $order->getItemsCollection();
      foreach ($items as $item) 
      {
        try 
        {
          $cart->addOrderItem($item);
        } 
        catch (Mage_Core_Exception $e) 
        {
          $session->addError($this->__($e->getMessage()));
          Mage::logException($e);
          continue;
          }
        }
        $cart->save();
      }    
    
      $this->_redirect('checkout/cart');
  }
 
  public function responseAction() 
  {
    if ($this->getRequest()->get('flag') == '1' && $this->getRequest()->get('orderId') && $this->getRequest()->get('leaseId') && $this->getRequest()->get('checkoutToken')) 
    {
      $orderId = $this->getRequest()->get('orderId');
      $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
      $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, 'Payment Success from Acima Credit.');
      $order->addStatusHistoryComment('Acima Credit - Lease ID: ' . $this->getRequest()->get('leaseId'))->setIsVisibleOnFront(false)->setIsCustomerNotified(false);
      $order->addStatusHistoryComment('Acima Credit - Checkout Token: ' . $this->getRequest()->get('checkoutToken'))->setIsVisibleOnFront(false)->setIsCustomerNotified(false);
      
      // automatically invoice the order
      $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
      $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
      $invoice->register();
      $invoice->getOrder()->setCustomerNoteNotify(false);          
      $invoice->getOrder()->setIsInProcess(true);
      $order->addStatusHistoryComment('Automatically invoiced by Acima Credit.', false);
      $transactionSave = Mage::getModel('core/resource_transaction')->addObject($invoice)->addObject($invoice->getOrder());
      $transactionSave->save();
      // end invoice order
      
      $order->save();
      
      Mage::getSingleton('checkout/session')->unsQuoteId();
      Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/success', array('_secure'=> false));
    }
    else
    {
      Mage::log('[AcimaCredit] finalize_transaction error');
      Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/failure', array('_secure'=> false));
    }
  }
}