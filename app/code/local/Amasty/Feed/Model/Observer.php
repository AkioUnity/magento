<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */  
class Amasty_Feed_Model_Observer
{
    /**
     * Run process generate product feeds
     *
     * @return Amasty_Feed_Model_Observer
     */
    public function process()
    {
        // set memory limit (Mb)
        //ini_set('memory_limit', Mage::getStoreConfig('amfeed/system/max_memory') . 'M');
        set_time_limit(60*60);
//        $message = '';
        $feeds = Mage::getModel('amfeed/profile')->getCollection();

        foreach ($feeds as $currentFeed) {
            
            if ($currentFeed->getMode() && ((Amasty_Feed_Model_Profile::STATE_READY == $currentFeed->getStatus()) || (Amasty_Feed_Model_Profile::STATE_ERROR == $currentFeed->getStatus())) &&
                ($this->_onSchedule($currentFeed))) {
                
                $isCompleted = false;
                
                while (!$isCompleted) {
                    try {
                        $feed = Mage::getModel('amfeed/profile')->load($currentFeed->getId());
                        $hasGenerated = $feed->generate();
                        $total = $feed->getInfoTotal();
                        
                        if (!$total) {
//                            $message = Mage::helper('amfeed')->__('There are no products to export for feed `%s`', $feed->getTitle());
                            $isCompleted = true;
                        } elseif ($hasGenerated) {
                            $feed->sendTo();
//                            $message = Mage::helper('amfeed')->__('The `%s` feed has been generated.', $feed->getTitle());
                            $isCompleted = true;
                        }
                    } catch (Exception $e) {
//                        $message = Mage::helper('amfeed')->__('The `%s` feed generation has failed: %s', $currentFeed->getTitle(), $e->getMessage());
                        $isCompleted = true;
                        $currentFeed->setStatus(Amasty_Feed_Model_Profile::STATE_ERROR);
                        $currentFeed->save();
                        Mage::logException($e, null, 'amfeed.log');
                    }
                }

                $currentFeed->compress();
//                echo $message;
            }
        }
        
        return $this;
    }

    protected function _validateTime($feed)
    {
        $validate = true;
        $cronTime = $feed->getCronTime();

        if (!empty($cronTime)){
            $currentDate = Mage::app()->getLocale()->date();
            $validate = false;
            $times = explode(",", $cronTime);
            $now = ( $currentDate->get(Zend_Date::HOUR)  * 60) +
                $currentDate->get(Zend_Date::MINUTE);

            foreach($times as $time){
                if ($now >= $time && $now < $time + 30){
                    $validate = true;
                    break;
                }
            }
        }
        return $validate;
    }

    protected function _onSchedule($feed)
    {
        $threshold = 24; // Daily
        switch ($feed->getMode()) {
            case '2': // Weekly
                $threshold = 168;
                break;
            case '3': // Monthly
                $threshold = 5040;
                break;
            case '4': // Hourly
                $threshold = 1;
                break;
        }
        if ($threshold <= (strtotime('now') - strtotime($feed->getGeneratedAt()))/3600 &&
                $this->_validateTime($feed)) {
            return true;
        }
        return false;
    }
    
    public function processConfigDataSave(Varien_Event_Observer $observer)
    {
        $configData = $observer->getEvent()->getConfigData();
        if ($configData->getPath() == 'amfeed/system/templates'){
            $fileContent = @file_get_contents($configData->getFilePath());
            if ($fileContent){
                
                $importObjects = unserialize($fileContent);
                
                if (is_array($importObjects) && count($importObjects) > 0){
                    $message = 'Following templates has been installed:';
                    
                    foreach($importObjects as $importObject){
                        $template = Mage::getModel('amfeed/template')->load($importObject['title'], 'title');
                        
                        if (!$template->getId()){
                            unset($importObject['feed_id']);
                        }
                        
                        $template->setData($importObject);
                        if ($template->save()){
                            
                            $message .= '<br/> - '.$template->getTitle();
                        }
                    }
                    Mage::getSingleton('core/session')->addSuccess($message);
                }
            }
        }
    } 
}