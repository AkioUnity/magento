<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */     
class Amasty_Feed_Adminhtml_Amfeed_ProfileController extends Amasty_Feed_Controller_Abstract
{
    public function preDispatch()
    {
        parent::preDispatch();
        $this->_tabs      =  true;
        $this->_modelName = 'profile';
        $this->_title     = 'Feed';
        $this->_dynamic   = array('cond_advanced', 'csv');
    } 
    
    public function generateAction()
    {
        $total = 0;
        $cnt = 0;
        $message     = '';
        $isCompleted = false;
        $file        = '';
        $fileName    = '';
        
        $id = $this->getRequest()->getParam('profileId'); // feed_id
        
        $feed = Mage::getModel('amfeed/profile')->load($id);
        if ($feed->getId()) {
            try {
                $hasGenerated = $feed->generate();
                
                $cnt  = $feed->getInfoCnt();
                $total = $feed->getInfoTotal();
                    
                if ($hasGenerated) {
                    $feed->compress();
                    $message = $this->__('The feed has been generated');
                    if (($feed->getDeliveryType() == Amasty_Feed_Model_Profile::DELIVERY_TYPE_FTP || $feed->getDeliveryType() == Amasty_Feed_Model_Profile::DELIVERY_TYPE_SFTP) && $feed->getDelivered()) {
                        $message .= $this->__(' and uploaded to '.($feed->getDeliveryType() == Amasty_Feed_Model_Profile::DELIVERY_TYPE_SFTP ? 'SFTP' : "FTP").' server');
                    }
                    $message .= '.';
                    $isCompleted = true;
                    $file = $feed->getDownloadUrl();
                    $fileName = $feed->getFilename();
                } else if (!$total) {
                    $message = $this->__('There are no products to export. Pleas check the filters and try again.');
                    $isCompleted = true;   
                } elseif (!$cnt){
                    $message = $this->__('The feed generating has been started. %d products will be exported.', $total);
                } else {
                    //@todo
                    // 1) get max exec time in seconds
                    // 2) check how many seconds it takes to generate N products
                    // 3) offer to increase the batch size
                    $message = $this->__('Feed generating is in progress. %d of %d products have been exported.', $cnt, $total);    
                }
            } catch (Exception $e) {
                $message = $this->__('Please check the following error and try again:<br>%s', $e->getMessage());
                $isCompleted = true;   
                $feed->setStatus(Amasty_Feed_Model_Profile::STATE_ERROR);
                $feed->save();
            }
        } else {
            $total = 0;
            $cnt = 0;
            $file = '';
            $fileName = '';
            $isCompleted = true;
            $message = $this->__('Please provide a valid feed ID.');                
        }
        
        $progress = 0;
        if ($total) {
            $progress = 100 * $cnt / $total;
        }
        
        $result = array(
            'total'       => $total,
            'progress'    => $progress,
            'log'         => $message,
            'isCompleted' => $isCompleted,
            'filepath'    => $file,
            'filename'    => $fileName,
        );
        
        $json = Zend_Json::encode($result);
        $this->getResponse()->setBody($json);
    }
    
    public function stopAction()
    {
        $id = $this->getRequest()->getParam('profileId');
        $error = $this->getRequest()->getParam('error');
        $feed = Mage::getModel('amfeed/profile')->load($id);
        $feed->unlink();
        $feed->setStatus(Amasty_Feed_Model_Profile::STATE_READY);
        $feed->save();
    }
    
    protected function prepareForSave($model)
    {
        if (($model->getType() == Amasty_Feed_Model_Profile::TYPE_CSV) || $model->getType() == Amasty_Feed_Model_Profile::TYPE_TXT) {
            $csv = $model->getCsv();
            if (!$csv || !is_array($csv) || count($csv['name']) < 2){
                throw new Exception($this->__('Please specify fields'));
            }
            
            // the last is alwaus empty
            unset($csv['name'][count($csv['name'])-1]);
            unset($csv['attr'][count($csv['attr'])-1]);
            unset($csv['type'][count($csv['type'])-1]);
            
            // name is required
            foreach($csv['name'] as $i => $name){
                if (!$name){
                    throw new Exception($this->__('Please provide name for the field #%d', $i+1));
                }
            }
            
            $model->setCsv($csv);
        }
        else {
            $model->setCsv(array()); 
        }
        
        $cond = $model->getCondAdvanced();
        if ($cond) {
            foreach ($cond['attr'] as $i => $value){
                if (!$value){
                    unset($cond['attr'][$i]);
                }
            }
            $model->setCondAdvanced($cond);             
        }
        
        if ($model->getOnDays()) {
            $data = implode(',', $model->getOnDays());
            $model->setOnDays($data);
        }
        
        if ($model->getCondType()) {
            $data = implode(',', $model->getCondType());
            $model->setCondType($data);
        }
        
        if ($model->getCondAttributeSets()) {
            $data = implode(',', $model->getCondAttributeSets());
            $model->setCondAttributeSets($data);
        } else {
            $model->setCondAttributeSets(null);
        }
        
        if ($model->getCronTime()) {
            $data = implode(',', $model->getCronTime());
            $model->setCronTime($data);
        } else {
            $model->setCronTime(null);
        }
        
        if ($model->getDeleteImage()) {
            $path = Mage::helper('amfeed')->getDownloadPath('images', $this->getId() . '.jpg');
            Mage::helper('amfeed')->deleteFile($path);
            $model->setDefaultImage(0);
        }
        
        $advanced = Mage::app()->getRequest()->getParam('advanced', array());
        $model->setConditionSerialized(serialize($advanced));
        
        return parent::prepareForSave($model);
    }
    
    public function importAction(){
//        $ret = array();
//        $collection = Mage::getModel('amfeed/template')->getCollection();
//        foreach($collection as $item){
//            $ret[] = $item->getData();
//        }
//        file_put_contents(getcwd().'/feed.am', serialize($ret));
//        exit(1);
        
        $id = $this->getRequest()->getParam('id');
        
        $template = Mage::getModel('amfeed/template')->load($id);
        
        if ($template->getId()) {
            if ($template->import()){
                $this->_redirect('*/*/index');
            }
        }
    }

    public function massDuplicateAction(){
        $ids = $this->getRequest()->getParam($this->_modelName . 's');
        if (!is_array($ids)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amfeed')->__('Please select records'));
             $this->_redirect('*/*/');
             return;
        }

        try {
            foreach ($ids as $id) {
                $model = Mage::getModel('amfeed/' . $this->_modelName)->load($id);
                $model->duplicate();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('adminhtml')->__(
                    'Total of %d record(s) were successfully duplicated', count($ids)
                )
            );
        }
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/*/');
    }

    public function massGenerateAction(){
        $ids = $this->getRequest()->getParam($this->_modelName . 's');
        if (!is_array($ids)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amfeed')->__('Please select records'));
             $this->_redirect('*/*/');
             return;
        }

        try {
            foreach ($ids as $id) {
                $currentFeed = Mage::getModel('amfeed/' . $this->_modelName)->load($id);
                $isCompleted = false;

                while (!$isCompleted) {
                    try {
                        $feed = Mage::getModel('amfeed/profile')->load($currentFeed->getId());
                        $hasGenerated = $feed->generate();
                        $total = $feed->getInfoTotal();

                        if (!$total) {
                            $isCompleted = true;
                        } elseif ($hasGenerated) {
                            $feed->sendTo();
                            $isCompleted = true;
                        }
                    } catch (Exception $e) {
                        $error = Mage::helper('amfeed')->__('The `%s` feed generation has failed: %s', $currentFeed->getTitle(), $e->getMessage());

                        Mage::getSingleton('adminhtml/session')->addError($error);

                        $isCompleted = true;
                        $currentFeed->setStatus(Amasty_Feed_Model_Profile::STATE_ERROR);
                        $currentFeed->save();
                    }
                }

                $currentFeed->compress();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('adminhtml')->__(
                    'Total of %d record(s) were successfully generated', count($ids)
                )
            );
        }
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/*/');
    }
}
