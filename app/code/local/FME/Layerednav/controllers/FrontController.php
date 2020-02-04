<?php

/**
 * FME Layered Navigation 
 * 
 * @category     FME
 * @package      FME_Layerednav 
 * @copyright    Copyright (c) 2010-2011 FME (http://www.fmeextensions.com/)
 * @author       FME (Kamran Rafiq Malik)  
 * @version      Release: 1.0.0
 * @Class        FME_Layerednav_FrontController  
 */
class FME_Layerednav_FrontController extends Mage_Core_Controller_Front_Action {
    

    public function categoryAction() {
        
        // init category
        // if($this->getRequest()->getParam('cat') !="" and Mage::getStoreConfig('layerednav/layerednav/reset_filters'))
        // {
        //     $categoryId = (int) $this->getRequest()->getParam('cat');
        // }else {
        $categoryId = (int) $this->getRequest()->getParam('id', false); 
       // }
        if (!$categoryId) {
            $this->_forward('noRoute');
            return;
        }

        $category = Mage::getModel('catalog/category')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($categoryId);
        Mage::register('current_category', $category);
      


        $this->loadLayout();
        if($this->getRequest()->getParam('cat') and $this->getRequest()->getParam('cat')!="clear")
        {
          $category1 = Mage::getModel('catalog/category')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($this->getRequest()->getParam('cat'));  
        }else {
           $category1 =$category;
        }
         // print_r($category);
        $response = array();
        $response['category']=Mage::helper('layerednav')->getcategorydate($category1);
         // print_r($response);
        $response['layer'] = $this->getLayout()->getBlock('layer')->toHtml();
        
        $response['products'] = $this->getLayout()->getBlock('root')->toHtml();
        error_reporting(-1);
        
        //echo json_last_error();
        $this->getResponse()->setBody($this->safe_json_encode($response));
        
        
    }
    function utf8ize($mixed) {
    if (is_array($mixed)) {
        foreach ($mixed as $key => $value) {
            $mixed[$key] = $this->utf8ize($value);
        }
    } else if (is_string ($mixed)) {
        return utf8_encode($mixed);
    }
    return $mixed;
}
    
    function safe_json_encode($value, $options = 0, $depth = 512){
    $encoded = json_encode($value, $options, $depth);
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            return $encoded;
        case JSON_ERROR_DEPTH:
            return 'Maximum stack depth exceeded'; // or trigger_error() or throw new Exception()
        case JSON_ERROR_STATE_MISMATCH:
            return 'Underflow or the modes mismatch'; // or trigger_error() or throw new Exception()
        case JSON_ERROR_CTRL_CHAR:
            return 'Unexpected control character found';
        case JSON_ERROR_SYNTAX:
            return 'Syntax error, malformed JSON'; // or trigger_error() or throw new Exception()
        case JSON_ERROR_UTF8:
            $clean = $this->utf8ize($value);
            return $this->safe_json_encode($clean, $options, $depth);
        default:
            return 'Unknown error'; // or trigger_error() or throw new Exception()

    }
}



    public function searchAction() {
        $this->loadLayout();
        $response = array();
        $response['layer'] = $this->getLayout()->getBlock('layer')->toHtml();
        $response['products'] = $this->getLayout()->getBlock('root')->setIsSearchMode()->toHtml();
        
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }

}
