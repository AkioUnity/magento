<?php
/**
 * Webardent_Dealer extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category   	Webardent
 * @package		Webardent_Dealer
 * @copyright  	Copyright (c) 2014
 * @license		http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Service model
 *
 * @category	Webardent
 * @package		Webardent_Dealer
 * @author Ultimate Module Creator
 */
class Xcentia_Coster_Model_Observer {
	
    public function getProducts() {
        $importdate = 'Import Completed On ' . date("d-m-Y", strtotime("now"));
        $products = $this->_sendRequest('GetProductList');

        foreach($products as $product) {
        	$proObj = Mage::getModel('xcentia_coster/product')->load($product->ProductNumber, 'sku');
        	$proObj->sku = $product->ProductNumber;
        	$proObj->content = json_encode($product);
        	$proObj->save();
        }
        Mage::log($importdate, 1, 'import.log');
    }

    public function getInventory() {
        $importdate = 'Inventory Completed On ' . date("d-m-Y", strtotime("now"));
        $inventory = $this->_sendRequest('GetInventoryList');

        foreach($inventory[0]->InventoryList as $product) {
        	$proObj = Mage::getModel('xcentia_coster/product')->load($product->ProductNumber, 'sku');
            if($proObj->getId() > 0) {
                $proObj->qty = $product->QtyAvail;
                $proObj->save();
            }
        	
        }
        Mage::log($importdate, 1, 'import.log');
    }

    public function getCost() {
        $importdate = 'Cost Completed On ' . date("d-m-Y", strtotime("now"));
        $inventory = $this->_sendRequest('GetPriceList');
        foreach($inventory[0]->PriceList as $product) {
            $proObj = Mage::getModel('xcentia_coster/product')->load($product->ProductNumber, 'sku');
            $proObj->cost = $product->Price;
            $proObj->status = 0;
            $proObj->save();
        }
        Mage::log($importdate, 1, 'import.log');
    }

    public function getPrices() {
        $importdate = 'Prices Completed On ' . date("d-m-Y", strtotime("now"));
        $inventory = $this->_sendRequest('GetPriceExceptionList');
        foreach($inventory[0]->PriceExceptionList as $product) {
            $proObj = Mage::getModel('xcentia_coster/product')->load($product->ProductNumber, 'sku');
            $proObj->price = $product->Price;
            $proObj->status = 0;
            $proObj->save();
        }
        Mage::log($importdate, 1, 'import.log');
    }
    
    public function getCategories() {
        $importdate = 'getCategories Completed On ' . date("d-m-Y", strtotime("now"));
        $categories = $this->_sendRequest('GetCategoryList');
        $storeId = Mage::app()->getStore()->getId();
        foreach($categories as $cat) {
        	$catName = ucwords(strtolower($cat->CategoryName));
        	$urlKey = Mage::getModel('catalog/product_url')->formatUrlKey( $catName );
        	$category = Mage::getModel('catalog/category');
		    $category->setName($catName);
		    $category->setUrlKey($urlKey);
		    $category->setIsActive(1);
		    $category->setDisplayMode(Mage_Catalog_Model_Category::DM_PRODUCT);
		    $category->setIsAnchor(1); //for active achor
		    $category->setStoreId($storeId);
		    $parentCategory = Mage::getModel('catalog/category')->load(9);
		    $category->setPath($parentCategory->getPath());
		    $category->save();

		    foreach($cat->SubCategoryList as $subcat) {
		    	$catName = ucwords(strtolower($subcat->SubCategoryName));
	        	$urlKey = Mage::getModel('catalog/product_url')->formatUrlKey( $catName );
	        	$subcategory = Mage::getModel('catalog/category');
			    $subcategory->setName($catName);
			    $subcategory->setUrlKey($urlKey);
			    $subcategory->setIsActive(1);
			    $subcategory->setDisplayMode(Mage_Catalog_Model_Category::DM_PRODUCT);
			    $subcategory->setIsAnchor(1); //for active achor
			    $subcategory->setStoreId($storeId);
			    $subcategory->setPath($category->getPath());
			    $subcategory->save();

			    foreach($subcat->PieceList as $piece) {
			    	$catName = ucwords(strtolower($piece->PieceName));
		        	$urlKey = Mage::getModel('catalog/product_url')->formatUrlKey( $catName );
		        	$peicecategory = Mage::getModel('catalog/category');
				    $peicecategory->setName($catName);
				    $peicecategory->setUrlKey($urlKey);
				    $peicecategory->setIsActive(1);
				    $peicecategory->setDisplayMode(Mage_Catalog_Model_Category::DM_PRODUCT);
				    $peicecategory->setIsAnchor(1); //for active achor
				    $peicecategory->setStoreId($storeId);
				    $peicecategory->setPath($subcategory->getPath());
				    $peicecategory->save();

				    $catObj = Mage::getModel('xcentia_coster/category');
		        	$catObj->piececode = $piece->PieceCode;
		        	$catObj->subcategorycode = $subcat->SubCategoryCode;
		        	$catObj->categorycode = $cat->CategoryCode;
		        	$catObj->category_id = $category->getId();
		        	$catObj->subcategory_id = $subcategory->getId();
		        	$catObj->peice_id = $peicecategory->getId();
		        	$catObj->status = 1;
		        	$catObj->save();
			    }

		    }
        }
        Mage::log($importdate, 1, 'import.log');
    }

    
    public function getCollections() {
    	$importdate = 'Collections Completed On ' . date("d-m-Y", strtotime("now"));
        $collections = $this->_sendRequest('GetCollectionList');
        //echo '<pre>'; print_r($collections); exit;
        foreach($collections as $collection) {
        	$proObj = Mage::getModel('xcentia_coster/collections')->load($collection->CollectionCode, 'collection_code');
        	$proObj->collection_code = $collection->CollectionCode;
        	$proObj->collection_name = $collection->CollectionName;
        	$proObj->save();
        }
        Mage::log($importdate, 1, 'import.log');
    }

    public function getStyles() {
    	$importdate = 'Style Completed On ' . date("d-m-Y", strtotime("now"));
        $styles = $this->_sendRequest('GetStyleList');
        //echo '<pre>'; print_r($styles); exit;
        foreach($styles as $style) {
        	$proObj = Mage::getModel('xcentia_coster/style')->load($style->StyleCode, 'style_code');
        	$proObj->style_code = $style->StyleCode;
        	$proObj->style_name = $style->StyleName;
        	$proObj->save();
        }
        Mage::log($importdate, 1, 'import.log');
    }
    
    public function getGroups() {
    	$importdate = 'Collections Completed On ' . date("d-m-Y", strtotime("now"));
        $groups = $this->_sendRequest('GetGroupList');
        echo '<pre>'; print_r($groups); exit;
        foreach($inventory[0]->PriceExceptionList as $product) {
        	$proObj = Mage::getModel('xcentia_coster/product')->load($product->ProductNumber, 'sku');
        	$proObj->price = $product->Price;
        	$proObj->save();
        }
        Mage::log($importdate, 1, 'import.log');
    }
    
    public function create11Product() {
        exit;
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
        	if($prodInfo->NumImages > 0) {
                $product = Mage::getModel('catalog/product')->loadByAttribute('sku',$iproduct->getSku());
        		$iproduct->setStatus(1)->save();
        		$images = array();
        		$num = 1;
                if(!($product->getId() > 0)) {
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
                    $price = $iproduct->getCost() * 1.92;
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

                if($product->getId() > 0) {
                    $stock_item = Mage::getModel('cataloginventory/stock_item')->loadByProduct( $product->getId() );
                    $stock_item->setData('is_in_stock', 1);
                    $stock_item->setData('qty', (int)$iproduct->getQty() );
                    $stock_item->save();
                } else {
                    $product->setStockData(array('is_in_stock' => 1, 'qty' => (int)$iproduct->getQty() ));
                }
				//echo '<pre>'; print_r($product); exit;
                if(!($product->getId() > 0)) {
    				foreach($images as $n => $image) {
    					if($n == 1)
    						$product->addImageToMediaGallery($path.$image , $mediaAttribute, false, false );
    					else
    						$product->addImageToMediaGallery($path.$image , null, false, false ); 
    				}
                }
				$product->save();
        	} else {
        		$iproduct->setStatus(2)->save();
        	}
        }
    }

    public function recreateProduct() {
        $prods = array();
        $iproducts = Mage::getModel('xcentia_coster/product')
                        ->getCollection()
                        ->addFieldToFilter('status', 0)
                        ->setPageSize(20)
                        ->setCurPage(1);
        if($iproducts->getSize() > 0 ) {
            foreach($iproducts as $iproduct) {
                $prods[] = $iproduct->getSku();
            }
            $filtercode = $this->_sendRequest('getFilter?ProductNumber='.implode(',', $prods));

            $products = $this->_sendRequest('GetProductList?filterCode='.$filtercode);

            foreach($products as $product) {
                $iproduct = Mage::getModel('xcentia_coster/product')->load($product->ProductNumber, 'sku');
                $iproduct->setFiltercode($filtercode);
                $iproduct->setContent(json_encode($product));
                $iproduct->setStatus(5);
                $iproduct->save();
            }
        }
    }
    public function createProduct() {
        $iproducts = Mage::getModel('xcentia_coster/product')
                        ->getCollection()
                        ->addFieldToFilter('status', 5)
                        ->setPageSize(20)
                        ->setCurPage(1);
      if($iproducts->getSize() > 0) {
        $store_id = 1;
        $website_id = Mage::app()->getStore($store_id)->getWebsiteId();

        $mediaAttribute = array ('thumbnail','small_image','image');
        $file = new Varien_Io_File();
        $path = Mage::getBaseDir('media').DS.'imports'.DS;
        $file->mkdir($path);
        foreach($iproducts as $iproduct) {
            $iproduct = Mage::getModel('xcentia_coster/product')->load( $iproduct->getId() );
            $id = Mage::getModel('catalog/product')->getIdBySku( $iproduct->getSku() );
            $prodInfo = json_decode($iproduct->getContent());
            //echo '<pre>'; print_r($prodInfo);
            if($prodInfo->NumImages > 0 && false === $id && $prodInfo->IsDiscontinued == false) {
                $product = Mage::getModel('catalog/product');
                $iproduct->setStatus(1)->save();

                $images = array();
                $num = 1;
                while($num <= $prodInfo->NumImages) {
                    $name = $prodInfo->ProductNumber.'-'.$num.'.jpg';
                    if(!file_exists($path.$name)) {
                        $data = file_get_contents('http://assets.coasteramer.com/productpictures/'.$prodInfo->ProductNumber.'/'.$num.'x900.jpg');
                        $file->write($path.$name, $data);
                    }
                    $images[$num] = $name;
                    $num++;
                }

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
                $isshipped = 0;
                if($iproduct->getPrice() > 0) {
                    $price = $iproduct->getPrice();
                    $isshipped = 1;
                } else {
                    $price = $iproduct->getCost() * 1.92;
                }
                if($prodInfo->MeasurementList[0]->Weight > 150 || $prodInfo->MeasurementList[0]->Length > 108 || $prodInfo->MeasurementList[0]->Width > 165){
		    $shippable = 0;
		}else{
		    $shippable = 1;
		}
                $product->setStoreId($store_id)
                        ->setWebsiteIds(array($website_id))
                        ->setCategoryIds($categories)
                        ->setAttributeSetId('4')
                        ->setPrice( $price )
                        ->setCost( $iproduct->getPrice() )
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
                        ->setPkgQty($prodInfo->PackQty)
			->setShipable($shippable)
                        ->setIsShipped($isshipped)
                        //->setMetaKeyword($details->meta_keyword)
                        ->setMetaDescription(strip_tags($prodInfo->Description))
                        ;
                $product->setStockData(array('is_in_stock' => 1, 'qty' => (int)$iproduct->getQty() ));
                //echo '<pre>'; print_r($product); exit;
                foreach($images as $n => $image) {
                    if($n == 1)
                        $product->addImageToMediaGallery($path.$image , $mediaAttribute, false, false );
                    else
                        $product->addImageToMediaGallery($path.$image , null, false, false ); 
                }
                try {
                    $product->save();
                } catch (Exception $e) {
                    Mage::logException($e);
                    $iproduct->setStatus(2)->save();
                }
            } else {
                $iproduct->setStatus(2)->save();
            }
        }
      } else {
        // Run the related products code
            $iproducts = Mage::getModel('xcentia_coster/product')
                            ->getCollection()
                            ->addFieldToFilter( array('status', 'status'), array(1,2) )
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
                        $product->setPkgQty($prodInfo->PackQty);
                        $product->save();
                    } // print_r($related);
                }
                $iproduct->setStatus(3)->save();
            }
      }
    }
    public function updateProducts(){
	
	$collection = Mage::getModel('catalog/product')->getCollection();    
	$collection->addAttributeToSelect('weight');        
 
	//filter for products whose weight is greater than (gt) 10
	$collection->addFieldToFilter(array(
	        array('attribute'=>'weight','gt'=>'150'),
	));
	Mage :: app()->setCurrentStore(Mage_Core_Model_App :: ADMIN_STORE_ID);
	$productCollection = $collection;
	$store_id = 1;
	$website_id = Mage::app()->getStore($store_id)->getWebsiteId();
	foreach($productCollection as $_product) {
	    echo "\n".'updating '.$_product->getSku()."...\n";
	    $product = Mage::getModel('catalog/product')->load($_product->getEntityId());
	    $product->setShipable(0);
	    $product->save();
	}
	
	/*
	$prods = array();
	//$currentSku = '100134B';
        $iproducts = Mage::getModel('xcentia_coster/product')
                        ->getCollection()
			//->addFieldToFilter('sku', array('like' => $currentSku.'%'))
                        ->addFieldToFilter('status', 5);
                        //->setPageSize(200)
                        //->setCurPage(1);
	    
	    $store_id = 1;
	    $website_id = Mage::app()->getStore($store_id)->getWebsiteId();
	    foreach($iproducts as $iproduct) {
		$iproduct = Mage::getModel('xcentia_coster/product')->load( $iproduct->getId() );
		$id = Mage::getModel('catalog/product')->getIdBySku( $iproduct->getSku() );
		$prodInfo = json_decode($iproduct->getContent());
		$product = Mage::getModel('catalog/product');
                $iproduct->setStatus(1)->save();
		if($prodInfo->MeasurementList[0]->Weight > 150 || $prodInfo->MeasurementList[0]->Length > 108 || $prodInfo->MeasurementList[0]->Width > 165){
		    $shippable = 0;
		}else{
		    $shippable = 1;
		}
		echo $iproduct->getSku().'<br />';
		//$product->setStoreId($store_id)
                       // ->setWebsiteIds(array($website_id))
			//->setShipable($shippable);
		//$product->save();	
	    }
	 * 
	 */
    }

    protected function _getCategories($catstr) {
    	$cats = explode(' > ', $catstr);
    	$return = array();
    	$readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
    	//$store_id = Mage::app()->getStore()->getId();
    	$store_id = 1;
    	foreach($cats as $k => $cat) {
    		if($cat != '') {
    			$query = "SELECT * FROM catalog_category_entity_view where level = ".($k+2)." and name = '".$cat."'";
				$results = $readConnection->fetchAll($query);
				if(count($results) == 0) {
					if(!$parent) {
						$parent = Mage::getModel('catalog/category')->setStoreId($store_id)->load(2);
					}
					$path = $parent->getPath();
					$level = $parent->getLevel() + 1;
					$url_key = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $cat));
					$category = Mage::getModel('catalog/category')
								    ->setStoreId($store_id)
								    ->setAttributeSetId(3)
								    ->setName($cat)
								    ->setUrlKey($url_key)
								    ->setIsActive(1)
								    ->setIncludeInMenu(1)
								    ->setParentId($parent->getId())
								    ->setPath($path)
								    ->setLevel($level)
								    ->setCustomUseParentSettings(1)
								    ->save();
				} else {
					$category = Mage::getModel('catalog/category')->load($results[0]['category_id']);

				}
    		}
			
			$parent = $category;
			$return[] = $category->getId();
    	}
    	return $return;
    }
	

	function _sendRequest($endpoint) {
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "http://api.coasteramer.com/api/product/" . $endpoint,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 240,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array("keycode: E122443B8549416BAA0629ED0C"),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
		  return false;
		} else {
		  return json_decode($response);
		}
	}
	

}