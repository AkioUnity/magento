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
        Mage::getModel('xcentia_coster/observer')->recreateProduct();
        echo 'hello'; exit;
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
}
