<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

class Amasty_Feed_Block_Adminhtml_Google_Edit_Tab_Content_Element
    extends Mage_Adminhtml_Block_Template implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'amasty/amfeed/google/content.phtml';

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

//    public function getContent()
//    {
//        if (!$this->_content) {
//            $this->_content = Mage::getModel('amfeed/google')->getContent();
//        }
//        return $this->_content;
//    }

    public function getFieldTypes()
    {
        $types =  array(
            Amasty_Feed_Model_Google::TYPE_ATTRIBUTE  => $this->__('Attribute'),
//            Amasty_Feed_Model_Google::TYPE_CUSTOM_FIELD  => $this->__('Custom Field'),
            Amasty_Feed_Model_Google::TYPE_IMAGE  => $this->__('Images'),
            Amasty_Feed_Model_Google::TYPE_TEXT  => $this->__('Text'),
        );

        return $types;
    }

    public function isSelectedType($element, $type)
    {
        return $element->getType() == $type;
    }

    public function isSelectedAttribute($element, $value)
    {
        return $element->getValue() == $value;
    }

    public function getAttributeValue($element)
    {
        return $element->getValue();
    }

    public function getAttributes()
    {
        $hlr = Mage::helper('amfeed/attribute');

        return array(
            'product' => array(
                'label' => $hlr->__("Product Attributes"),
                'options' => $hlr->getProductAttributes()
            ),
            'price' => array(
                'label' => $hlr->__("Price"),
                'options' => $hlr->getPriceAttributes()
            ),
            'other' => array(
                'label' => $hlr->__("Other Attributes"),
                'options' => $hlr->getCompoundAttributes()
            )
        );
    }

    public function getImages(){
        $hlr = Mage::helper('amfeed');

        $maxImages = Amasty_Feed_Model_Google::MAX_ADDITIONAL_IMAGES;

        for($i = 1; $i <= $maxImages; $i++){
            $ret['image_' . $i] = 'Image ' . $i;
        }

        return $ret;
    }

    public function getCustomFields()
    {
        $fields = Mage::getModel('amfeed/field')->getCollection();
        $options = array();
        foreach($fields as $field) {
            $options[$field->getCode()] = $field->getTitle();
        }

        return $options;
    }
}