<?php
class Magebird_Popup_ReviewController extends Mage_Core_Controller_Front_Action
{

    /**
     * Action list where need check enabled cookie
     *
     * @var array
     */
    protected $_cookieCheckActions = array('post');

    public function preDispatch()
    {
        parent::preDispatch();

        $allowGuest = Mage::helper('review')->getIsGuestAllowToWrite();
        if (!$this->getRequest()->isDispatched()) {
            return;
        }

        $action = $this->getRequest()->getActionName();
        if (!$allowGuest && $action == 'post' && $this->getRequest()->isPost()) {
            if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
                $this->setFlag('', self::FLAG_NO_DISPATCH, true);
                Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_current' => true)));
                Mage::getSingleton('review/session')->setFormData($this->getRequest()->getPost())
                    ->setRedirectUrl($this->_getRefererUrl());
                $this->_redirectUrl(Mage::helper('customer')->getLoginUrl());
            }
        }

        return $this;
    }



    /**
     * Submit new review action
     *
     */
    public function submitAction()
    {  
        
        $ajaxExceptions = array();    
        $data   = $this->getRequest()->getPost();
        $rating = $this->getRequest()->getParam('ratings', array());
        $productId  = (int) $this->getRequest()->getParam('productId');
        $product = Mage::getModel('catalog/product')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($productId);
        if (!$product->getId()){
          $ajaxExceptions['exceptions'][] = $this->__('Missing product.');  
        }elseif(empty($data['title']) || empty($data['nickname']) || empty($data['detail'])){
          $ajaxExceptions['exceptions'][] = $this->__('Missing review data.');
        }elseif(sizeof($rating)<1){
          $ajaxExceptions['exceptions'][] = $this->__('Missing review.');  
        }else{
            $review     = Mage::getModel('review/review')->setData($data);
            try {
                $review->setEntityId($review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE))
                    ->setEntityPkValue($product->getId())
                    ->setStatusId(Mage_Review_Model_Review::STATUS_PENDING)
                    ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->setStores(array(Mage::app()->getStore()->getId()))
                    ->save();

                foreach ($rating as $ratingId => $optionId) {
                    Mage::getModel('rating/rating')
                    ->setRatingId($ratingId)
                    ->setReviewId($review->getId())
                    ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                    ->addOptionVote($optionId, $product->getId());
                }

                $review->aggregate();
                $response = json_encode(array('success' => 'success', 'coupon' => $coupon));
                $this->getResponse()->setBody($response);                                              
                return;                
                //$session->addSuccess($this->__('Your review has been accepted for moderation.'));
            }
            catch (Exception $e) {                
                $ajaxExceptions['exceptions'][] = $this->__('Unable to post the review.');
            }
       
        }

        $response = json_encode($ajaxExceptions);
        $this->getResponse()->setBody($response);
    }

}
