<?php
class Magebird_Popup_Block_Review
    extends Mage_Core_Block_Template
    implements Mage_Widget_Block_Interface
{
    protected function _construct()
    {
        $this->_serializer = new Varien_Object();
        $customerSession = Mage::getSingleton('customer/session');

        parent::_construct();

        $data =  Mage::getSingleton('review/session')->getFormData(true);
        $data = new Varien_Object($data);

        // add logged in customer name as nickname
        if (!$data->getNickname()) {
            $customer = $customerSession->getCustomer();
            if ($customer && $customer->getId()) {
                $data->setNickname($customer->getFirstname());
            }
        }

        $this->setAllowWriteReviewFlag($customerSession->isLoggedIn() || Mage::helper('review')->getIsGuestAllowToWrite());
        if (!$this->getAllowWriteReviewFlag) {
            $this->setLoginLink(
                Mage::getUrl('customer/account/login/', array(
                    Mage_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME => Mage::helper('core')->urlEncode(
                        Mage::getUrl('*/*/*', array('_current' => true)) .
                        '#review-form')
                    )
                )
            );
        }
   
        $this->setTemplate($this->getData('template'))
            ->assign('data', $data)
            ->assign('messages', Mage::getSingleton('review/session')->getMessages(true));
    }

    public function getProduct()
    {
        if($this->getRequest()->getParam('url') && strpos($this->getRequest()->getParam('url'), 'popupProductId')!==false){
          $url = $this->getRequest()->getParam('url');
          $query_str = parse_url($url, PHP_URL_QUERY);
          parse_str($query_str, $query_params);
          $productId = $query_params['popupProductId'];
        }elseif(Mage::registry('current_product')){
          $productId = $this->getRequest()->getParam('id');
        }elseif($this->getRequest()->getParam('popup_page_id')==2 && $this->getRequest()->getParam('filterId')){
          $productId = $this->getRequest()->getParam('filterId');
        }else{
          return false;
        }  
             
        $product = Mage::getModel('catalog/product')->load($productId);
        return $product;
    }

    public function getFormAction()
    {
      if(Mage::app()->getStore()->isCurrentlySecure()){
        $action = Mage::getUrl('magebird_popup/review/submit', array('_forced_secure' => true));         
      }else{
        $action = Mage::getUrl('magebird_popup/review/submit');
      }
      return $action;
    }

    public function getRatings()
    {
        $ratingCollection = Mage::getModel('rating/rating')
            ->getResourceCollection()
            ->addEntityFilter('product')
            ->setPositionOrder()
            ->addRatingPerStoreName(Mage::app()->getStore()->getId())
            ->setStoreFilter(Mage::app()->getStore()->getId())
            ->load()
            ->addOptionToItems();
        return $ratingCollection;
    }
    
    protected function brightness($colourstr, $steps)
    {
      $colourstr = str_replace('#','',$colourstr);
      $rhex = substr($colourstr,0,2);
      $ghex = substr($colourstr,2,2);
      $bhex = substr($colourstr,4,2);
    
      $r = hexdec($rhex);
      $g = hexdec($ghex);
      $b = hexdec($bhex);
    
      $r = max(0,min(255,$r + $steps));
      $g = max(0,min(255,$g + $steps));  
      $b = max(0,min(255,$b + $steps));
    
      $decheyr = dechex($r);     
        
      if(strlen($decheyr)==1) $decheyr = "0".$decheyr;
    
      $decheyg = dechex($g);
      if(strlen($decheyg)==1) $decheyg = "0".$decheyg;
    
      $decheyb = dechex($b); 
      if(strlen($decheyb)==1) $decheyb = "0".$decheyb;
          
      return '#'.$decheyr.$decheyg.$decheyb;

    }      
} 