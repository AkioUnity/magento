<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */     
class Amasty_Feed_Adminhtml_Amfeed_FilterController extends Amasty_Feed_Controller_Abstract
{
    public function newValueAction()
    {
        $attributeCode = $this->getRequest()->getParam('attribute');
        
        $attribute = Mage::getSingleton('eav/config')
                ->getAttribute('catalog_product', $attributeCode);

        $layout = $this->getLayout();
        
        $block = $layout
            ->createBlock('amfeed/adminhtml_control')
            ->initTemplate($attribute);
                
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
            'code' => $attributeCode,
            'block' => $block->toHtml()
                
        )));
        
    }
    
    public function newConditionAction()
    {
        $code = $this->getRequest()->getParam('code');
        $type = $this->getRequest()->getParam('type');
        $mode = (string)$this->getRequest()->getParam('mode');

        $layout = $this->getLayout();
        
        $html = '';
        
        if (in_array($mode, array('field', 'profile'))){
            switch ($type){
                case Amasty_Feed_Model_Filter::$_TYPE_OTHER:
                    
                    $block = $layout
                        ->createBlock('amfeed/adminhtml_control_'.$mode)
                        ->initOtherConditionTemplate($code);

                    $html = $block->toHtml();

                break;
                case Amasty_Feed_Model_Filter::$_TYPE_ATTRIBUTE:
                    $attribute = Mage::getSingleton('eav/config')
                            ->getAttribute('catalog_product', $code);

                    $block = $layout
                        ->createBlock('amfeed/adminhtml_control_'.$mode)
                        ->initTemplate($attribute);
                
                    $html = $block->toHtml();
                break;

            }
        }
        
                
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
            'code' => $code,
            'type' => $type,
            'html' => $html
                
        )));
        
    }
}