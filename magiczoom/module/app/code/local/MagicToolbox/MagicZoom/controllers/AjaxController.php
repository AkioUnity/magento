<?php

class MagicToolbox_MagicZoom_AjaxController extends Mage_Core_Controller_Front_Action
{

    /**
     * Product model
     *
     * @var null|Mage_Catalog_Model_Product
     */
    protected $_productModel = null;

    /**
     * Index action
     */
    public function indexAction()
    {
        $response = new Varien_Object();
        return $this->getResponse()->setBody($response->toJson());
    }

    /**
     * Get product small image action
     */
    public function getProductSmallImageAction()
    {
        $response = new Varien_Object();
        if ($this->_initProduct()) {
            $image = $this->_productModel->getSmall_image();
            if (!$image || $image == 'no_selection') {
                return $this->getResponse()->setBody($response->toJson());
            }

            if (0 !== strpos($image, '/', 0)) {
                $image = '/' . $image;
            }
            $baseMediaPath = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();
            $imagePath = $baseMediaPath . $image;
            if (!file_exists($imagePath)) {
                return $this->getResponse()->setBody($response->toJson());
            }

            $imageSize = getimagesize($imagePath);

            $magicToolboxHelper = Mage::helper('magiczoom/settings');
            $tool = $magicToolboxHelper->loadTool('category');
            $imageHelper = Mage::helper('catalog/image');
            $createSquareImages = $tool->params->checkValue('square-images', 'Yes');
            
            $bigImage = $imageHelper->init($this->_productModel, 'small_image', $image)->__toString();
            if ($createSquareImages) {
                $bigImageSize = ($imageSize[0] > $imageSize[1]) ? $imageSize[0] : $imageSize[1];
                $bigImage = $imageHelper->watermark(null, null)->resize($bigImageSize)->__toString();
            }

            list($w, $h) = $magicToolboxHelper->magicToolboxGetSizes('thumb', $imageSize);
            $mediumImage = $imageHelper->watermark(null, null)->resize($w, $h)->__toString();

            $response->setUrls(array(
                'large-image-url' => $bigImage,
                'small-image-url' => $mediumImage,
            ));
            $response->setResult(true);
        }
        return $this->getResponse()->setBody($response->toJson());
    }

    /**
     * Initialize requested product object
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function _initProduct()
    {
        $productId = (int)$this->getRequest()->getParam('product_id');
        if (!$productId) {
            return false;
        }
        $this->_productModel = Mage::getModel('catalog/product')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($productId);
        return $this->_productModel;
    }
}
