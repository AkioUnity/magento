<?php
/**
 * Xcentia_Coster extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       Xcentia
 * @package        Xcentia_Coster
 * @copyright      Copyright (c) 2017
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Product front contrller
 *
 * @category    Xcentia
 * @package     Xcentia_Coster
 * @author      Ultimate Module Creator
 */
class Xcentia_Coster_ProductController extends Mage_Core_Controller_Front_Action
{
    const STATE_VOID            = '0';
    const STATE_SUCCESS         = '1';
    const STATE_ERROR           = '2';
    const STATE_NO_PRICE        = '3';
    const STATE_NOT_AVAILABLE   = '4';

    const MARGIN      = '1.92';

    //https://pricebusters.furniture/coster/product/coster?sku=CB60RT
    public function costerAction() {
        $sku=$this->getRequest()->getParam('sku');
        $iProduct = Mage::getModel('xcentia_coster/product')->load($sku, 'sku');
//        echo "qty".$iProduct->qty;
//        echo "  inventory_status:".$iProduct->inventory_status;
        print_r($iProduct->getData());
//        $proObj->content = json_encode($product);
//        $proObj->save();
    }

    public function testAction() {
        $iproducts = Mage::getModel('xcentia_coster/product')
                        ->getCollection()
                        ->addFieldToFilter(array('status', 'status'),
                                                array(1,5)
                                            )
                        ->addFieldToFilter('filtercode', array('gt' => null))
                        ->setPageSize(20)
                        ->setCurPage(1);
        foreach($iproducts as $iproduct) {
            $iproduct = Mage::getModel('xcentia_coster/product')->load( $iproduct->getId() );
            $prodInfo = json_decode($iproduct->getContent());
            $related = $prodInfo->RelatedProductList;
            if(sizeof($related) > 0) {
                $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $iproduct->getSku() );
                if($product) {
                    $aParams = array();
                    $nRelatedCounter = 1;
                    foreach($related as $rsku) {
                        $rProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $rsku);
                        if($rProduct) {
                            $aParams[$rProduct->getId()] = array('position' => $nRelatedCounter);
                            $nRelatedCounter++;
                        }
                    }
		    
                    $product->setRelatedLinkData($aParams);
                    $product->save();
                } // print_r($related);
            }
            $iproduct->setStatus(3)->save();
            exit;
        }


        echo $iproducts->getSelect(); exit;

		Mage::getModel('xcentia_coster/observer')->getProducts();
        echo 'hello'; exit;
	}

  	public function recreateAction() {
	    echo 'hello'; exit;
	    die;
	    Mage::getModel('xcentia_coster/observer')->updateProducts();
	    //Mage::getModel('xcentia_coster/observer')->recreateProduct();
	    //
	}
	public function createAction() {
        Mage::getModel('xcentia_coster/observer')->createProduct();
        echo 'hello'; exit;
    }
	public function createhereAction() {
		//Mage::getModel('xcentia_coster/observer')->createProduct();
		$iproducts = Mage::getModel('xcentia_coster/product')
    					->getCollection()
    					->addFieldToFilter('status', 0)
    					->setPageSize(20)
            			->setCurPage(1);

        $store_id = 1;
        $website_id = Mage::app()->getStore($store_id)->getWebsiteId();

        $mediaAttribute = array ('thumbnail','small_image','image');
        $file = new Varien_Io_File();
        $path = Mage::getBaseDir('media').DS.'imports'.DS;
        $file->mkdir($path);
        foreach($iproducts as $iproduct) {
        	$iproduct = Mage::getModel('xcentia_coster/product')->load( $iproduct->getId() );
        	$prodInfo = json_decode($iproduct->getContent());
        	//echo '<pre>'; print_r($prodInfo);
        	if($prodInfo->NumImages > 0 && $prodInfo->IsDiscontinued == false) {
                $product = Mage::getModel('catalog/product')->loadByAttribute('sku',$iproduct->getSku());
        		//$iproduct->setStatus(1)->save();
        		$images = array();
        		$num = 1;
                if($product && !($product->getId() > 0)) {
        		while($num <= $prodInfo->NumImages) {
        			$name = $prodInfo->ProductNumber.'-'.$num.'.jpg';
        			if(!file_exists($path.$name)) {
        				$data = file_get_contents('http://assets.coasteramer.com/productpictures/'.$prodInfo->ProductNumber.'/'.$num.'x900.jpg');
        				$file->write($path.$name, $data);
        			}
        			$images[$num] = $name;
        			$num++;
        		} }

        		$name = '';
        		$description = '';
        		if(!empty($prodInfo->CollectionCode)) {
        			$collect = Mage::getModel('xcentia_coster/collections')->load($prodInfo->CollectionCode, 'collection_code')->getCollectionName();
        			$name .= ucwords(strtolower($collect));
        			$description .= 'Part of the ' . ucwords(strtolower($collect)) . ' by Coaster<br />';
        		}
        		if(!empty($prodInfo->StyleCode)) {
        			$style = Mage::getModel('xcentia_coster/style')->load($prodInfo->StyleCode, 'style_code')->getStyleName();
        			$name .= ucwords(strtolower($style));
        		}
        		$name .= ucwords(strtolower($prodInfo->MeasurementList[0]->PieceName));

        		$description .= 'Model Number: ' . $iproduct->getSku() . '<br />' ;
        		$description .= 'Dimensions: Width: '.$prodInfo->MeasurementList[0]->Width.'  x  Depth: '.$prodInfo->MeasurementList[0]->Length.'  x  Height: '.$prodInfo->MeasurementList[0]->Height.'<br />' ;

        		$cat = Mage::getModel('xcentia_coster/category')
        				->getCollection()
        				->addFieldToFilter('categorycode', $prodInfo->CategoryCode)
        				->addFieldToFilter('subcategorycode', $prodInfo->SubcategoryCode)
        				->addFieldToFilter('piececode', $prodInfo->PieceCode)
        				->getFirstItem();
        		$categories = array(9, $cat->category_id, $cat->subcategory_id, $cat->peice_id);

                if($iproduct->getPrice() > 0) {
                    $price = $iproduct->getPrice();
                } else {
                    $price = $iproduct->getCost() * 1.923;
                }
        		
        		$product->setStoreId($store_id)
					    ->setWebsiteIds(array($website_id))
					    ->setCategoryIds($categories)
					    ->setAttributeSetId('4')
					    ->setPrice( $price )
					    ->setShortDescription($prodInfo->Description)
					    ->setDescription($description)
					    ->setSku($iproduct->getSku())
					    ->setName($prodInfo->Name)
					    ->setWeight($prodInfo->BoxWeight)
					    ->setTaxClassId(0)
					    ->setStatus(1)
					    ->setIsFeatured(0)
					    ->setTypeId('simple')
						->setMetaTitle($name)
						//->setMetaKeyword($details->meta_keyword)
						->setMetaDescription(strip_tags($prodInfo->Description))
						;

                if($product && $product->getId() > 0) {
                    $stock_item = Mage::getModel('cataloginventory/stock_item')->loadByProduct( $product->getId() );
                    $stock_item->setData('is_in_stock', 1);
                    $stock_item->setData('qty', (int)$iproduct->getQty() );
                    $stock_item->save();
                } else {
                    $product->setStockData(array('is_in_stock' => 1, 'qty' => (int)$iproduct->getQty() ));
                }
				//echo '<pre>'; print_r($product); exit;
                if($product && !($product->getId() > 0)) {
    				foreach($images as $n => $image) {
    					if($n == 1)
    						$product->addImageToMediaGallery($path.$image , $mediaAttribute, false, false );
    					else
    						$product->addImageToMediaGallery($path.$image , null, false, false ); 
    				}
                }
				$product->save();
				echo '<pre>'; print_r($product->getData());
				exit;
        	} else {
        		$iproduct->setStatus(2)->save();
        	}
        }

        echo 'hello'; exit;
	}
	public function inventoryAction() {
		Mage::getModel('xcentia_coster/observer')->getInventory();
        echo 'hello'; exit;
	}
	public function priceAction() {
		Mage::getModel('xcentia_coster/observer')->getPrices();
        echo 'hello'; exit;
	}
	public function costAction() {
		Mage::getModel('xcentia_coster/observer')->getCost();
        echo 'hello'; exit;
	}
	public function collectionAction() {
		Mage::getModel('xcentia_coster/observer')->getCollections();
        echo 'hello'; exit;
	}
	public function styleAction() {
		Mage::getModel('xcentia_coster/observer')->getStyles();
        echo 'hello'; exit;
	}
	public function groupAction() {
		Mage::getModel('xcentia_coster/observer')->getGroups();
        echo 'hello'; exit;
	}

    public function synccosterproductsAction() {
        if($this->getRequest()->getParam('key') == "gorhdufzk"){
            Mage::getModel('xcentia_coster/observer')->syncCosterProducts();
            echo "Done!";
        }else{
            echo "Wrong key!";
        }
    }

    public function synccosterinventoryAction() {
        if($this->getRequest()->getParam('key') == "gorhdufzk"){
            Mage::getModel('xcentia_coster/observer')->syncCosterInventory();
            echo "Done!";
        }else{
            echo "Wrong key!";
        }
    }

    public function synccostercostAction() {
        if($this->getRequest()->getParam('key') == "gorhdufzk"){
            Mage::getModel('xcentia_coster/observer')->syncCosterCost();
            echo "Done!";
        }else{
            echo "Wrong key!";
        }
    }

    public function synccosterexceptionpriceAction() {
        if($this->getRequest()->getParam('key') == "gorhdufzk"){
            Mage::getModel('xcentia_coster/observer')->syncCosterExceptionPrice();
            echo "Done!";
        }else{
            echo "Wrong key!";
        }
    }

    public function updateInventoryAction() {
        if($this->getRequest()->getParam('key') == "gorhdufzk"){
            Mage::getModel('xcentia_coster/observer')->updateInventory();
            echo "Done!";
        }else{
            echo "Wrong key!";
        }
    }

    public function updateproductpriceAction() {
        if($this->getRequest()->getParam('key') == "gorhdufzk"){
            Mage::getModel('xcentia_coster/observer')->updateProductPrice();
            echo "Done!";
        }else{
            echo "Wrong key!";
        }
    }

    public function updatefreeshippingperproductAction() {
        if($this->getRequest()->getParam('key') == "gorhdufzk"){
            Mage::getModel('xcentia_coster/observer')->updateFreeShippingPerProduct();
            echo "Done!";
        }else{
            echo "Wrong key!";
        }
    }

    public function createnewproductAction() {
        if($this->getRequest()->getParam('key') == "gorhdufzk"){
            Mage::getModel('xcentia_coster/observer')->createNewProduct();
            echo "Done!";
        }else{
            echo "Wrong key!";
        }
    }

    public function enablescoasterproductsAction() {
        if($this->getRequest()->getParam('key') == "gorhdufzk"){
            Mage::getModel('xcentia_coster/observer')->enablesCoasterProducts();
            echo "Done!";
        }else{
            echo "Wrong key!";
        }
    }


    //This function reset the state to 0
    //http://pricebusters.local/index.php/coster/product/resetstate?key=gorhdufzk
    public function resetstateAction(){

        $start = microtime(true);

        if($this->getRequest()->getParam('key') == "gorhdufzk"){

            $iProducts = Mage::getModel('xcentia_coster/product')
                ->getCollection();

            foreach($iProducts as $iProduct) {

                $iProduct->setState(self::STATE_VOID)->save();

            }

        }else{
            echo "Wrong key!";
        }

        $time_elapsed_secs = microtime(true) - $start;
        echo "Done in ".round($time_elapsed_secs)." seconds!";
    }

    //This function reset the Create_product_status to 0
    //http://pricebusters.local/index.php/coster/product/reset_create_product_status?key=gorhdufzk
    public function reset_create_product_statusAction(){

        $start = microtime(true);

        if($this->getRequest()->getParam('key') == "gorhdufzk"){

            $iProducts = Mage::getModel('xcentia_coster/product')
                ->getCollection();

            foreach($iProducts as $iProduct) {

                $iProduct->setCreate_product_status(self::STATE_VOID)->save();

            }

        }else{
            echo "Wrong key!";
        }

        $time_elapsed_secs = microtime(true) - $start;
        echo "Done in ".round($time_elapsed_secs)." seconds!";
    }

    //This function set coaster products
    //http://pricebusters.local/index.php/coster/product/setcoasterproduct?key=gorhdufzk
    public function setcoasterproductAction(){

        $start = microtime(true);

        if($this->getRequest()->getParam('key') == "gorhdufzk"){

            $iProducts = Mage::getModel('xcentia_coster/product')
                ->getCollection()
                ->addFieldToFilter('update_product_status', '0')
                ->setPageSize(10000)
                ->setCurPage(1);

            if($iProducts->getSize() > 0){


                foreach($iProducts as $iProduct) {

                    $iProductObject = Mage::getModel('xcentia_coster/product')->load( $iProduct->getId() );

                    if($iProductObject->getSku()) {
                        $productId = Mage::getModel('catalog/product')->getIdBySku( $iProductObject->getSku() );
                        if($productId){
                            $product = Mage::getModel('catalog/product')->load($productId);
                            $product->setIs_coaster(1);
                            try {
                                $product->save();
                                $iProductObject->setUpdate_product_status(1)->save();
                                $log =  "\n".'Setting coaster for product SKU ['.$iProductObject->getSku().'] ID ['.$iProductObject->getEntity_id()."]\n";
                                Mage::log($log, null, 'product_sync.log', true);
                            } catch (Exception $e) {
                                Mage::logException($e);
                            }
                        }
                    }
                }
            }

        }else{
            echo "Wrong key!";
        }

        $time_elapsed_secs = microtime(true) - $start;
        echo "Done in ".round($time_elapsed_secs)." seconds!";
    }

    //This function reset the update_product_status to 0
    //http://pricebusters.local/index.php/coster/product/reset_update_product_status?key=gorhdufzk
    public function reset_update_product_statusAction(){

        $start = microtime(true);

        if($this->getRequest()->getParam('key') == "gorhdufzk"){

            $iProducts = Mage::getModel('xcentia_coster/product')
                ->getCollection();

            foreach($iProducts as $iProduct) {

                $iProduct->setUpdate_product_status(self::STATE_VOID)->save();

            }

        }else{
            echo "Wrong key!";
        }

        $time_elapsed_secs = microtime(true) - $start;
        echo "Done in ".round($time_elapsed_secs)." seconds!";
    }


    //http://pricebusters.local/index.php/coster/product/setshippingprice?key=gorhdufzk
    public function setshippingpriceAction(){

        $start = microtime(true);

        if($this->getRequest()->getParam('key') == "gorhdufzk"){

            $iProducts = Mage::getModel('xcentia_coster/product')
                ->getCollection()
                ->addFieldToFilter('update_product_status', '0')
                ->setPageSize(500)
                ->setCurPage(1)
            ;

            foreach($iProducts as $iProduct) {

                $iProductObject = Mage::getModel('xcentia_coster/product')->load( $iProduct->getId() );

                $productId = Mage::getModel('catalog/product')->getIdBySku( $iProductObject->getSku() );

                if($productId){
                    $product = Mage::getModel('catalog/product')->load($productId);

                    if($iProductObject->getPrice() > 0) {
                        $product->setMultishipping_rate('0');

                    }else{
                        $product->setMultishipping_rate('150');
                    }

                    try {
                        $product->save();
                        $log =  "\n".'Setting shipping price for product SKU ['.$iProductObject->getSku().'] ID ['.$iProductObject->getEntity_id()."]\n";
                        Mage::log($log, null, 'shipping_price_sync.log', true);
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                }
                $iProduct->setUpdate_product_status(1)->save();
            }

        }else{
            echo "Wrong key!";
        }

        $time_elapsed_secs = microtime(true) - $start;
        echo "Done in ".round($time_elapsed_secs)." seconds!";
    }



}
