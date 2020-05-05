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
 * @category    Webardent
 * @package        Webardent_Dealer
 * @copyright    Copyright (c) 2014
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Service model
 *
 * @category    Webardent
 * @package        Webardent_Dealer
 * @author Ultimate Module Creator
 */
class Xcentia_Coster_Model_Observer
{

    const MARGIN = '1.92';
    const SHIPPING_RATE = '150';
    const Init_Status = '6';
    const Created_Status = '5';
    const Price_Status = '4';
    const Qty_Status = '3';
    const Enable_Status = '1';
    const Disable_Status = '0';


    public function getProducts()
    {
        $importdate = 'Import Completed On ' . date("d-m-Y", strtotime("now"));
        $products = $this->_sendRequest('GetProductList');

        foreach ($products as $product) {
            $proObj = Mage::getModel('xcentia_coster/product')->load($product->ProductNumber, 'sku');
            $proObj->sku = $product->ProductNumber;
            $proObj->content = json_encode($product);
            $proObj->save();
        }
        Mage::log($importdate, 1, 'import.log');
    }

    public function getInventory()
    {
        $importdate = 'Inventory Completed On ' . date("d-m-Y", strtotime("now"));
        $inventory = $this->_sendRequest('GetInventoryList');

        foreach ($inventory[0]->InventoryList as $product) {
            $proObj = Mage::getModel('xcentia_coster/product')->load($product->ProductNumber, 'sku');
            if ($proObj->getId() > 0) {
                $proObj->qty = $product->QtyAvail;
                $proObj->save();
            }

        }
        Mage::log($importdate, 1, 'import.log');
    }

    public function getCost()
    {
        $importdate = 'Cost Completed On ' . date("d-m-Y", strtotime("now"));
        $inventory = $this->_sendRequest('GetPriceList');
        foreach ($inventory[0]->PriceList as $product) {
            $proObj = Mage::getModel('xcentia_coster/product')->load($product->ProductNumber, 'sku');
            $proObj->cost = $product->Price;
            $proObj->status = 0;
            $proObj->save();
        }
        Mage::log($importdate, 1, 'import.log');
    }

    public function getPrices()
    {
        $importdate = 'Prices Completed On ' . date("d-m-Y", strtotime("now"));
        $inventory = $this->_sendRequest('GetPriceExceptionList');
        foreach ($inventory[0]->PriceExceptionList as $product) {
            $proObj = Mage::getModel('xcentia_coster/product')->load($product->ProductNumber, 'sku');
            $proObj->price = $product->Price;
            $proObj->status = 0;
            $proObj->save();
        }
        Mage::log($importdate, 1, 'import.log');
    }

    public function getCategories()
    {
        $importdate = 'getCategories Completed On ' . date("d-m-Y", strtotime("now"));
        $categories = $this->_sendRequest('GetCategoryList');
        $storeId = Mage::app()->getStore()->getId();
        foreach ($categories as $cat) {
            $catName = ucwords(strtolower($cat->CategoryName));
            $urlKey = Mage::getModel('catalog/product_url')->formatUrlKey($catName);
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

            foreach ($cat->SubCategoryList as $subcat) {
                $catName = ucwords(strtolower($subcat->SubCategoryName));
                $urlKey = Mage::getModel('catalog/product_url')->formatUrlKey($catName);
                $subcategory = Mage::getModel('catalog/category');
                $subcategory->setName($catName);
                $subcategory->setUrlKey($urlKey);
                $subcategory->setIsActive(1);
                $subcategory->setDisplayMode(Mage_Catalog_Model_Category::DM_PRODUCT);
                $subcategory->setIsAnchor(1); //for active achor
                $subcategory->setStoreId($storeId);
                $subcategory->setPath($category->getPath());
                $subcategory->save();

                foreach ($subcat->PieceList as $piece) {
                    $catName = ucwords(strtolower($piece->PieceName));
                    $urlKey = Mage::getModel('catalog/product_url')->formatUrlKey($catName);
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

    public function getCollections()
    {
        $importdate = 'Collections Completed On ' . date("d-m-Y", strtotime("now"));
        $collections = $this->_sendRequest('GetCollectionList');
        //echo '<pre>'; print_r($collections); exit;
        foreach ($collections as $collection) {
            $proObj = Mage::getModel('xcentia_coster/collections')->load($collection->CollectionCode, 'collection_code');
            $proObj->collection_code = $collection->CollectionCode;
            $proObj->collection_name = $collection->CollectionName;
            $proObj->save();
        }
        Mage::log($importdate, 1, 'import.log');
    }

    public function getStyles()
    {
        $importdate = 'Style Completed On ' . date("d-m-Y", strtotime("now"));
        $styles = $this->_sendRequest('GetStyleList');
        //echo '<pre>'; print_r($styles); exit;
        foreach ($styles as $style) {
            $proObj = Mage::getModel('xcentia_coster/style')->load($style->StyleCode, 'style_code');
            $proObj->style_code = $style->StyleCode;
            $proObj->style_name = $style->StyleName;
            $proObj->save();
        }
        Mage::log($importdate, 1, 'import.log');
    }

    public function getGroups()
    {
        $importdate = 'Collections Completed On ' . date("d-m-Y", strtotime("now"));
        $groups = $this->_sendRequest('GetGroupList');
        echo '<pre>';
        print_r($groups);
        exit;
        foreach ($inventory[0]->PriceExceptionList as $product) {
            $proObj = Mage::getModel('xcentia_coster/product')->load($product->ProductNumber, 'sku');
            $proObj->price = $product->Price;
            $proObj->save();
        }
        Mage::log($importdate, 1, 'import.log');
    }

    public function recreateProduct()
    {
        $prods = array();
        $iproducts = Mage::getModel('xcentia_coster/product')
            ->getCollection()
            ->addFieldToFilter('status', 0)
            ->setPageSize(20)
            ->setCurPage(1);
        if ($iproducts->getSize() > 0) {
            foreach ($iproducts as $iproduct) {
                $prods[] = $iproduct->getSku();
            }
            $filtercode = $this->_sendRequest('getFilter?ProductNumber=' . implode(',', $prods));

            $products = $this->_sendRequest('GetProductList?filterCode=' . $filtercode);

            foreach ($products as $product) {
                $iproduct = Mage::getModel('xcentia_coster/product')->load($product->ProductNumber, 'sku');
                $iproduct->setFiltercode($filtercode);
                $iproduct->setContent(json_encode($product));
                $iproduct->setStatus(5);
                $iproduct->save();
            }
        }
    }

    public function createProduct()
    {
        $iproducts = Mage::getModel('xcentia_coster/product')
            ->getCollection()
            ->addFieldToFilter('status', 5)
            ->setPageSize(20)
            ->setCurPage(1);
        if ($iproducts->getSize() > 0) {
            $store_id = 1;
            $website_id = Mage::app()->getStore($store_id)->getWebsiteId();

            $mediaAttribute = array('thumbnail', 'small_image', 'image');
            $file = new Varien_Io_File();
            $path = Mage::getBaseDir('media') . DS . 'imports' . DS;
            $file->mkdir($path);
            foreach ($iproducts as $iproduct) {
                $iproduct = Mage::getModel('xcentia_coster/product')->load($iproduct->getId());
                $id = Mage::getModel('catalog/product')->getIdBySku($iproduct->getSku());
                $prodInfo = json_decode($iproduct->getContent());
                //echo '<pre>'; print_r($prodInfo);
                if ($prodInfo->NumImages > 0 && false === $id && $prodInfo->IsDiscontinued == false) {
                    $product = Mage::getModel('catalog/product');
                    $iproduct->setStatus(1)->save();

                    $images = array();
                    $num = 1;
                    while ($num <= $prodInfo->NumImages) {
                        $name = $prodInfo->ProductNumber . '-' . $num . '.jpg';
                        if (!file_exists($path . $name)) {
                            $data = file_get_contents('http://assets.coasteramer.com/productpictures/' . $prodInfo->ProductNumber . '/' . $num . 'x900.jpg');
                            $file->write($path . $name, $data);
                        }
                        $images[$num] = $name;
                        $num++;
                    }

                    $name = '';
                    $description = '';
                    if (!empty($prodInfo->CollectionCode)) {
                        $collect = Mage::getModel('xcentia_coster/collections')->load($prodInfo->CollectionCode, 'collection_code')->getCollectionName();
                        $name .= ucwords(strtolower($collect));
                        $description .= 'Part of the ' . ucwords(strtolower($collect)) . ' by Coaster<br />';
                    }
                    if (!empty($prodInfo->StyleCode)) {
                        $style = Mage::getModel('xcentia_coster/style')->load($prodInfo->StyleCode, 'style_code')->getStyleName();
                        $name .= ucwords(strtolower($style));
                    }
                    $name .= ucwords(strtolower($prodInfo->MeasurementList[0]->PieceName));

                    $description .= 'Model Number: ' . $iproduct->getSku() . '<br />';
                    $description .= 'Dimensions: Width: ' . $prodInfo->MeasurementList[0]->Width . '  x  Depth: ' . $prodInfo->MeasurementList[0]->Length . '  x  Height: ' . $prodInfo->MeasurementList[0]->Height . '<br />';

                    $cat = Mage::getModel('xcentia_coster/category')
                        ->getCollection()
                        ->addFieldToFilter('categorycode', $prodInfo->CategoryCode)
                        ->addFieldToFilter('subcategorycode', $prodInfo->SubcategoryCode)
                        ->addFieldToFilter('piececode', $prodInfo->PieceCode)
                        ->getFirstItem();
                    $categories = array(9, $cat->category_id, $cat->subcategory_id, $cat->peice_id);
                    $isshipped = 0;
                    if ($iproduct->getPrice() > 0) {
                        $price = $iproduct->getPrice();
                        $isshipped = 1;
                    } else {
                        $price = $iproduct->getCost() * 1.92;
                    }

                    $total = $prodInfo->MeasurementList[0]->Length + 2 * $prodInfo->MeasurementList[0]->Width + 2 * $prodInfo->MeasurementList[0]->Height;
                    if ($prodInfo->MeasurementList[0]->Weight > 150 || $prodInfo->MeasurementList[0]->Length > 108 || $total > 165) {
                        $shippable = 0;
                    } else {
                        $shippable = 1;
                    }

                    $product->setStoreId($store_id)
                        ->setWebsiteIds(array($website_id))
                        ->setCategoryIds($categories)
                        ->setAttributeSetId('4')
                        ->setPrice($price)
                        ->setCost($iproduct->getPrice())
                        ->setShortDescription($prodInfo->Description)
                        ->setDescription($description)
                        ->setSku($iproduct->getSku())
                        ->setName($prodInfo->Name)
                        ->setWeight($prodInfo->BoxWeight)
                        ->setBoxWidth($prodInfo->BoxSize->Width)
                        ->setBoxLength($prodInfo->BoxSize->Length)
                        ->setBoxHeight($prodInfo->BoxSize->Height)
                        ->setTaxClassId(0)
                        ->setStatus(1)
                        ->setIsFeatured(0)
                        ->setTypeId('simple')
                        ->setMetaTitle($name)
                        ->setPkgQty($prodInfo->PackQty)
                        ->setShipable($shippable)
                        ->setIsShipped($isshipped)
                        //->setMetaKeyword($details->meta_keyword)
                        ->setMetaDescription(strip_tags($prodInfo->Description));
                    $product->setStockData(array('is_in_stock' => 1, 'qty' => (int)$iproduct->getQty()));
                    //echo '<pre>'; print_r($product); exit;

                    foreach ($images as $n => $image) {
                        if ($n == 1)
                            $product->addImageToMediaGallery($path . $image, $mediaAttribute, false, false);
                        else
                            $product->addImageToMediaGallery($path . $image, null, false, false);
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
                ->addFieldToFilter(array('status', 'status'), array(1, 2))
                ->addFieldToFilter('filtercode', array('gt' => null))
                ->setPageSize(20)
                ->setCurPage(1);
            foreach ($iproducts as $iproduct) {
                $iproduct = Mage::getModel('xcentia_coster/product')->load($iproduct->getId());
                $prodInfo = json_decode($iproduct->getContent());
                $related = $prodInfo->RelatedProductList;
                if (sizeof($related) > 0) {
                    $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $iproduct->getSku());
                    if ($product) {
                        $aParams = array();
                        $nRelatedCounter = 1;
                        foreach ($related as $rsku) {
                            $rProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $rsku);
                            if ($rProduct) {
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

    public function updateProducts()
    {
        $iproducts = Mage::getModel('xcentia_coster/product')
            ->getCollection();
        $newarray = array();
        $newsku = '';
        $i = 0;
        foreach ($iproducts as $iproduct) {
            $iproduct = Mage::getModel('xcentia_coster/product')->load($iproduct->getId());
            $id = Mage::getModel('catalog/product')->getIdBySku($iproduct->getSku());
            $prodInfo = json_decode($iproduct->getContent());
            $total = $prodInfo->MeasurementList[0]->Length + 2 * $prodInfo->MeasurementList[0]->Width + 2 * $prodInfo->MeasurementList[0]->Height;

            if ($prodInfo->MeasurementList[0]->Weight > 150 || $prodInfo->MeasurementList[0]->Length > 108 || $total > 165) {
                if ($iproduct->getSku() > 0) {
                    $newsku .= "'" . $iproduct->getSku() . "',";
                    $newarray[$i]['sku'] = $iproduct->getSku();
                    $newarray[$i]['length'] = $prodInfo->MeasurementList[0]->Length;
                    $newarray[$i]['width'] = $prodInfo->MeasurementList[0]->Width;
                    $newarray[$i]['height'] = $prodInfo->MeasurementList[0]->Height;
                    $newarray[$i]['totalsize'] = $total; //L+2W+2H
                    $newarray[$i]['weight'] = $prodInfo->MeasurementList[0]->Weight;
                    $i++;
                }
            }

        }
        $skunew = rtrim($newsku, ',');
        $_productCollection = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSort('created_at', 'DESC')
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('SKU', array('in' => array('460394T', '460394F', '503898', '503976', '505616', '205091KE', '205091KW', '205091Q', '205093', '205095', '205121KE', '205121KW', '205121Q', '205123', '205125', '205250KE', '205250Q', '205253', '205255', '205250KW', '204421Q', '204421T', '204423', '204425', '204421F', '505608', '505612', '950950', '350062T', '350062F', '350063T', '350063F', '350063Q', '350063KE', '350063KW', '350064T', '350064F', '350064Q', '350064KE', '350064KW', '350065Q', '350065KE', '350065KW', '600057', '600084', '600086', '600087', '551101', '551102', '551103', '551141', '551142', '551143', '551161', '551162', '551163', '460390', '460392', '551251', '551252', '551253', '551241', '551242', '551243', '301044QF', '301044K', '301011QF', '301011K', '301017K', '301017QF', '301020QF', '301020K', '801475', '801497', '801525', '121181', '107283', '301067')));

        Mage:: app()->setCurrentStore(Mage_Core_Model_App :: ADMIN_STORE_ID);
        $productCollection = $_productCollection;
        $store_id = 1;
        $website_id = Mage::app()->getStore($store_id)->getWebsiteId();
        foreach ($productCollection as $_product) {
            echo "\n" . 'updating ' . $_product->getSku() . "...\n";
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

    protected function _getCategories($catstr)
    {
        $cats = explode(' > ', $catstr);
        $return = array();
        $readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
        //$store_id = Mage::app()->getStore()->getId();
        $store_id = 1;
        foreach ($cats as $k => $cat) {
            if ($cat != '') {
                $query = "SELECT * FROM catalog_category_entity_view where level = " . ($k + 2) . " and name = '" . $cat . "'";
                $results = $readConnection->fetchAll($query);
                if (count($results) == 0) {
                    if (!$parent) {
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

    function _sendRequest($endpoint)
    {
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


    //This function updates the price column in xcentia_coster/product table with the API.
    public function syncCosterExceptionPrice()
    {

        $importdate = date("d-m-Y H:i:s", strtotime("now"));
        $log = "Exception Price sync started at: " . $importdate;
        Mage::log($log, null, 'price_sync.log', true);

        try {
            $cExceptionPriceList = $this->_sendRequest('GetPriceExceptionList');

            foreach ($cExceptionPriceList[0]->PriceExceptionList as $cProduct) {
                $iProduct = Mage::getModel('xcentia_coster/product')->load($cProduct->ProductNumber, 'sku');
                if ($iProduct->getSku() && $iProduct->price != $cProduct->Price) {
                    $iProduct->price = $cProduct->Price;
                    $iProduct->price_status = "1";
                    $iProduct->save();
                }
            }
            $importdate = date("d-m-Y H:i:s", strtotime("now"));
            $log = "Exception Price sync finished at: " . $importdate;
            Mage::log($log, null, 'price_sync.log', true);

        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    //This function updates the free shipping method
    public function updateFreeShippingPerProduct()
    {

        $start = microtime(true);
        $importdate = date("d-m-Y H:i:s", strtotime("now"));
        $log = "Set shipping price per product started at: " . $importdate;
        Mage::log($log, null, 'shipping_price_sync.log', true);

        $iProducts = Mage::getModel('xcentia_coster/product')
            ->getCollection()
            ->addFieldToFilter('price_status', '1')
            ->setPageSize(10000)
            ->setCurPage(1);

        if ($iProducts->getSize() > 0) {


            foreach ($iProducts as $iProduct) {

                $iProductObject = Mage::getModel('xcentia_coster/product')->load($iProduct->getId());

                if ($iProductObject->getSku()) {

                    $productId = Mage::getModel('catalog/product')->getIdBySku($iProductObject->getSku());

                    if ($productId) {
                        $product = Mage::getModel('catalog/product')->load($productId);
                        if ($iProductObject->getPrice() > 0) {
                            $product->setMultishipping_rate('0');
                        } else {
                            $product->setMultishipping_rate(self::SHIPPING_RATE);
                        }
                        try {
                            $product->save();
                            $iProductObject->setPrice_status(0)->save();
                            $log = "\n" . 'set shipping price for SKU [' . $iProductObject->getSku() . '] ID [' . $iProductObject->getEntity_id() . "]\n";
                            Mage::log($log, null, 'shipping_price_sync.log', true);
                        } catch (Exception $e) {
                            Mage::logException($e);
                        }
                    }
                }
            }
        }

        $time_elapsed_secs = microtime(true) - $start;
        $importdate = date("d-m-Y H:i:s", strtotime("now"));
        $log = "Set shipping price per product finished at: " . $importdate . " Done in " . round($time_elapsed_secs) . " seconds!";
        Mage::log($log, null, 'shipping_price_sync.log', true);
    }

    //This function enables coaster products
    public function enablesCoasterProducts()
    {
        $start = microtime(true);
        $importdate = date("d-m-Y H:i:s", strtotime("now"));
        $log = "Enabling started at: " . $importdate . "----------------";
        Mage::log($log, null, 'product_sync.log', true);

        $iProducts = Mage::getModel('xcentia_coster/product')
            ->getCollection()
            ->addFieldToFilter('state', '1')
            ->setPageSize(1000)
            ->setCurPage(1);

        if ($iProducts->getSize() > 0) {
            foreach ($iProducts as $iProduct) {
                $iProductObject = Mage::getModel('xcentia_coster/product')->load($iProduct->getId());

                if ($iProductObject->getSku()
                    && $iProductObject->getCreate_product_status() == "0"
                    && $iProductObject->getCost_status() == "0"
                    && $iProductObject->getInventory_status() == "0"
                    && $iProductObject->getPrice_status() == "0"
                ) {
                    $productId = Mage::getModel('catalog/product')->getIdBySku($iProductObject->getSku());
                    if ($productId) {
                        $product = Mage::getModel('catalog/product')->load($productId);
                        $product->setStatus(1);
                        try {
                            $product->save();
                            $iProductObject->setState(0)->save();
                            $log = "\n" . 'Enabling product SKU [' . $iProductObject->getSku() . '] ID [' . $iProductObject->getEntity_id() . "]\n";
                            Mage::log($log, null, 'product_sync.log', true);
                        } catch (Exception $e) {
                            $log = "\n" . 'Not able to enable product SKU [' . $iProductObject->getSku() . '] ID [' . $iProductObject->getEntity_id() . "]\n";
                            Mage::log($log, null, 'product_sync.log', true);
                            Mage::logException($e);
                        }
                    }
                }
            }
        }

        $time_elapsed_secs = microtime(true) - $start;
        $importdate = date("d-m-Y H:i:s", strtotime("now"));
        $log = "Enabling finished at: " . $importdate . " Done in " . round($time_elapsed_secs) . " seconds!" . "\n";
        Mage::log($log, null, 'product_sync.log', true);
    }

//0 1 * * *    https://pricebusters.furniture/coster/product/synccostercost?key=gorhdufzk
    public function syncCosterCost()
    {
        $importdate = date("d-m-Y H:i:s", strtotime("now"));
        $log = "Cost sync started at: " . $importdate;
        Mage::log($log, null, 'price_sync.log', true);
        $cn=0;
        try {
            $cPriceList = $this->_sendRequest('GetPriceList?customernumber=16998');
            foreach ($cPriceList[0]->PriceList as $cProduct) {
                $iProduct = Mage::getModel('xcentia_coster/product')->load($cProduct->ProductNumber, 'sku');
                if ($iProduct->getSku() && abs($iProduct->cost-$cProduct->Price)>1 && $iProduct->status < self::Init_Status) {
                    $log = $iProduct->cost."->" . $cProduct->Price;
                    Mage::log($log, null, 'price_sync.log', true);
                    $iProduct->cost = $cProduct->Price;
                    $iProduct->cost_status = "3";
                    $iProduct->save();
                    $cn++;
                }
            }
            $importdate = date("d-m-Y H:i:s", strtotime("now"));
            $log = "sync finished cn: ".$cn."  at: " . $importdate . "\n";
            Mage::log($log, null, 'price_sync.log', true);

        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

//0 */2 * * *  https://pricebusters.furniture/coster/product/updateproductprice?key=gorhdufzk
    public function updateProductPrice()
    {
        $start = microtime(true);
        $importdate = date("d-m-Y H:i:s", strtotime("now"));
        $log = "-----------------------started at: " . $importdate;
        Mage::log($log, null, 'price_sync.log', true);

        $iProducts = Mage::getModel('xcentia_coster/product')
            ->getCollection()
            ->addFieldToFilter('cost_status', '3')
            ->setPageSize(500)
            ->setCurPage(1);
        try {
            Mage::register('isSecureArea', true);
            if ($iProducts->getSize() > 0) {
                $log = "getSize " . $iProducts->getSize();
                Mage::log($log, null, 'price_sync.log', true);
                foreach ($iProducts as $iProduct) {
                    $iProductObject = Mage::getModel('xcentia_coster/product')->load($iProduct->getId());
                    $sku = $iProductObject->getSku();
                    if ($sku) {
                        $price = $iProductObject->getCost() * self::MARGIN;
                        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
                        if ($product) {
//                            if (abs($product->getPrice()-0.1)>1)
//                                continue;
                            $iProductObject->setCost_status(0)->save();
                            $log = 'updating price ' . $sku . ' $' . $product->getPrice() . '-> $' . $price;
                            $product->setPrice($price);
                            if ($iProductObject->status == self::Created_Status) { //init
                                $iProductObject->setStatus(self::Price_Status)->save(); //Inventory Updates Init.
                            } else if ($iProductObject->status == self::Qty_Status) {
                                if ($product->getStatus() < 2) {
                                    $product->setStatus((int)((int)$iProductObject->qty > 0));
                                }
                                $iProductObject->setStatus((int)((int)$iProductObject->qty > 0))->save();
                            }
                            $product->save();
                        } else {
                            $log = '----------------no Product will remove iProduct ' . $sku;
                            $iProductObject->delete();
                        }
                    } else {
                        $log = '----------------no SKU ' . $sku;
                    }
                    Mage::log($log, null, 'price_sync.log', true);
                }
            }
            Mage::unregister('isSecureArea');
        } catch (Exception $e) {
            Mage::logException($e);
        }
        $time_elapsed_secs = microtime(true) - $start;
        $importdate = date("d-m-Y H:i:s", strtotime("now"));
        $log = "finished at: " . $importdate . " Done in " . round($time_elapsed_secs) . " seconds!" . "\n";
        Mage::log($log, null, 'price_sync.log', true);
    }

    //This function checks the products with the API, adds new products to the xcentia_coster/product table
//0 0 * * *  https://pricebusters.furniture/coster/product/syncCosterProducts?key=gorhdufzk
    public function syncCosterProducts()
    {
        $importdate = date("d-m-Y H:i:s", strtotime("now"));
        $log = "Sync started at: " . $importdate;
        Mage::log($log, null, 'product_sync.log', true);
        $cn=0;
        try {
            $cProducts = $this->_sendRequest('GetProductList');
            Mage::register('isSecureArea', true);
            foreach ($cProducts as $cProduct) {
                $sku = $cProduct->ProductNumber;
                $lastSku = substr($sku, -2);
                $B1 = ($lastSku == "B1" || $lastSku == "B2" || $lastSku == "B3") ? true : false;
                $iProduct = Mage::getModel('xcentia_coster/product')->load($sku, 'sku');
                if (!$iProduct->getSku() && !$cProduct->IsDiscontinued && $cProduct->NumImages > 0 && !$B1) {
                    $iProduct->sku = $sku;
                    $iProduct->content = json_encode($cProduct);
                    $iProduct->create_product_status = "1";
                    $iProduct->cost_status = "0";
                    $iProduct->inventory_status = "0";
                    $iProduct->price_status = "0";  //for exception price
                    $iProduct->state = "1";
                    $iProduct->status = self::Init_Status;  //init
                    $iProduct->save();
                    $cn++;
                    $log = "New iProduct: " . $sku;
                    Mage::log($log, null, 'product_sync.log', true);
                } else if ($iProduct->getSku() && ($cProduct->IsDiscontinued || $cProduct->NumImages == 0 || $B1)) {
                    $log = "Delete: " . $sku;
                    Mage::log($log, null, 'product_sync.log', true);
                    $iProduct->delete();
                    $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
                    if ($product)
                        $product->delete();
                }
            }

            $importdate = date("d-m-Y H:i:s", strtotime("now"));
            $log = "sync finished cn:".$cn." at: " . $importdate . "\n";
            Mage::log($log, null, 'product_sync.log', true);

        } catch (Exception $e) {
            Mage::logException($e);
        }
        Mage::unregister('isSecureArea');
    }

    //This function creates new products in magento
//10 * * * *     https://pricebusters.furniture/coster/product/createNewProduct?key=gorhdufzk
    public function createNewProduct()
    {
        $start = microtime(true);
        $importdate = date("d-m-Y H:i:s", strtotime("now"));
        $log = "started at: " . $importdate;
        Mage::log($log, null, 'product_sync.log', true);

        $iProducts = Mage::getModel('xcentia_coster/product')
            ->getCollection()
            ->addFieldToFilter('create_product_status', '1')
            ->setPageSize(100)
            ->setCurPage(1);

        if ($iProducts->getSize() > 0) {
            $store_id = 1;
            $website_id = Mage::app()->getStore($store_id)->getWebsiteId();

            $mediaAttribute = array('thumbnail', 'small_image', 'image');
            $file = new Varien_Io_File();
            $path = Mage::getBaseDir('media') . DS . 'imports' . DS;
            $file->mkdir($path);
            foreach ($iProducts as $iProduct) {
                //echo '<pre>'; print_r($iProduct);die("ok");
                $iProductObject = Mage::getModel('xcentia_coster/product')->load($iProduct->getId());
                $sku = $iProductObject->getSku();
                $productId = Mage::getModel('catalog/product')->getIdBySku($sku);
                $prodInfo = json_decode($iProductObject->getContent());
                //echo '<pre>'; print_r($prodInfo);die("ok");
                $lastSku = substr($sku, -2);
                $B1 = ($lastSku == "B1" || $lastSku == "B2" || $lastSku == "B3") ? true : false;
                if ($prodInfo->NumImages > 0 && false === $productId && $prodInfo->IsDiscontinued == false && !$B1) {
                    $product = Mage::getModel('catalog/product');

                    $images = array();
                    $num = 1;
                    while ($num <= $prodInfo->NumImages) {
                        $name = $prodInfo->ProductNumber . '-' . $num . '.jpg';
                        if (!file_exists($path . $name)) {
                            $data = file_get_contents('http://assets.coasteramer.com/productpictures/' . $prodInfo->ProductNumber . '/' . $num . 'x900.jpg');
                            $file->write($path . $name, $data);
                        }
                        $images[$num] = $name;
                        $num++;
                    }

                    $name = '';
                    $description = '';
                    if (!empty($prodInfo->CollectionCode)) {
                        $collect = Mage::getModel('xcentia_coster/collections')->load($prodInfo->CollectionCode, 'collection_code')->getCollectionName();
                        $name .= ucwords(strtolower($collect));
                        $description .= 'Part of the ' . ucwords(strtolower($collect)) . ' by Coaster<br />';
                    }
                    if (!empty($prodInfo->StyleCode)) {
                        $style = Mage::getModel('xcentia_coster/style')->load($prodInfo->StyleCode, 'style_code')->getStyleName();
                        $name .= ucwords(strtolower($style));
                    }
                    $name .= ucwords(strtolower($prodInfo->MeasurementList[0]->PieceName));

                    $description .= 'Model Number: ' . $iProductObject->getSku() . '<br />';

                    if ($prodInfo->MeasurementList[0]->Width && $prodInfo->MeasurementList[0]->Length && $prodInfo->MeasurementList[0]->Height) {
                        $description .= 'Dimensions: Width: ' . $prodInfo->MeasurementList[0]->Width . '  x  Depth: ' . $prodInfo->MeasurementList[0]->Length . '  x  Height: ' . $prodInfo->MeasurementList[0]->Height . '<br />';
                    }


                    $cat = Mage::getModel('xcentia_coster/category')
                        ->getCollection()
                        ->addFieldToFilter('categorycode', $prodInfo->CategoryCode)
                        ->addFieldToFilter('subcategorycode', $prodInfo->SubcategoryCode)
                        ->addFieldToFilter('piececode', $prodInfo->PieceCode)
                        ->getFirstItem();
                    $categories = array(9, $cat->category_id, $cat->subcategory_id, $cat->peice_id);


                    $price = $iProductObject->getCost() * self::MARGIN;

                    if ($iProductObject->getPrice() > 0) {
                        $product->setMultishipping_rate('0');
                    } else {
                        $product->setMultishipping_rate(self::SHIPPING_RATE);
                    }

                    $shippable = 1;

                    $product->setStoreId($store_id)
                        ->setWebsiteIds(array($website_id))
                        ->setCreatedAt(strtotime('now'))
                        ->setCategoryIds($categories)
                        ->setAttributeSetId('4')
                        ->setPrice($price)
                        ->setCost($iProductObject->getPrice())
                        ->setShortDescription($prodInfo->Description)
                        ->setDescription($description)
                        ->setSku($iProductObject->getSku())
                        ->setName($prodInfo->Name)
                        ->setWeight($prodInfo->BoxWeight)
                        ->setBoxWidth($prodInfo->BoxSize->Width)
                        ->setBoxLength($prodInfo->BoxSize->Length)
                        ->setBoxHeight($prodInfo->BoxSize->Height)
                        ->setTaxClassId(2)
                        ->setStatus(0)
                        ->setIs_coaster(1)
                        ->setIsFeatured(0)
                        ->setTypeId('simple')
                        ->setMetaTitle($name)
                        ->setPkgQty($prodInfo->PackQty)
                        ->setShipable($shippable)
                        ->setMetaDescription(strip_tags($prodInfo->Description))
                        ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);

                    $product->setStockData(array(
                            'manage_stock' => 1,
                            'is_in_stock' => (int)((int)$iProductObject->getQty() > 0),
                            'qty' => (int)$iProductObject->getQty()
                        )
                    );
                    $noImage=false;
                    foreach ($images as $n => $image) {
                        if (file_exists($path . $image)) {
                            if ($n == 1)
                                $product->addImageToMediaGallery($path . $image, $mediaAttribute, false, false);
                            else
                                $product->addImageToMediaGallery($path . $image, null, false, false);
                            $log = $path . $image;
                            Mage::log($log, null, 'product_sync.log', true);
                        } else {
                            $noImage=true;
                            Mage::log("no image", null, 'product_sync.log', true);
                        }
                    }
                    if ($noImage){
                        $log = 'no Image ' . $iProductObject->getSku() . ' will be recreate---------';
                        Mage::log($log, null, 'product_sync.log', true);
                        continue;
                    }

                    //echo '<pre>'; print_r($product); die('OK');
                    try {
                        $iProductObject->setCreate_product_status(0)->save();
                        $iProductObject->setStatus(self::Created_Status)->save();
                        $log = 'new product ' . $iProductObject->getSku() . ' saved';
                        Mage::log($log, null, 'product_sync.log', true);
                        $product->save();
                    } catch (Exception $e) {
                        Mage::logException($e);
                        $log = "\n" . 'Could not save product ' . $iProductObject->getSku() . ' ID [' . $iProductObject->getEntity_id() . "]\n";
                        Mage::log($log, null, 'product_sync.log', true);
                    }
                } else {
                    $iProductObject->setCreate_product_status(2)->save();
                    $iProductObject->setInventory_status(2)->save();
                    $iProductObject->setCost_status(2)->save();
                    $iProductObject->setPrice_status(2)->save();
                    $iProductObject->setStatus(self::Disable_Status)->save();
                    if ($prodInfo->IsDiscontinued != false) {
                        $log = 'not create ' . $iProductObject->getSku() . ' Discontinued';
                    } elseif ($prodInfo->NumImages <= 0) {
                        $log = 'not create ' . $iProductObject->getSku() . 'NumImages=0';
                    } elseif ($productId !== false) {
                        $log = 'not create ' . $iProductObject->getSku() . ' Product Id already exist';
                    } elseif ($B1) {
                        $log = 'not create ' . $iProductObject->getSku() . ' ' . $lastSku;
                    } else {
                        $log = 'not create ' . $iProductObject->getSku() . ' other reason ';
                    }
                    Mage::log($log, null, 'product_sync.log', true);
                }
            }
        }
        $time_elapsed_secs = microtime(true) - $start;
        $importdate = date("d-m-Y H:i:s", strtotime("now"));
        $log = "Create new products finished at: " . $importdate . " Done in " . round($time_elapsed_secs) . " seconds!" . "\n";
        Mage::log($log, null, 'product_sync.log', true);
    }

    // This function updates the qty column in xcentia_coster/product table with the API.
//>*/30 * * * * pricebusters.furniture/coster/product/syncCosterInventory?key=gorhdufzk
    public function syncCosterInventory()
    {
        $importdate = date("d-m-Y H:i:s", strtotime("now"));
        $log = "sync started at: " . $importdate;
        Mage::log($log, null, 'inventory_sync.log', true);

        try {
            $cInventory = $this->_sendRequest('GetInventoryList');
            $inventoryList = $cInventory[1]->InventoryList;  //AT   0->TX
            for ($i = 0; $i < count($inventoryList); $i++) {
                $cProduct = $inventoryList[$i];
                $iProduct = Mage::getModel('xcentia_coster/product')->load($cProduct->ProductNumber, 'sku');
                $qty = ($cProduct->QtyAvail > 0) ? $cProduct->QtyAvail : $cInventory[0]->InventoryList[$i]->QtyAvail;
                if ($iProduct->getSku() && $iProduct->qty != $qty && $iProduct->status < self::Init_Status) {
                    $iProduct->qty = $qty;
                    $iProduct->inventory_status = "1";
                    $iProduct->save();
                }
            }
            $importdate = date("d-m-Y H:i:s", strtotime("now"));
            $log = "Inventory sync finished at: " . $importdate . "\n";
            Mage::log($log, null, 'inventory_sync.log', true);

        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    // This function updates the product qty in magento with the xcentia_coster/product table.
//*/10 * * * *    https://pricebusters.furniture/coster/product/updateInventory?key=gorhdufzk
    public function updateInventory()
    {
        $start = microtime(true);
        $importdate = date("d-m-Y H:i:s", strtotime("now"));
        $log = "started at: " . $importdate;
        Mage::log($log, null, 'inventory_sync.log', true);
        Mage::register('isSecureArea', true);
        $iProducts = Mage::getModel('xcentia_coster/product')
            ->getCollection()
            ->addFieldToFilter('inventory_status', '1')
            ->setPageSize(500)
            ->setCurPage(1);
        if ($iProducts->getSize() > 0) {
            foreach ($iProducts as $iProduct) {
                $iProductObject = Mage::getModel('xcentia_coster/product')->load($iProduct->getId());
                $sku = $iProductObject->getSku();
                $qty = $iProductObject->getQty();
                $iProductObject->setInventory_status(3)->save();
                $updateProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
                if (!$updateProduct) {
                    $iCStatus = $iProductObject->getCreate_product_status();
                    $log = 'No Product will remove ' . $sku . ' Create_product_status:' . $iCStatus;
                    Mage::log($log, null, 'inventory_sync.log', true);
                    $iProductObject->delete();
                    continue;
                }
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($updateProduct->getId());
                if ($stockItem->getId() > 0 and $stockItem->getManageStock()) {
                    $stockItem->setQty((int)$qty);
                    $stockItem->setIsInStock((int)((int)$qty > 0));
                    try {

                        if ($iProductObject->status == self::Created_Status) { //init
                            $iProductObject->setStatus(self::Qty_Status)->save(); //Inventory Updates Init.
                        } else if ($iProductObject->status != self::Qty_Status) {
                            if ($updateProduct->getStatus() < 2) {
                                $updateProduct->setStatus((int)((int)$qty > 0));
                            }
                            $iProductObject->setStatus((int)((int)$qty > 0))->save();
                        }

                        $updateProduct->save();

                        $stockItem->save();
                        $log = 'updating ' . $sku . ' ' . $qty;
                        Mage::log($log, null, 'inventory_sync.log', true);
                    } catch (Exception $e) {
                        $log = "\n" . 'Exception [' . $sku . '] - [' . $qty . "]\n";
                        Mage::log($log, null, 'inventory_sync.log', true);
                        Mage::logException($e);
                    }
                } else {
                    $log = "\n" . 'No Stock ' . $sku . '-' . $qty;
                    Mage::log($log, null, 'inventory_sync.log', true);
                }
            }
        }
        Mage::unregister('isSecureArea');
        $time_elapsed_secs = microtime(true) - $start;
        $importdate = date("d-m-Y H:i:s", strtotime("now"));
        $log = "Update Inventory finished at: " . $importdate . " Done in " . round($time_elapsed_secs) . " seconds!\n";
        Mage::log($log, null, 'inventory_sync.log', true);
    }

}