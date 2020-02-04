<?php 
/**
 * M-Connect Solutions.
 *
 * NOTICE OF LICENSE
 *

 *
 * @category   Catalog
 * @package    Mconnect_MultiShippingperproduct
 * @author     M-Connect Solutions (http://www.mconnectsolutions.com, http://www.mconnectmedia.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */ 
class Mconnect_MultiShippingperproduct_Model_Carrier_Multishippingperproduct 
extends Mage_Shipping_Model_Carrier_Abstract
implements Mage_Shipping_Model_Carrier_Interface
{
    protected $_code = 'multishippingperproduct';
        
    public function collectRates(Mage_Shipping_Model_Rate_Request $request){
		
		if (!Mage::helper('multishippingperproduct/config')->initializeModule()){
			return $this;
		}
		
        if(Mage::getStoreConfig('carriers/multishippingperproduct/active')){
		if(Mage::app()->getStore()->isAdmin() || Mage::getDesign()->getArea() == 'adminhtml'){
			$quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
		}else{
			$quote = Mage::getSingleton('checkout/session')->getQuote();
		}
		$cartItems = $quote->getAllVisibleItems();
		$cartAddedProducts = array();
		foreach($cartItems as $cartItem){
			$cartAddedProducts[] = $cartItem->getId();
		}
         $items = $request->getAllItems(); //Mage::getSingleton('checkout/session')->getQuote()->getAllItems();
		 $quote = Mage::getSingleton('checkout/cart')->getQuote();
         if(count($items)>0){
         $_quoteitem_qty_rate = 0;
         $multiplication_qty = 0;
         $_qouteitem_main_qty = 1;
         $_qouteitemCnt = 1;
         $perOrderRate = array();
         $multiple_qty = $this->getConfigData('multiply_qty'); 
         $min_max = ($this->getConfigData('max_min'))? $this->getConfigData('max_min') : "max";
		 $Sipping_Rate_Attr = 0;
		 $parentId = $parentQty = NULL;
		 $addedProducts = array();
		 $availableItems = array();
		 
         foreach($items as $item) {
			if(!in_array($item->getId(),$cartAddedProducts)){ continue; }
			$availableItems[] = $item->getProductId();
			$type_id = Mage::getModel('catalog/product')->load($item->getProductId())->getTypeId();
			$qty = $item->getQty();
			if(($item->getIsVirtual() || $type_id == 'virtual' || $type_id == 'downloadable')){
				continue;
            }
			
			/*if($type_id == 'bundle'){
				$parentQty = $item->getQty();
				continue;
			}*/
			// if($item->getParentItemId() == NULL && $item->getHasChildren()){
				// $parentId = $item->getProduct()->getId();
				// $parentQty = $item->getQty();
				// continue;
			// }
			$product = Mage::getModel('catalog/product')->load($item->getProductId());
			$Sipping_Rate_Attr = Mage::getModel('catalog/product')->load($item->getProductId())->getMultishippingRate();
			if($Sipping_Rate_Attr  == ""){
			$bundlesCollection = Mage::getResourceModel('bundle/selection')->getParentIdsByChild($product->getId());
				foreach ($bundlesCollection as $bundleProdId) {
					$qty = $parentQty;
					
					  $parent = Mage::getModel('catalog/product')->load($bundleProdId);
						//if(!in_array($bundleProdId,$addedProducts)){
							$addedProducts[] = $bundleProdId;
							$Sipping_Rate_Attr = $parent->getMultishippingRate();
							
						//}else{
							//$Sipping_Rate_Attr = 0;
						//}
				}
			}
			if($product->getTypeId() == "simple" && $Sipping_Rate_Attr == ""){
				$parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($product->getId());
				if(!$parentIds)
					$parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
				//if(isset($parentIds[0])){
					foreach($parentIds as $parentIdtmp){
						$parent = Mage::getModel('catalog/product')->load($parentIdtmp);
						$Sipping_Rate_Attr = $parent->getMultishippingRate();
						if($Sipping_Rate_Attr != ""){ break; }
					}
				//}
			}
			if(($Sipping_Rate_Attr == '' || $Sipping_Rate_Attr == NULL) && Mage::getStoreConfig('carriers/multishippingperproduct/shipping_default_value_enable')){
                    $Sipping_Rate_Attr = Mage::getStoreConfig('carriers/multishippingperproduct/default_shipping_cost');
             } 
			$perOrderRate[] = $Sipping_Rate_Attr;
			$multiplication_qty = $multiplication_qty + ($qty * $Sipping_Rate_Attr);
            $_qouteitemCnt++;
        }
        
        $result = Mage::getModel('shipping/rate_result');
                
        if($this->getConfigData('methodtype') == 'per order'){
            $multiplication_qty = $min_max($perOrderRate);
        }
        
        $grandTotal = $request->getBaseSubtotalInclTax();
        $free_shipping_over_total = $this->getConfigData('free_shipping_over_total');
        if($this->getConfigData('allow_free_shipping') && $free_shipping_over_total < $grandTotal){
            $multiplication_qty = 0.00;
        }
        
        
			$method = Mage::getModel('shipping/rate_result_method');
					
			$method->setCarrier('multishippingperproduct');
			$method->setCarrierTitle($this->getConfigData('name'));			
			$method->setMethod('multishippingperproduct');    
			$method->setMethodTitle($this->getConfigData('title'));        
			$method->setPrice($multiplication_qty);
			$method->setCost($multiplication_qty);
					
			$result->append($method);
			//if($method->getPrice() == 0){
			//	if($this->getConfigData('allow_free_shipping')){
			//		return $result;
			//	}else{
			//		return '';
			//	}
			//}else{
				return $result;
			//}
         } 
       }
    }
    
    public function getAllowedMethods()
    {
        return array('multishippingperproduct'=>$this->getConfigData('name'));
    }

}
