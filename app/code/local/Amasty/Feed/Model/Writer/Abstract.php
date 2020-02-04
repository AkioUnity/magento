<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */  
class Amasty_Feed_Model_Writer_Abstract 
{
    protected $_feed;
    
    protected function _getFeed(){
        return $this->_feed;
    }
    
    function init($feed){
        $this->_feed = $feed;
        
        $exportKey = $this->_getFeed()->getExportKey();
        
        $filePath = $this->_getFeed()->getMainPath() . $exportKey;
        
        $dir = Mage::getBaseDir('media') . DS . 'amfeed' . DS . 'feeds';
        
        if (!is_writable($dir)){
            Mage::throwException($dir . " should be writable");
        }
        
        $flags = 'ab';
        
        if ($this->isFirstStep()){
            $flags = 'wb';
        }
        
        $this->fp = fopen($filePath, $flags);        
        
        if ($this->isFirstStep()){
//            fprintf($this->fp, chr(0xEF).chr(0xBB).chr(0xBF));
        }
    }
    
    function isFirstStep(){
        $exportStep = $this->_getFeed()->getExportStep();
        $exportKey = $this->_getFeed()->getExportKey();
        
        $filePath = $this->_getFeed()->getMainPath() . $exportKey;
        
        return $exportStep == 0 || !file_exists($filePath);
    }
    
    function isLastStep(){
        return count($this->_getFeed()->getResultData()) < $this->_getFeed()->getStepSize();
    }
    
    public function close()
    {
        if ($this->fp) {
            fclose($this->fp);
        }
    }
    
    function write(){
        $resultData = $this->_getFeed()->getResultData();
        if (is_array($resultData))
            foreach($resultData as $record){
                $this->writeRecord($record);
            }
    }
    
    public function writeRecord($record)
    {
//        fwrite($this->fp, $record);
    }

}