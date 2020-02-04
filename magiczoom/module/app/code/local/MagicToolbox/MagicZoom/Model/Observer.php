<?php

class MagicToolbox_MagicZoom_Model_Observer
{

    /* NOTE: after get layout updates */
    public function fixLayoutUpdates($observer)
    {
        //NOTE: to prevent an override of our templates with other modules
        //NOTE: also to sort the modules layout for displaying headers in the right order

        global $isLayoutUpdatesAlreadyFixed;
        if (isset($isLayoutUpdatesAlreadyFixed)) return;
        $isLayoutUpdatesAlreadyFixed = true;

        //$xml = Mage::app()->getConfig()->getNode('frontend/layout/updates')->asNiceXml();
        //debug_log($xml);

        //NOTE: default order (without sorting)
        //Magic360
        //MagicScroll
        //MagicSlideshow
        //MagicThumb
        //MagicZoom
        //MagicZoomPlus

        //NOTE: sort order
        $modules = array(
            'magic360' => false,
            'magicthumb' => false,
            'magiczoom' => false,
            'magiczoomplus' => false,
            'magicscroll' => false,
            'magicslideshow' => false,
        );

        $pattern = '#^(?:'.implode('|', array_keys($modules)).')$#';
        foreach (Mage::app()->getConfig()->getNode('frontend/layout/updates')->children() as $key => $child) {
            if (preg_match($pattern, $key)) {
                //NOTE: remember detected modules 
                $modules[$key] = array(
                    'module' => $child->getAttribute('module'),
                    'file' => (string)$child->file,
                );
            }
        }

        //NOTE: remove node to prevent dublicate
        $path = implode(' | ', array_keys($modules));
        $elements = Mage::app()->getConfig()->getNode('frontend/layout/updates')->xpath($path);
        foreach ($elements as $element) {
            unset($element->{0});
        }

        //NOTE: add new nodes to the end
        foreach ($modules as $key => $data) {
            if (empty($data)) continue;
            $child = new Varien_Simplexml_Element("<{$key} module=\"{$data['module']}\"><file>{$data['file']}</file></{$key}>");
            Mage::app()->getConfig()->getNode('frontend/layout/updates')->appendChild($child);
        }

    }

    /* NOTE: before generate layout xml */
    public function addLayoutUpdate($observer)
    {

        global $isLayoutUpdateAlreadyAdded;
        if (isset($isLayoutUpdateAlreadyAdded)) return;
        $isLayoutUpdateAlreadyAdded = true;

        $layout = $observer->getEvent()->getLayout();
        //NOTE: modules are already sorted by order (fixLayoutUpdates)
        $pattern = '#^magic(?:thumb|360|zoom|zoomplus|scroll|slideshow)$#';
        foreach (Mage::app()->getConfig()->getNode('frontend/layout/updates')->children() as $key => $child) {
            if (preg_match($pattern, $key, $match)) {
                //NOTE: add layout update for detected module
                $xml = '
<reference name="product.info.media">
    <action method="setTemplate">
        <template helper="'.$match[0].'/settings/getBlockTemplate">
            <name>product.info.media</name>
            <template>'.$match[0].'/media.phtml</template>
        </template>
    </action>
</reference>';
                $layout->getUpdate()->addUpdate($xml);
            }
        }
    }

    public function prepareProductVideosAttribute($observer)
    {
        $productModel = $observer->getEvent()->getProduct();
        $attrCode = 'product_videos';
        $attribute = $productModel->getResource()->getAttribute($attrCode);
        if ($attribute) {
            $attribute->setFrontendInput('textarea');
        }
        $attrValue = $productModel->getData($attrCode);
        if (empty($attrValue) || !is_string($attrValue) || strpos($attrValue, 'a:') !== 0) {
            return;
        }
        $attrValue = unserialize($attrValue);
        $attrValue = array_keys($attrValue);
        $attrValue = implode("\n", $attrValue)."\n";
        $productModel->setData($attrCode, $attrValue);
    }

    public function prepareProductVideosElement($observer)
    {
        /* @var $form Varien_Data_Form */
        $form = $observer->getEvent()->getForm();//Varien_Data_Form
        $productVideos = $form->getElement('product_videos');
        if ($productVideos) {
            $productVideos->setData('onchange', 'var product_videos = $(\'advice-validate-ajax-product_videos\'); if (product_videos) {product_videos.remove()}; ');
        }
    }

    public function validateProductData($observer)
    {
        $productModel = $observer->getEvent()->getProduct();
        $attrCode = 'product_videos';
        $attribute = $productModel->getResource()->getAttribute($attrCode);
        $attrValue = $productModel->getData($attrCode);
        if (empty($attrValue) || !is_string($attrValue) || strpos($attrValue, 'a:') === 0) {
            return;
        }

        $urls = preg_split('#\n++|\s++#', $attrValue, -1, PREG_SPLIT_NO_EMPTY);
        $validateUrlPattern = '^(?:https?://)?[^\W_][\w-]*(?:\.[^\W_][\w-]*)+(?::\d+)?/.*?$';
        $invalidUrls = array();
        foreach ($urls as $_url) {
            if (!preg_match("#{$validateUrlPattern}#", $_url)) {
                $invalidUrls[] = $_url;
                continue;
            }
            $url = parse_url($_url);
            if (!$url) {
                $invalidUrls[] = $_url;
            }
        }
        if (empty($invalidUrls)) {
            foreach ($urls as $_url) {
                $url = parse_url($_url);
                $videoCode = null;
                if (preg_match('#\b(?:youtube\.com|youtu\.be)\b#', $url['host'])) {
                    if (isset($url['query']) && preg_match('#\bv=([^&]+)(?:&|$)#', $url['query'], $matches)) {
                        $videoCode = $matches[1];
                    } elseif (isset($url['path']) && preg_match('#^/(?:embed/|v/)?([^/\?]+)(?:/|\?|$)#', $url['path'], $matches)) {
                        $videoCode = $matches[1];
                    }
                } elseif (preg_match('#\b(?:www\.|player\.)?vimeo\.com\b#', $url['host'])) {
                    if (isset($url['path']) && preg_match('#/(?:channels/[^/]+/|groups/[^/]+/videos/|album/[^/]+/video/|video/|)(\d+)(?:/|\?|$)#', $url['path'], $matches)) {
                        $videoCode = $matches[1];
                    }
                }
                if (!$videoCode) {
                    $invalidUrls[] = $_url;
                }
            }
            $message = 'The value of attribute "%s" contains incorrect urls:<br \>%s<br \>Only Vimeo and Youtube video is supported!';
        } else {
            $message = 'The value of attribute "%s" contains incorrect urls:<br \>%s';
        }
        if (!empty($invalidUrls)) {
            $label = $attribute->getFrontend()->getLabel();
            $e = Mage::getModel(
                'eav/entity_attribute_exception',
                Mage::helper('eav')->__($message, $label, implode('<br \>', $invalidUrls))
            );
            $e->setAttributeCode($attrCode)->setPart(/*$part*/'backend');
            throw $e;
        }
    }

    public function prepareProductVideosAttributeForSave($observer)
    {
        $productModel = $observer->getEvent()->getProduct();
        $id = $productModel->getId();
        $id = (int)$id;//NOTE: just in case (if $id will be empty)
        $attrCode = 'product_videos';
        $attribute = $productModel->getResource()->getAttribute($attrCode);
        $attrValue = $productModel->getData($attrCode);

        if (empty($attrValue) || !is_string($attrValue) || strpos($attrValue, 'a:') === 0) {
            return;
        }

        $urls = preg_split('#\n++|\s++#', $attrValue, -1, PREG_SPLIT_NO_EMPTY);
        $attrNewValue = array();
        foreach ($urls as $key => $_url) {

            $url = parse_url($_url);
            if (!$url) {
                $attrNewValue[$_url] = array();
                continue;
            }

            $isVimeo = false;
            $videoCode = null;
            if (preg_match('#youtube\.com|youtu\.be#', $url['host'])) {
                if (isset($url['query']) && preg_match('#\bv=([^&]+)(?:&|$)#', $url['query'], $matches)) {
                    $videoCode = $matches[1];
                } elseif (isset($url['path']) && preg_match('#^/(?:embed/|v/)?([^/\?]+)(?:/|\?|$)#', $url['path'], $matches)) {
                    $videoCode = $matches[1];
                }
            } elseif (preg_match('#(?:www\.|player\.)?vimeo\.com#', $url['host'])) {
                $isVimeo = true;
                if (isset($url['path']) && preg_match('#/(?:channels/[^/]+/|groups/[^/]+/videos/|album/[^/]+/video/|video/|)(\d+)(?:/|\?|$)#', $url['path'], $matches)) {
                    $videoCode = $matches[1];
                }
            }

            if (!$videoCode) {
                $attrNewValue[$_url] = array();
                continue;
            }

            if ($isVimeo) {
                $hash = unserialize(file_get_contents('http://vimeo.com/api/v2/video/'.$videoCode.'.php'));
                $thumb = $hash[0]['thumbnail_small'];
            } else {
                $thumb = 'https://i1.ytimg.com/vi/'.$videoCode.'/1.jpg';
            }

            $attrNewValue[$_url] = array(
                'code' => $videoCode,
                'thumb' => $thumb,
                'vimeo' => $isVimeo,
                'youtube' => !$isVimeo,
            );
        }

        $productModel->setData($attrCode, serialize($attrNewValue));
    }
}
