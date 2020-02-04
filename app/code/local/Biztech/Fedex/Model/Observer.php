<?php
class Biztech_Fedex_Model_Observer
{
	public function salesQuoteItemSetCustomAttributeShipping($observer){


		$isFedex = false;
		if (strpos($observer->getQuote()->getShippingAddress()->getShippingMethod(), 'fedex') !== false) {
		    $isFedex = true;
		}

		if (Mage::helper('fedex')->isEnable() && Mage::getStoreConfig('carriers/fedex/enable_hafl')){
			if($isFedex == true){
				$postData = $observer->getRequest()->getPost();
				if($postData['hal_enable'] == 1) {
					$observer->getQuote()->setFedexHalContent($postData['hal_content']); 
					$observer->getQuote()->setFedexHalEnable($postData['hal_enable']);
				}
				else{
					$observer->getQuote()->setFedexHalContent('null'); 
					$observer->getQuote()->setFedexHalEnable($postData['hal_enable']);		
				}

				
			}
		}

		if (Mage::helper('fedex')->isEnable()){
			if($isFedex == true){

				$quote = $observer->getQuote();
				$quote->setIsDangerousGoods(0);
				$quote->setIsAlchohol(0);
				$quote->setDryIce(0);
				foreach($quote->getAllItems() as $quoteItem){
					$quoteItemId = $quoteItem->getData()['item_id'];
					$quoteItemObj = Mage::getModel('sales/quote_item')->load($quoteItemId);
					if($quoteItemObj->getIsDangerousGoods() == 1){
						$quote->setIsDangerousGoods(1);
					}
					if($quoteItemObj->getIsAlchohol() == 1){
						$quote->setIsAlchohol(1);
					}
					if($quoteItemObj->getDryIce() == 1){
						$quote->setDryIce(1);
					}
				}
			}
		}


	}
	public function setDangerousGoods($observer){
		if (Mage::helper('fedex')->isEnable()){
			$quoteItem = $observer->getQuoteItem();
		    $product = $observer->getProduct();
		    $productObj = Mage::getModel('catalog/product')->load($product->getId());
		    $quote = Mage::getModel('checkout/cart')->getQuote();
			//for dangerouds goods
			if(Mage::getStoreConfig('carriers/fedex/enable_dangerousgoods')){
			    $isDangerousGoods = $productObj->getIsDangerousGoods();
				$quoteItem->setIsDangerousGoods($isDangerousGoods);
			}
			// for alchohol
			if(Mage::getStoreConfig('carriers/fedex/enable_alchoholship')){
				$isAlchohol = $productObj->getIsAlchohol();
				$quoteItem->setIsAlchohol($isAlchohol);
			}
			// for Dry Ice
			if(Mage::getStoreConfig('carriers/fedex/enable_dryice')){
				$DryIce = $productObj->getDryIce();
				$quoteItem->setDryIce($DryIce);
			}
		}
	}
	public function checkKey($observer)
    {
        if( $observer->getWebsite() != '' || $observer->getStore() != '' ) {
            return;
        }
        $key = Mage::getStoreConfig('fedex/activation/key');
        Mage::helper('fedex')->checkKey($key);
	}
}