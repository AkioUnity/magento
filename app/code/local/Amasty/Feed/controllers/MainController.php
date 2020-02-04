<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */
class Amasty_Feed_MainController extends Mage_Core_Controller_Front_Action
{
    /**
     * exclude file extension from name, and lookup profile
     */
    public function downloadAction()
    {
        $fileName = $this->_sanitizeFileName($this->getRequest()->getParam('file'));
        try
        {
            $file = str_replace(array('.csv', '.xml', '.txt'), '', $fileName);
            $this->_download($file);
        } catch (Exception $e) {
            try
            {
                //compatibility with primary generated file names, versions before  3.3.4 - July 24, 2016
                $filePath = Mage::helper('amfeed')->getDownloadPath('feeds', $fileName);
                $this->_prepareDownloadResponse($fileName, array(
                    'value' => $filePath,
                    'type' => 'filename'
                ));
            } catch (Exception $e) {
                Mage::logException($e, null, 'amfeed.log');
                $this->_forward('noRoute');
            }
        }
    }

    /**
     * lookup profile by filename and download
     */
    public function getAction()
    {
        try
        {
            $fileName = $this->getRequest()->getParam('file');
            $this->_download($fileName);
        } catch (Exception $e) {
            $this->_forward('noRoute');
        }
    }

    /**
     * @param $fileName
     * @throws Mage_Core_Exception
     */
    protected function _download($fileName)
    {
        $profile = Mage::getModel('amfeed/profile')->load($fileName, 'filename');

        if ($profile->getId()){
            $this->_prepareDownloadResponse(
                $profile->getResponseFilename(),
                array(
                    'value' => Mage::helper('amfeed')->getDownloadPath('feeds', $profile->getOutputFilename()),
                    'type' => 'filename'
                ));
        } else {
            Mage::throwException("Profile not found");
        }
    }

    /**
     * @param $filename
     * @return mixed
     */
    protected static function _sanitizeFileName($filename)
    {
        $chars = array(" ", '"', "'", "&", "/", "\\", "?", "#");

        // every forbidden character is replace by an underscore
        return str_replace($chars, '_', $filename);
    }
}