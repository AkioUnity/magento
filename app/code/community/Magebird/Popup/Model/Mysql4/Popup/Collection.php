<?php
/**
 * Magebird.com
 *
 * @category   Magebird
 * @package    Magebird_Popup
 * @copyright  Copyright (c) 2018 Magebird (http://www.Magebird.com)
 * @license    http://www.magebird.com/licence
 * Any form of ditribution, sell, transfer forbidden see licence above 
 */
class Magebird_Popup_Model_Mysql4_Popup_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();        
        $this->_init('magebird_popup/popup');  
    }
	
	  public function addStoreFilter($store, $adminStore = true) {  
    		$stores = array();
        $extensionKey = Mage::getStoreConfig('magebird_popup/general/extension_key');
        $configModel = Mage::getModel('core/config_data'); //use model to prevent cache
        $trialStart = $configModel->load('magebird_popup/general/trial_start','path')->getData('value');
        if(empty($extensionKey) && ($trialStart<strtotime('-7 days'))){
          $stores[] = 10;
        }else{
          if ($store instanceof Mage_Core_Model_Store) {
              $stores[] = $store->getId();
          }elseif($store){
              $stores[] = $store;
          }
      		$stores[] = 0;           
        }                           

        if ($adminStore) {
            $stores[] = Mage_Core_Model_App::ADMIN_STORE_ID;
        }
                     
    		$storeTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_store');
        $this->getSelect()->join(
                        array('stores' => $storeTable),
                        'main_table.popup_id = stores.popup_id',
                        array()
                )
                ->where('stores.store_id in (?)', $stores)
                ->group('main_table.popup_id');
        
        return $this;
    }                            
    
	  public function addPageFilter($page) {
        if(Mage::app()->getRequest()->getParam('url')){
          $url = str_replace(array("index.php/","http://","https://"),"",urldecode(Mage::app()->getRequest()->getParam('url')));
        }else{
          $requestUri = Mage::app()->getRequest()->getOriginalRequest()->getRequestUri();
          $url = str_replace(array("index.php/","http://","https://"),"",$_SERVER['HTTP_HOST'].$requestUri);        
        }                
        
    		$pageTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_page');
        $this->getSelect()->join(
                              array('pages' => $pageTable),
                              'main_table.popup_id = pages.popup_id',
                              array()
                            );
        if($page){
          $this->getSelect()->where("pages.page_id = ".intval($page)." OR pages.page_id = 0 OR (pages.page_id = 6)");
        }                                                       
        return $this;
    }
    
    public function specifiedUrlFilter($url,$isNegative=false) {
      if($isNegative){
        if(!$url) return false;
      }else{
        if(!$url) return true;
      }      
      $urls = explode(",,",$url);          
      if(Mage::app()->getRequest()->getParam('url')){
        $currentUrl = str_replace(array("index.php/","http://","https://"),"",urldecode(Mage::app()->getRequest()->getParam('url')));
      }else{
        $requestUri = Mage::app()->getRequest()->getOriginalRequest()->getRequestUri();
        $currentUrl = str_replace(array("index.php/","http://","https://"),"",$_SERVER['HTTP_HOST'].$requestUri);        
      }   
      
      foreach($urls as $url){   
        if(substr($url, -1)=="%" && substr($url, 0,1)=="%"){            
          if(strpos($currentUrl,trim($url,'%'))!==false) return true;
        }elseif(substr($url, 0,1)=="%"){
          if(ltrim($url,'%') == substr($currentUrl, -(strlen($url)-1))) return true;
        }elseif(substr($url, -1)=="%"){
          if(rtrim($url,'%') == substr($currentUrl, 0, strlen($url)-1)) return true;
        }else{
          if($url == $currentUrl) return true;
        }
      }
      return false;
    }                  
    
	  public function addIfRefferalFilter() {           
        $url = null;  
        if(Mage::app()->getRequest()->getParam('ref')){
          $url = str_replace(array("index.php/","http://","https://"),"",urldecode(Mage::app()->getRequest()->getParam('ref')));
        }
        $refferal = parse_url("http://".$url);
        $refDomain = $refferal['host'];
        $curDomain = $_SERVER["HTTP_HOST"];
        if($refDomain!=$curDomain){
          Mage::getSingleton('core/session')->setPopupReferer($url);
        }elseif(Mage::getSingleton('core/session')->getPopupReferer()){
          $url = Mage::getSingleton('core/session')->getPopupReferer();
        }
        
        $referralTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_referral');                              
        $this->getSelect()->joinLeft(
                  array('referrals' => $referralTable),
                  'main_table.popup_id = referrals.popup_id',
                  array()
                )
        ->where("referrals.referral = '' OR referrals.referral IS NULL OR ? LIKE referrals.referral",$url);
        
        if(!$url) $url = 'something'; //otherwise notreferrals won't be null 
        $notreferralTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_notreferral');
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');
        $this->getSelect()->joinLeft(
                        array('notreferrals' => $notreferralTable),
                        $this->getConnection()->quoteInto(
                                            "main_table.popup_id = notreferrals.popup_id AND ? LIKE notreferrals.not_referral",$url
                                            ),                        
                        array()
                )     
                ->where("notreferrals.not_referral IS null"); 

        return $this;
    }                   
    
	  public function addProductFilter($productId,$targetPageId) {
        $productIds = array(intval($productId),0);
    		$productsTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_product');
        $this->getSelect()->join(
                        array('products' => $productsTable),
                        'main_table.popup_id = products.popup_id',
                        array()
                );
        if($targetPageId==2){
          $this->getSelect()->where('products.product_id in (?)', $productIds);                   
        }      

        return $this;
    }   
    
	  public function addIpFilter() {
    
    		$couponsTable = Mage::getSingleton('core/resource')->getTableName('salesrule_coupon');
        $this->getSelect()->joinLeft(
                        array('coupons' => $couponsTable),
                        $this->getConnection()->quoteInto(
                                            "main_table.cookie_id = coupons.popup_cookie_id AND ? = coupons.user_ip",Mage::helper('magebird_popup')->getIp()
                                            ),                        
                        array()
                )     
                ->where("coupons.user_ip IS null OR coupons.user_ip=''");   

        return $this;
    }         
    
	  public function addCategoryFilter($categoryId,$targetPageId) {
        $categoryIds = array(intval($categoryId),0);
    		$categoriesTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_category');
        $this->getSelect()->join(
                        array('categories' => $categoriesTable),
                        'main_table.popup_id = categories.popup_id',
                        array()
                );        
        if($targetPageId==3){
          $this->getSelect()->where('categories.category_id in (?)', $categoryIds);        
        }
        return $this;
    }           
    
	  public function addCustomerGroupsFilter() {
     
    		$groupId = intval($this->getCustomerGroupId());
    		$groupsTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_customer_group');
        $this->getSelect()->joinLeft(
                        array('groups' => $groupsTable),
                        'main_table.popup_id = groups.popup_id',
                        array()
                )
                ->where("groups.customer_group_id =$groupId OR groups.customer_group_id IS NULL");
        
        return $this;
    }
    
	  public function addDaysFilter() {
     
    		$day = Mage::getSingleton('core/date')->date('w');
    		$daysTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_day');
        $this->getSelect()->joinLeft(
                        array('days' => $daysTable),
                        'main_table.popup_id = days.popup_id',
                        array()
                )
                ->where("days.day = $day OR days.day IS NULL");
        
        return $this;
    }    
    
	  public function addCountryFilter($countryId) {
        if(!isset($countryId)) return $this;
        $countryIds = array($countryId,'');
    		$countriesTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_country');
        $this->getSelect()->joinLeft(
                        array('countries' => $countriesTable),
                        'main_table.popup_id = countries.popup_id',
                        array()
                )
                ->where('countries.country_id IS NULL OR countries.country_id in (?)', $countryIds);         
        return $this;
    }   
    
	  public function addNotCountryFilter($countryId) {
        if(!isset($countryId)) return $this;
        $countryId = substr($countryId,0,5);
    		$countriesTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_notcountry');
        $this->getSelect()->joinLeft(
                        array('notcountries' => $countriesTable),
                        "main_table.popup_id = notcountries.popup_id AND notcountries.country_id='$countryId'",
                        array()
                )     
                ->where("notcountries.country_id IS null");         
        return $this;
    }       

    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(Zend_Db_Select::GROUP);
        return $countSelect;
    }
    
    public function getCustomerGroupId(){
      $customer = Mage::getSingleton('customer/session')->getCustomer();
      $groupId =  $customer->getGroupId();    
      return $groupId; 
    }              
    
    public function addNowFilter() {
        //$now = Mage::getSingleton('core/date')->gmtDate();
        $now = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));
        $where = "((from_date < '" . $now . "') OR (from_date IS NULL)) AND ((to_date > '" . $now . "') OR (to_date IS NULL))";
        $this->getSelect()->where($where);
		    return $this;
    }
    
    public function checkAttributes($productId,$attributes){
        $globalFalse = false;
        if(!$productId) return false;                      
        $_product = Mage::helper('magebird_popup')->getProduct($productId);        
        $attrs = explode(",,",$attributes);                 
        foreach($attrs as $attr){
          $orCond = false;
          if(strpos($attr,"OR ")!==false){
            $attr = str_replace("OR ", '', $attr);
            $orCond = true;  
          }                  
          $operators = array('!=EMPTY','=EMPTY','<','>','=','!=','>=','<=');
          foreach($operators as $opr){
            if(strpos($attr,$opr)!==false){
              if($opr=="=" && 
                (strpos($attr,"!=")!==false 
                || strpos($attr,">=")!==false 
                || strpos($attr,"!=EMPTY")!==false
                || strpos($attr,"=EMPTY")!==false
                || strpos($attr,"<=")!==false)
              ) continue;        
              if($opr=="!=" && strpos($attr,"!=EMPTY")!==false) continue;
              if($opr=="=EMPTY" && strpos($attr,"!=EMPTY")!==false) continue;
              if($opr=="<" && strpos($attr,"<=")!==false) continue;
              if($opr==">" && strpos($attr,">=")!==false) continue;                       
              $attrData = explode($opr,$attr);
              $code = $attrData[0];
              $value = $attrData[1];            
              if($productId && $_product->getData($code)){  
                if($_product->getAttributeText($code)){
                  $prodVal = $_product->getAttributeText($code); 
                }else{
                  $prodVal = $_product->getData($code);
                }                              
              }elseif($code=='qty'){
                $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);
                $prodVal = $stock->getData('qty');          
              }else{
                $prodVal = '';
              }                        
              
              $currentFalse = false;   
            switch($opr){
              case '!=EMPTY':
                if(empty($prodVal)){
                  if(!$orCond) $globalFalse = true;
                  $currentFalse = true;
                }
                break;
              case '=EMPTY':
                if(!empty($prodVal)){
                  if(!$orCond) $globalFalse = true;
                  $currentFalse = true;
                }
                break;
              case '<':
                if($prodVal>=$value){
                  if(!$orCond) $globalFalse = true;
                  $currentFalse = true;
                }
                break;   
              case '>':
                if($prodVal<=$value){
                  if(!$orCond) $globalFalse = true;
                  $currentFalse = true;
                }
                break;   
              case '=':
                  $value = explode(",", $value);
                  $matches = false;
                  foreach($value as $val){
                    if(substr_count($val,"%")==2){   
                      $val = trim($val,"%");  
                      if(strpos($prodVal, $val)!==false){
                        $matches = true;
                      }
                    }else{
                      if($prodVal==$val){
                        $matches = true;
                      }
                    }                     
                  }
                  if(!$matches){
                    if(!$orCond) $globalFalse = true;
                    $currentFalse = true;
                  }                                  
                  break;  
              case '!=':                    
                if($prodVal==$value){
                  if(!$orCond) $globalFalse = true;
                  $currentFalse = true;
                }
                break;
              case '>=':
                if($prodVal<$value){
                  if(!$orCond) $globalFalse = true;
                  $currentFalse = true;
                }
                break;        
              case '<=':
                if($prodVal>$value){
                  if(!$orCond) $globalFalse = true;
                  $currentFalse = true;
                }
                break;                                                                                                                                   
            } 
            if($orCond && !$currentFalse){
              return true;
            }    
                                                                   
            }                       
          }                     
        } 
        if($globalFalse){
          return false;
        } 
        return true;
    } 
    
    public function productCartAttrFilter($attr){
      if(empty($attr)) return true;    
      if(Mage::helper('magebird_popup')->getPopupCookie('magentoSessionId')==$_COOKIE['frontend']){    
        $ids = Mage::helper('magebird_popup')->getPopupCookie('cartProductIds');
        $ids = explode(",",$ids);                
        foreach($ids as $productId){                                                               
          if($this->checkAttributes($productId,$attr)){
            return true;
          } 
        }      
      }         
      return false; //no product in cart  
    }
    
    public function productCatFilter($productCatsFilter){
      if(empty($productCatsFilter)) return true;
      $_product = Mage::helper('magebird_popup')->getProduct();
      if(!$_product) return false;
      $currentProdCats = $_product->getResource()->getCategoryIds($_product);
      $productCatsFilter = explode(",",$productCatsFilter);
      foreach($productCatsFilter as $cat){
        if(in_array($cat, $currentProdCats)){
          return true;
        }
      }
      return false;
    }     
    
    public function cartProductCatFilter($cartProductCatsFilter){
      if(empty($cartProductCatsFilter)) return true;
      if(Mage::helper('magebird_popup')->getPopupCookie('magentoSessionId')==$_COOKIE['frontend']){    
        $ids = Mage::helper('magebird_popup')->getPopupCookie('cartProductIds');
        $ids = explode(",",$ids); 
        $cartCategories = array();               
        foreach($ids as $productId){   
          $productCats = $this->getProductCategories($productId);
          $cartCategories = array_merge($cartCategories,$productCats);                                                                          
        }   
         
        $productsCats = explode(",",$cartProductCatsFilter);
        
        foreach($productsCats as $cat){
          if(in_array($cat, $cartCategories)){
            return true;
          }
        }     
      }
            
      return false; //no product in cart              
    }       
    
    public function notCartProductsFilter($notAttr){  
      if(empty($notAttr)) return true;    
      if(Mage::helper('magebird_popup')->getPopupCookie('magentoSessionId')==$_COOKIE['frontend']){
        $ids = Mage::helper('magebird_popup')->getPopupCookie('cartProductIds');
        $ids = explode(",",$ids);   
        foreach($ids as $productId){  
          if($this->checkAttributes($productId,$notAttr)){
            return false;
          } 
        } 
      } 
      return true; //no product in cart  
    }     
    
    public function addProductAttrFilter($productAttribute,$productId){                              
      if($productAttribute){                                                         
        if(!$this->checkAttributes($productId,$productAttribute)){
          return false;
        }                   
      }
      return true;
    }  

    public function addNVisitorFilter(){
        $nVisitor = intval(Mage::helper('magebird_popup')->getPopupCookie('nV'));
        $nVisitor = substr($nVisitor,-1);          
    		$nVisitorTable = Mage::getSingleton('core/resource')->getTableName('magebird_popup_n_visitor');
        $this->getSelect()->joinLeft(
                        array('nvisitor' => $nVisitorTable),
                        'main_table.popup_id = nvisitor.popup_id',
                        array()
                )
                ->where("nvisitor.every_n_visitor IS NULL OR nvisitor.every_n_visitor = $nVisitor");         
        return $this; 
    }  
                    
    
    public function getProduct2($productId=null)
    {      
        if(isset($this->_product[$productId])) return $this->_product[$productId];
        //if user want to show product information outside product page, he needs to append popupProductId into url
        if(Mage::app()->getRequest()->getParam('url') && strpos(Mage::app()->getRequest()->getParam('url'), 'popupProductId')!==false){
          $url = Mage::app()->getRequest()->getParam('url');
          $query_str = parse_url($url, PHP_URL_QUERY);
          parse_str($query_str, $query_params);
          $productId = $query_params['popupProductId'];
        }elseif(!$productId){
          if($this->getTargetPageId()==2){
            $productId = Mage::helper('magebird_popup')->getFilterId();
          }
        } 
        if(!$productId) return false;
        $this->_product[$productId] = Mage::getModel('catalog/product')->load($productId);  
        return $this->_product[$productId];
    } 
    
    public function getProductCategories($productId){
      $tableName = Mage::getSingleton("core/resource")->getTableName('catalog_category_product');
      $read = Mage::getSingleton('core/resource')->getConnection('core_read');  
      $query = "SELECT DISTINCT category_id FROM ".$tableName." WHERE product_id = ".intval($productId);
      $results = $read->fetchCol($query);
      return $results;  
    }      
         
}