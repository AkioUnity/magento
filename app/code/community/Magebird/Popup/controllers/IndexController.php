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
class Magebird_Popup_IndexController extends Mage_Core_Controller_Front_Action{
	public function IndexAction(){

	}
    
	public function showAction(){                                                               
		header('Cache-Control: private, no-cache, no-store, max-age=0, must-revalidate, proxy-revalidate');
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		//it seems previous request to magebirdpopup.php wasn't successfull, switch to magebird_popup/index/show           
		if($this->getRequest()->getParam('switchRequestType') && Mage::getStoreConfig('magebird_popup/settings/requestswitched')!=1){
			Mage::getModel('core/config')->saveConfig('magebird_popup/settings/requesttype', 3);
			Mage::getModel('core/config')->saveConfig('magebird_popup/settings/requestswitched', 1);
			Mage::app()->getCacheInstance()->cleanType('config');
		}          
		Mage::getModel('magebird_popup/popup')->addNewView();
		$block = $this->getLayout()->createBlock('magebird_popup/popup')->setTemplate('magebird/popup/popup.phtml');
		$this->getResponse()->setBody($block->toHtml());                         
	}       
    
	public function previewAction(){                             
		header('Cache-Control: private, no-cache, no-store, max-age=0, must-revalidate, proxy-revalidate');
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");       
		$this->loadLayout();     
		$this->getLayout()->getBlock("head")->setTitle($this->__("Popup Preview"));
		$this->renderLayout();
	}    
    
	public function templateAction(){    
		header('Cache-Control: private, no-cache, no-store, max-age=0, must-revalidate, proxy-revalidate');
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");       
		$this->loadLayout();        
		$this->getLayout()->getBlock("head")->setTitle($this->__("Template Preview"));
		$this->renderLayout();
	} 
    
	public function statsAction(){     
		if(Mage::helper('magebird_popup')->getIsCrawler()) return;      
		if($this->getRequest()->getParam('mousetracking')){
			Mage::helper('magebird_popup/mousetracking')->handleMousetracking();
		}
		$popupIds = array();
		if($popupId = $this->getRequest()->getParam('popupId')){
			$popupIds[$popupId] = $this->getRequest()->getParam('time');
		}
		if($popupIds2 = $this->getRequest()->getParam('popupIds')){
			$popupIds2 = json_decode($popupIds2);
			foreach($popupIds2 as $id => $time){
				$popupIds[$id] = $time;
			}        
		}              
       
		foreach($popupIds as $popupId => $time){
			$_popup = Mage::getModel('magebird_popup/popup')->load($popupId);            
			if($_popup->getData('popup_id')){
				$views = $_popup->getData('views');
				if(
					($_popup->getData('background_color')!=3 && $_popup->getData('background_color')!=4) 
					||  
					(($_popup->getData('background_color')==3 || $_popup->getData('background_color')!=4) && $_popup->getData('show_when')!=1)
				){  
					$_popup->setPopupData($popupId,'views',$views+1);   
					Mage::getModel('magebird_popup/popup')->uniqueViewStats($_popup->getData('popup_id'));      
				}          
          
				$totalTime = $_popup->getData('total_time');
				$currentViewSpent = $time;          
				if($currentViewSpent>($_popup->getData('max_count_time')*1000)){
					$currentViewSpent = $_popup->getData('max_count_time')*1000;
				}
				$_popup->setPopupData($popupId,'total_time',$totalTime+$currentViewSpent);   
				if($this->getRequest()->getParam('closed')==1){      
					$_popup->setPopupData($popupId,'popup_closed',$_popup->getData('popup_closed')+1);
				}elseif($this->getRequest()->getParam('windowClosed')==1){       
					if($_popup->getData('background_color')!=3 && $_popup->getData('background_color')!=4){
						//prever če ni to kaj fore s tem ker uporabm getter znotraj setterja
						$_popup->setPopupData($popupId,'window_closed',$_popup->getData('window_closed')+1);
						$_popup->setPopupData($popupId,'last_rand_id',$this->getRequest()->getParam('lastPageviewId'));
					} 
				}elseif($this->getRequest()->getParam('clickInside')==1){                    
					$_popup->setPopupData($popupId,'click_inside',$_popup->getData('click_inside')+1);
				}         
			}
		}
	}
    
	public function popupCartsCountAction(){
		$popupId = $this->getRequest()->getParam('popupId');
		Mage::getModel('magebird_popup/popup')->uniqueViewStats($popupId);
	}  
    
	public function aweberAppAction(){
		header('Cache-Control: private, no-cache, no-store, max-age=0, must-revalidate, proxy-revalidate');
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache"); 
		
		$this->loadLayout();  
		$this->getLayout()->getBlock("head")->setTitle($this->__("Aweber Access Tokens"));		   
		$this->renderLayout();
						
		$consumerSecret = Mage::getStoreConfig('magebird_popup/services/aweber_consumerKey');
		$consumerKey = Mage::getStoreConfig('magebird_popup/services/aweber_consumerSecret');
    $token = $this->getRequest()->getParam('token');
    $secret = $this->getRequest()->getParam('secret');       
		if($consumerSecret == "" || $consumerKey == ""){
			Mage::getSingleton('core/session')->addError($this->__("Please save AWeber consumer secret and consumer key before visiting this page."));					
		} elseif (empty($token) and empty($secret)) {
			require_once(Mage::getBaseDir('lib') . '/magebird/popup/Aweber/aweber_api/aweber_api.php');
			$aweber = new AWeberAPI($consumerKey, $consumerSecret);
		
			if(empty($_COOKIE['accessToken'])){
				$oauthToken = $this->getRequest()->getParam('oauth_token');
        if(empty($oauthToken)){
					$callbackUrl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
					list($requestToken, $requestTokenSecret) = $aweber->getRequestToken($callbackUrl);
					
					setcookie('requestTokenSecret', $requestTokenSecret);
					setcookie('callbackUrl', $callbackUrl);
					header("Location: {$aweber->getAuthorizeUrl()}");
					return;
				}
				
				$aweber->user->tokenSecret = $_COOKIE['requestTokenSecret'];
        $aweber->user->requestToken = $this->getRequest()->getParam('oauth_token');
        $aweber->user->verifier = $this->getRequest()->getParam('oauth_verifier');
				list($accessToken, $accessTokenSecret) = $aweber->getAccessToken();
				//setcookie('accessToken', $accessToken);
				//setcookie('accessTokenSecret', $accessTokenSecret);
				header('Location: '.$_COOKIE['callbackUrl']."?token=".$accessToken."&secret=".$accessTokenSecret);
				return;
			}
			
		}
	}
	
	public function aweberListsAction(){
		header('Cache-Control: private, no-cache, no-store, max-age=0, must-revalidate, proxy-revalidate');
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache"); 
		
		$this->loadLayout();  
		$this->getLayout()->getBlock("head")->setTitle($this->__("Aweber Lists"));		   
		
						
		$consumerSecret = Mage::getStoreConfig('magebird_popup/services/aweber_consumerKey');
		$consumerKey = Mage::getStoreConfig('magebird_popup/services/aweber_consumerSecret');
		$accToken = Mage::getStoreConfig('magebird_popup/services/aweber_token_key');
		$accSecret = Mage::getStoreConfig('magebird_popup/services/aweber_token_secret');
       
		if($consumerSecret == "" || $consumerKey == "" || $accToken == "" || $accSecret == ""){
			Mage::getSingleton('core/session')->addError($this->__("Please enter all AWeber API keys before visiting this page as otherwise lists are not available."));
			
		
		}else{
			require_once(Mage::getBaseDir('lib') . '/magebird/popup/Aweber/aweber_api/aweber_api.php');
			$aweber = new AWeberAPI($consumerKey, $consumerSecret);
			
			$account = $aweber->getAccount($accToken, $accSecret);
			$account_id = $account->id;
			
			$listURL ="/accounts/{$account->id}/lists/"; 
    		$lists = $account->loadFromUrl($listURL);
			$block = $this->getLayout()->getBlock('aweberLists');
			$block->setLists($lists);	
		}
		
		$this->renderLayout();
	}
}