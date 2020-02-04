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
class Magebird_Popup_Helper_Data extends Mage_Core_Helper_Abstract
{
  protected $_product = null;
  var $popupCookie;
  
  public function __construct(){ 
    $this->popupCookie = false;  
    if(!$this->getPopupCookie('magentoSessionId') && isset($_COOKIE['frontend'])){
      $this->setPopupCookie('magentoSessionId',$_COOKIE['frontend']);
    }   
  }
    
  public function getIsCrawler()
  {  
    //if($_SERVER['REMOTE_ADDR']=='127.0.0.1') return true;
    $userAgent = $_SERVER['HTTP_USER_AGENT'];    
    $crawlers = 'robot|spider|crawler|curl|Bloglines subscriber|Dumbot|Sosoimagespider|QihooBot|FAST-WebCrawler|Superdownloads Spiderman|LinkWalker|msnbot|ASPSeek|WebAlta Crawler|Lycos|FeedFetcher-Google|Yahoo|YoudaoBot|AdsBot-Google|Googlebot|Scooter|Gigabot|Charlotte|eStyle|AcioRobot|GeonaBot|msnbot-media|Baidu|CocoCrawler|Google|Charlotte t|Yahoo! Slurp China|Sogou web spider|YodaoBot|MSRBOT|AbachoBOT|Sogou head spider|AltaVista|IDBot|Sosospider|Yahoo! Slurp|Java VM|DotBot|LiteFinder|Yeti|Rambler|Scrubby|Baiduspider|accoona';
    $isCrawler = (preg_match("/$crawlers/i", $userAgent) > 0);
    return $isCrawler;
  }	
  
	public function getTargetPageId() {  
    //if popup loaded with ajax
    if(Mage::app()->getRequest()->getParam('popup_page_id')) return Mage::app()->getRequest()->getParam('popup_page_id');      
    $request = Mage::app()->getRequest();
    $module = $request->getModuleName();
    $controller = $request->getControllerName();
    $action = $request->getActionName();
    if($action=="template" || $action=="preview") return '';
    
    $filterId = '';        
    if((Mage::getSingleton('cms/page')->getIdentifier() == 'home' && Mage::app()->getFrontController()->getRequest()->getRouteName() == 'cms')
      || Mage::getUrl('') == Mage::getUrl('*/*/*', array('_current'=>true, '_use_rewrite'=>true))){
      $targetPageId = 1;   
    }elseif(Mage::registry('current_product')){
      $targetPageId = 2;
    }elseif(Mage::registry('current_category')){
      $targetPageId = 3;
    }elseif($module == 'checkout' && $controller == 'cart' && $action == 'index'){
      $targetPageId = 5;
    }elseif($module == 'onestepcheckout' || ($module == 'checkout' && $controller == 'onepage' && $action == 'index')){
      $targetPageId = 4;
    } else{        
      $targetPageId = 7;      
    }     
    return $targetPageId;
  }
  
  public function getFilterId() {
    if(Mage::app()->getRequest()->getParam('filterId')) return Mage::app()->getRequest()->getParam('filterId');
    $filterId = null;
    if(Mage::registry('current_product')){
      $filterId = Mage::registry('current_product')->getId();
    }elseif(Mage::registry('current_category')){
      $filterId = Mage::registry('current_category')->getId();
    }  
    return $filterId; 
  }   
  
  public function getRandString(){
    return substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 7);
  }     

  public function getTrialStart(){
    $configModel = Mage::getModel('core/config_data'); //use model to prevent cache
    $trialStart = $configModel->load('magebird_popup/general/trial_start','path')->getData('value');
    return $trialStart;  
  }   
  
  public function showPopup(){
    $extensionKey = Mage::getStoreConfig('magebird_popup/general/extension_key');
    $configModel = Mage::getModel('core/config_data'); //use model to prevent cache
    $trialStart = $configModel->load('magebird_popup/general/trial_start','path')->getData('value');
    if(empty($extensionKey) && ($trialStart<strtotime('-7 days'))){
      return false;
    }     
    return true;  
  }
  
  public function getLicenceKeys(){
    //use model to prevent cache
    $collection = Mage::getModel('core/config_data')
                  ->getCollection()
                  ->addFieldToFilter('path', array('like' => '%magebird_popup/licence%'))
                  ->addFieldToFilter('value', array('neq' => ''))
                  ->getColumnValues('path'); 
    return $collection;                
  }    
    
  public function addOnActivated($addon){
    $configModel = Mage::getModel('core/config_data'); //use model to prevent cache
    $licenceKey = $configModel->load('magebird_popup/licence/'.$addon,'path')->getData('value');    
    if(!$licenceKey) return false;
    return true;  
  } 

  public function getWidgetData($content,$widgetId){
      $widgetId = 'widget_id="'.$widgetId.'"';
      $widgetArray = explode($widgetId,$content);
      $widget = end($widgetArray);
      $widget = explode('}}',$widget);
      $attribs = $widget[0];
      $pattern = '/(\\w+)\s*=\\s*("[^"]*"|\'[^\']*\'|[^"\'\\s>]*)/';
      preg_match_all($pattern, $attribs, $matches, PREG_SET_ORDER);
      $attrs = array();
      foreach ($matches as $match) {
          if (($match[2][0] == '"' || $match[2][0] == "'") && $match[2][0] == $match[2][strlen($match[2])-1]) {
              $match[2] = substr($match[2], 1, -1);
          }
          $name = strtolower($match[1]);
          $value = html_entity_decode($match[2]);
          $attrs[$name] = $value;
      }
      return $attrs;
  }  
  
  /*If we don't need also expire part then we dont return expired cookies
    when !alsoExpirePart it will return '' null value for cookie. 
    If alsoExpirePart -> getPopupCookie(cookieName, true) it means we retrieve 
    cookie even if it is expired. We need that in cases when we will replace 
    cookie value or expiration time with new one using .replace function
  */
  function getPopupCookie($param, $alsoExpirePart=false){ 
    if($this->popupCookie){
      $cookie = $this->popupCookie;
    }else{
      $cookie = isset($_COOKIE['popupData']) ? $_COOKIE['popupData'] : '';
      $this->popupCookie = $cookie;
    }            
    $param = explode($param.":",$cookie);
    if(!isset($param[1])){
      //fix for cookies from old version because they werent stored inside popupData
      if($param == 'lastSession' && isset($_COOKIE['lastPopupSession'])){
        $value = $_COOKIE['lastPopupSession'];
      }elseif($param == 'lastRandId' && isset($_COOKIE['lastRandId'])){
        $value = $_COOKIE['lastRandId'];
      }else{
        return false;
      }
    }else{
      $value = explode("|",$param[1]);
      $value = $value[0];
    }  
    if(!$alsoExpirePart){
      $value = explode("=",$value);      
      if(isset($value[1])){
        $expire = $value[1]; 
        if($expire<time()) return false;
      }                   
      $value = $value[0];
    } 
    return $value;  
  }   
  
  public function setPopupMultiCookie($cookies){
    foreach($cookies as $cookie){
      if($cookie['expired']){
        $cookie['value'] .= "=".$cookie['expired'];
      }        
          
      if($this->popupCookie){
        $_cookie = $this->popupCookie;
        $oldVal = $this->getPopupCookie($cookie['cookieName'], true); 
        if(strpos($_cookie,$cookie['cookieName'])!==false){
          $_cookie = str_replace($cookie['cookieName'].":".$oldVal, $cookie['cookieName'].":".$cookie['value'], $_cookie);      
        }else{
          $_cookie .= "|".$cookie['cookieName'].":".$cookie['value'];
        }                 
      }else{
        $_cookie = $cookie['cookieName'].":".$cookie['value'];            
      }            
      $this->popupCookie = $_cookie;    
    }
    setcookie('popupData', $this->popupCookie, time() + (3600*24*365), '/');
  }
  
  public function setPopupCookie($cookieName,$value, $expired=false){
    if($expired){
      $value .= "=".$expired;
    }        
        
    if($this->popupCookie){
      $cookie = $this->popupCookie;
      $oldVal = $this->getPopupCookie($cookieName, true); 
      if(strpos($cookie,$cookieName)!==false){
        $cookie = str_replace($cookieName.":".$oldVal, $cookieName.":".$value, $cookie);      
      }else{
        $cookie .= "|".$cookieName.":".$value;
      }                 
    }else{
      $cookie = $cookieName.":".$value;            
    }  
    setcookie('popupData', $cookie, time() + (3600*24*365), '/');
    
    $this->popupCookie = $cookie;        
  } 
  
  public function getProduct($productId=null)
  {            
      //if user want to show product information outside product page, he needs to append popupProductId into url
      if(Mage::app()->getRequest()->getParam('url') && strpos(Mage::app()->getRequest()->getParam('url'), 'popupProductId')!==false){
        $url = Mage::app()->getRequest()->getParam('url');
        $query_str = parse_url($url, PHP_URL_QUERY);
        parse_str($query_str, $query_params);
        $productId = $query_params['popupProductId'];
      }elseif(!$productId){
        if($this->getTargetPageId()==2){
          $productId = $this->getFilterId();
        }
      } 
      if(!$productId) return false;
      if(isset($this->_product[$productId])) return $this->_product[$productId];
      
      $this->_product[$productId] = Mage::getModel('catalog/product')->load($productId);  
      return $this->_product[$productId];
  } 
  
  function getBaseSubtotal(){
    if(Mage::getStoreConfig('tax/cart_display/subtotal')==2){
      $totals = Mage::helper('checkout/cart')->getQuote()->getTotals();
      $rate = Mage::helper('checkout/cart')->getQuote()->getData('base_to_quote_rate');
      if(!$rate || $rate==0) $rate=1;
      $subtotalIncTax = $totals["subtotal"]->getValue()/$rate;
      return round($subtotalIncTax,2);
    }else{
      return Mage::helper('checkout/cart')->getQuote()->getBaseSubtotal();
    }    
  }
  
  public function getIp(){
    if(isset($_SERVER["HTTP_CF_CONNECTING_IP"])){
        $ipaddress = $_SERVER["HTTP_CF_CONNECTING_IP"];
    }elseif(isset($_SERVER['HTTP_CLIENT_IP'])){
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }elseif(isset($_SERVER['HTTP_X_FORWARDED'])){
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    }elseif(isset($_SERVER['HTTP_FORWARDED_FOR'])){
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    }elseif(isset($_SERVER['HTTP_FORWARDED'])){
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    }elseif(isset($_SERVER['REMOTE_ADDR'])){
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    }else{
        $ipaddress = '';
    }       
    return $ipaddress;
  }  
          
}