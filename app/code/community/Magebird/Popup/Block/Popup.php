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
 
require_once(Mage::getBaseDir('lib') . '/magebird/popup/Mobile_Detect.php');
require_once(Mage::getBaseDir('lib') . '/magebird/popup/MaxMind/Db/Reader2.php');
require_once(Mage::getBaseDir('lib') . '/magebird/popup/MaxMind/Db/Reader/Decoder2.php');
require_once(Mage::getBaseDir('lib') . '/magebird/popup/MaxMind/Db/Reader/InvalidDatabaseException2.php');
require_once(Mage::getBaseDir('lib') . '/magebird/popup/MaxMind/Db/Reader/Metadata2.php');
require_once(Mage::getBaseDir('lib') . '/magebird/popup/MaxMind/Db/Reader/Util2.php'); 
 
class Magebird_Popup_Block_Popup extends Magebird_Popup_Block_PopupCustomize
{
  protected $cartProductIds    = null;
  
	public function getPopup() {          
    //sometimes Magento doesn't complete installation, in this case this code is needed
    $tableName = Mage::getSingleton("core/resource")->getTableName('magebird_popup_day');
    $table = (boolean) Mage::getSingleton('core/resource')->getConnection('core_write')->showTableStatus(trim($tableName,'`'));
    if(!$table){        
      Mage::log('Magebird magebird_popup_day table does not exist');
      return array();
    }
      
    $detect = new Mobile_Detect3;
    $deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'mobile') : 'desktop'); 
    
    if($this->getPreviewId() && $this->getRequest()->getModuleName()=="magebird_popup"){  
      $previewId = intval($this->getPreviewId());
      $collection = Mage::getModel('magebird_popup/popup')->getCollection();
      $collection->addFieldToFilter('popup_id',$previewId);
      $_popup = array($collection->getLastItem());
    }elseif($this->getTemplateId() && $this->getRequest()->getModuleName()=="magebird_popup"){
      $templateId = intval($this->getTemplateId());
      $collection = Mage::getModel('magebird_popup/template')->getCollection();
      $collection->addFieldToFilter('template_id',$templateId);    
      $_popup = $collection->getLastItem();    
    }else{ 
      $collection = Mage::getModel('magebird_popup/popup')->getCollection(); 
      $collection->getSelect()->columns('GROUP_CONCAT(page_id) AS page_ids');
      switch($deviceType){
        case 'tablet':
        $collection->addFieldToFilter('devices',array('in' => array(1,4,5,6)));
        break;
        case 'mobile':
        $collection->addFieldToFilter('devices',array('in' => array(1,3,5,7)));
        break;
        default:
        $collection->addFieldToFilter('devices',array('in' => array(6,7,2,1)));                                                
      }   
      if(!$this->checkLicence()){
        $collection->addFieldToFilter('status',4);
      }               
          
      $cookie = Mage::getSingleton('core/cookie');   
      
      
      if($lastPageviewId = Mage::helper('magebird_popup')->getPopupCookie('lastPageviewId')){ 
        Mage::helper('magebird_popup')->setPopupCookie('lastPageviewId',''); //remove cookie                    
        Mage::getModel('magebird_popup/popup')->checkIfPageRefreshed($lastPageviewId);
      }                                                       

      $phpSessionId = isset($_COOKIE["PHPSESSID"]) ? $_COOKIE["PHPSESSID"] : session_id();
      $firstPopupSession = Mage::helper('magebird_popup')->getPopupCookie('lastSession');
      
      if(!$firstPopupSession){   
        Mage::helper('magebird_popup')->setPopupCookie('lastSession',$phpSessionId);
      } 

      if($firstPopupSession && $firstPopupSession!=$phpSessionId){
        //returning visitor
        $collection->addFieldToFilter('if_returning',array('in' => array(1,2)));
      }else{
        //new visitor
        $collection->addFieldToFilter('if_returning',array('in' => array(1,3)));
      }   
       
      if(Mage::helper('magebird_popup')->getPopupCookie('resN')==false){
        $collection->addFieldToFilter('search_results',
                                       array(
                                          array('like' => ''),
                                          array('null' => true),
                                        )        
        );         
      }elseif(Mage::helper('magebird_popup')->getPopupCookie('resN')!=false){        
        $results = Mage::helper('magebird_popup')->getPopupCookie('resN');
        $collection->addFieldToFilter('search_results',
                                       array(
                                          array('gt' => $results),
                                          array('null' => true),
                                        )        
        );
        Mage::helper('magebird_popup')->setPopupCookie('resN','');
      }

              
      $numVisitedPages = intval(Mage::getSingleton('core/session')->getNumVisitedPages())+1;
      Mage::getSingleton('core/session')->setNumVisitedPages($numVisitedPages);
      $collection->addFieldToFilter(
                    'num_visited_pages',
                    array(
                      array('lteq' => $numVisitedPages),
                      array('eq' => 0),
                    )
                 );    
      $deniedIds = $this->getDeniedIds($cookie);                 
      $collection->addStoreFilter(Mage::app()->getStore())
                 ->addNowFilter()                 
                 ->addFieldToFilter('cookie_id',array('nin' => $deniedIds))
                 ->addFieldToFilter(
                    'main_table.user_ip',
                    array(
                      array('like' => '%'.Mage::helper('magebird_popup')->getIp().'%'),
                      array('like' => ''),
                    )
                 )
                 ->addFieldToFilter('product_in_cart',array('in' => array(0,$this->getProductInCart())))
                 ->addFieldToFilter(
                    'cart_subtotal_min',
                    array(
                      array('gt' => $this->getSubtotal()),
                      array('eq' => 0),
                    )
                 )
                 ->addFieldToFilter(
                    'cart_subtotal_max',
                    array(
                      array('lt' => $this->getSubtotal()),
                      array('eq' => 0),
                    )
                 )        
                 ->addFieldToFilter(
                    'cart_qty_min',
                    array(
                      array('lt' => $this->getCartQty()),
                      array('eq' => 0),
                    )
                 )
                 ->addFieldToFilter(
                    'cart_qty_max',
                    array(
                      array('gt' => $this->getCartQty()),
                      array('eq' => 0),
                    )
                 )                              
                 ->addFieldToFilter('status',1);    
      
      $pageId = $this->getTargetPageId();
      $productId = null;   
      if($pageId==2){
        $productId = $this->getFilterId();         
      }else{
        $productId = null;
      }
      
      $collection->addProductFilter($productId, $pageId);
      $collection->addCategoryFilter($this->getFilterId(),$pageId); 
      $collection->addPageFilter($pageId);
      $collection->addIpFilter();   
      $collection->addIfRefferalFilter();
      $collection->addCustomerGroupsFilter();
      $collection->addDaysFilter();
      $collection->addNVisitorFilter();
      $reader = new Reader2(Mage::getBaseDir('lib') . '/magebird/popup/MaxMind/GeoLite2-Country.mmdb' );
      $ipData = $reader->get(Mage::helper('magebird_popup')->getIp());          
      if(isset($ipData['country']) && isset($ipData['country']['iso_code'])){
        $collection->addCountryFilter($ipData['country']['iso_code']);
        $collection->addNotCountryFilter($ipData['country']['iso_code']);         
      }               
      
      $loggedIn = $this->helper('customer')->isLoggedIn();
      if($loggedIn){
        $collection->addFieldToFilter('user_login',array('in' => array(1,2)));
      }else{
        $collection->addFieldToFilter('user_login',array('in' => array(1,3)));
      } 
      
      if ($loggedIn and Mage::helper('magebird_popup')->getPopupCookie('isSubscribed') == false){
  			$collection->addFieldToFilter('user_not_subscribed',array('neq' => '1'));
  	  }
      
      if($this->getRequest()->getParam('cEnabled')=="false"){
        $collection->addFieldToFilter('cookies_enabled',array('in' => array(1,3)));
      }else{
        $collection->addFieldToFilter('cookies_enabled',array('in' => array(1,2)));
      }            
                               
      $collection->getSelect()->order('priority','asc');
      $collection->getSelect()->order('stop_further','asc');
      $collection->getSelect()->order(new Zend_Db_Expr('RAND()'));  
      $stopFurther = false;  
      $cookieIds = array();
      $checkPendingOrder = false;
      $checkPendingOrderChecked = false;       
      $productInCart = $this->getProductInCart();          
      foreach($collection as $key => $_popup){       
        //show once per session 
        if($_popup->getData('showing_frequency')==7){
            $session = Mage::getSingleton('customer/session')->getData('popupIds');
            if(!$session) $session = array();
            if(in_array($_popup->getData('cookie_id'), $session)){
              $collection->removeItemByKey($key);
              continue;         
            }else{
              array_push($session, $_popup->getData('cookie_id'));
              Mage::getSingleton('customer/session')->setData('popupIds', $session);
            }       
        }  
           
        if($stopFurther == true || in_array($_popup->getData('cookie_id'),$cookieIds)){
          $collection->removeItemByKey($key);
          continue;
        }  
        
        /*
        if(in_array($_popup->getData('cookie_id'),$cookieIds)){       
          if(array_key_exists($_popup->getData('popup_id'),Mage::getSingleton('customer/session')->getData('loadedPopups'))){          
            //clear previous popups               
            foreach($keys[$popup['cookie_id']] as $popupId => $key2){
              $collection->removeItemByKey($key2);
               $array = Mage::getSingleton('customer/session')->getData('loadedPopups');
               unset($array[1]); 
               Mage::getSingleton('customer/session')->setData('loadedPopups',$array);
            }             
          }else{
            $collection->removeItemByKey($key);
            continue;    
          }   
        } 
        $keys[$_popup->getData('cookie_id')][$_popup->getData('popup_id')] = $key;  
        */
                 
        $cookieIds[] = $_popup->getData('cookie_id'); 
        $pageIds = explode(",",$_popup->getData('page_ids'));
        $productIds = explode(",",$_popup->getData('product_ids'));
        $categoryIds = explode(",",$_popup->getData('category_ids'));        
        if(!$collection->specifiedUrlFilter($_popup->getData('specified_url')) 
            && !($pageId==1 && in_array(1, $pageIds))
            && !($pageId==4 && in_array(4, $pageIds))
            && !($pageId==5 && in_array(5, $pageIds))
            && !($pageId==7 && in_array(7, $pageIds))
            && !($pageId==2 && in_array(2, $pageIds) && (in_array(0, $productIds) || in_array($this->getFilterId(), $productIds)))
            && !($pageId==3 && in_array(3, $pageIds) && (in_array(0, $categoryIds) || in_array($this->getFilterId(), $categoryIds)))
          ){ 
          $collection->removeItemByKey($key);
          continue;             
        } 
        
        if($collection->specifiedUrlFilter($_popup->getData('specified_not_url'),true)){ 
          $collection->removeItemByKey($key);
          continue;             
        }      
        
        if(!$collection->productCatFilter($_popup->getData('product_categories'))){
          $collection->removeItemByKey($key);
          continue;   
        }                     
        
        if(!$collection->productCartAttrFilter($_popup->getData('product_cart_attr'))){       
          $collection->removeItemByKey($key);
          continue;             
        } 
                
        if(!$collection->notCartProductsFilter($_popup->getData('not_product_cart_attr'))){ 
          $collection->removeItemByKey($key);
          continue;             
        }   
        
        if(!$collection->cartProductCatFilter($_popup->getData('cart_product_categories'))){ 
          $collection->removeItemByKey($key);
          continue;             
        }          
        
        if(!$collection->addProductAttrFilter($_popup->getData('product_attribute'),$productId)){
          $collection->removeItemByKey($key);
          continue;
        }              
      
        if($_popup->getData('if_pending_order')){
          if(!$checkPendingOrderChecked){
            $checkPendingOrder = $this->checkPendingOrder();
            $checkPendingOrderChecked = true;
          }
          if(!$checkPendingOrder){
            $collection->removeItemByKey($key);
            continue;  
          }                
        }                 
                                                                     
        //for other type we set view on stats ajax call
        //for 3 and 4 we set on load to prevent too many extra ajax calls since 3 and 4 are popups 
        //with the lowest interaction rate. for 1 and 2 we need ajax call everytime because at least 1 action is made (mostly close popup) 
        if(($_popup->getData('background_color')==3 || $_popup->getData('background_color')==4) && $_popup->getData('show_when')==1){
          $this->setView($_popup);
        }
                               
        if($_popup->getData('stop_further')==1){
          $stopFurther = true;
        }                                   
      }            
    }
                                       
    return parent::getPopupCustomize($collection);
	}  
    
  public function getDeniedIds($cookie){
    $popupIds = $cookie->get('popupIds');
    //cookies created with native Magento function
    $popupIds = unserialize($popupIds); 
    $deniedIds[] = '';
    if($popupIds){
      foreach($popupIds as $popupId => $expire){
        if($expire>=time() && !in_array(strval($popupId),$deniedIds)){       
          $deniedIds[] = strval($popupId);
        }
      }       
    }
    //cookies created with native javascript
    $popupIds = $cookie->get('popup_ids');
    $popupIds = explode("|",$popupIds);      
    if($popupIds){
      foreach($popupIds as $key => $popupId){
        $explode = explode("=",$popupId);
        if(!isset($explode[1])) continue;
        $expire = $explode[1];
        $popupId = $explode[0]; 
        if($expire>=time() && !in_array(strval($popupId),$deniedIds)){       
          $deniedIds[] = strval($popupId);
        }
      }       
    }
    return $deniedIds;         
  }              
  
  public function getPreviewId(){
    return $this->getRequest()->getParam('previewId'); 
  }
  
  public function getTemplateId(){ 
    return $this->getRequest()->getParam('templateId'); 
  }  
  
  public function getSubtotal(){  
    return Mage::helper('magebird_popup')->getBaseSubtotal();
  }   
  
  public function getCartQty(){  
    return Mage::helper('checkout/cart')->getSummaryCount();
  }     
  
	public function getTargetPageId() {      
    return Mage::helper('magebird_popup')->getTargetPageId();
  }
  
  public function setView($_popup){ 
    if(!Mage::helper('magebird_popup')->getIsCrawler()){       
        Mage::getModel('magebird_popup/popup')->setPopupData($_popup->getData('popup_id'),'views',$_popup->getData('views')+1);
        Mage::getModel('magebird_popup/popup')->uniqueViewStats($_popup->getData('popup_id'));       
    }
  }   
  
  public function getFilterId() {
    return Mage::helper('magebird_popup')->getFilterId();
  }      
  
  function getProductInCart(){
    if(Mage::helper('checkout/cart')->getItemsCount()){
      return 1;
    }
    return 2;
  }   
  
  function checkPendingOrder(){
    if(Mage::helper('magebird_popup')->getPopupCookie('magentoSessionId')==$_COOKIE['frontend']){
      return Mage::helper('magebird_popup')->getPopupCookie('pendingOrder');   
    } 
    return 0;
  } 
  
  public function getPrefixedCss($css,$prefix){
      $parts = explode('}', $css); 
      foreach ($parts as &$part) {                    
          $checkPart = trim($part);
          if (empty($checkPart)) {
              continue;
          }    
          
          $prefix2 = substr(str_shuffle("dpqzsjhiunbhfcjseepudpn"), 0, 6);        
          $partDetails = explode('{',$part);
          if(substr_count($part,"{")==2){
            $mediaQuery = $partDetails[0]."{";
            $partDetails[0] = $partDetails[1];
            $mediaQueryStarted = true;
          }
          //old version had class .dialog, new has .mbdialog           
          $subParts = explode(',', $partDetails[0]);
          foreach ($subParts as &$subPart) {    
              if(trim($subPart)=="@font-face"                                               
                || strpos($subPart,".dialog ")!==false 
                || strpos($subPart," .dialog")!==false
                || strpos($subPart,".dialog#")!==false
                || strpos($subPart,".dialog.")!==false
                || strpos($subPart,"dialogBg")!==false
                || (strpos($subPart,".dialog")!==false && strlen($subPart)==7)) continue;
                                    
              if(strpos($subPart,$prefix)!==false){
                $subPart = trim($subPart);                                
              }elseif(strpos($subPart,".mbdialog")!==false){
                $subPart = str_replace(".mbdialog", $prefix, $subPart);
              }else{
                $subPart = $prefix . ' ' . trim($subPart);
              } 
          }       
      
          if(substr_count($part,"{")==2){
            $part = $mediaQuery."\n".implode(', ', $subParts)."{".$partDetails[2];
          }elseif(empty($part[0]) && $mediaQueryStarted){
            $mediaQueryStarted = false;
            $part = implode(', ', $subParts)."{".$partDetails[2]."}\n"; //finish media query
          }else{
            $part = implode(', ', $subParts)."{".$partDetails[1];
          }  
      }
                      
      $prefixedCss = implode("}\n", $parts);
      
      return $prefixedCss;   
  }        
  
  public function getHtmlAttributeVal($attrib, $tag){
		//get attribute from html tag
		$re = '/' . preg_quote($attrib) . '=([\'"])?((?(1).+?|[^\s>]+))(?(1)\1)/is';
		if (preg_match($re, $tag, $match)) {
			return urldecode($match[2]);
		}
		return false;
	}    

  public function checkLicence(){
      $extensionKey = Mage::getStoreConfig('magebird_popup/general/extension_key');
      $configModel = Mage::getModel('core/config_data'); //use model to prevent cache
      $trialStart = $configModel->load('magebird_popup/general/trial_start','path')->getData('value');
      if(empty($extensionKey) && ($trialStart<strtotime('-7 days'))){
        return false;
      }     
      return true;
  }
  
  public function parseProdAttr($content,$cartProd=false){ 
    if($cartProd){          
      $attrs = explode('{{productCartAttribute="',$content);
    }else{                  
      $attrs = explode('{{productAttribute="',$content);
    } 

    unset($attrs[0]); 
    if(count($attrs>0)){    
      if($cartProd){        
        $productIds = Mage::helper('magebird_popup')->getPopupCookie('cartProductIds');        
        if($productIds){      
          $productIds = explode(",",$productIds);  
          $firstProductId = $productIds[0];
          $_product = Mage::helper('magebird_popup')->getProduct($firstProductId);
        }                        
      }else{
        $_product = Mage::helper('magebird_popup')->getProduct();
      }
      //Mage::getModel('catalog/product')->load($productId)      
      if(!isset($_product) || !$_product) return $content;   
      foreach($attrs as $attr){
        $value = explode('"}}',$attr);
        $attrCode = $value[0];  
        if($cartProd){         
          $search = '{{productCartAttribute="'.$attrCode.'"}}';
        }else{
          $search = '{{productAttribute="'.$attrCode.'"}}';
        }            
        if($_product->getData($attrCode)){  
          if($_product->getAttributeText($attrCode)){
            $replace = $_product->getAttributeText($attrCode);
          }else{
            $replace = $_product->getData($attrCode);
          }                              
        }else{
          $replace = '';
        }  
        if($attrCode=='price'){
          $replace = number_format($replace, 2);
        }      
        if($attrCode=='image' || $attrCode=='small_image' || $attrCode=='thumbnail'){
          $replace = $_product->getImageUrl();      
        }                            
        $content = str_replace($search,$replace,$content);
      }
    } 
    
    return $content;
  }   
         
}