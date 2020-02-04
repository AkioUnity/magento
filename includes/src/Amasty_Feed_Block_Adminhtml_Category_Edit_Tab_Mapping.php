<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

class Amasty_Feed_Block_Adminhtml_Category_Edit_Tab_Mapping
    extends Mage_Adminhtml_Block_Catalog_Category_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'amasty/amfeed/category/mapping.phtml';

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    public function getCategoriesList($node = null)
    {
        $list = array();
        $root = $this->getRoot(null, 10);
        if ($root->hasChildren()) {
            foreach($root->getChildren() as $node){
                $this->_getChildCategories($list, $node);
            }
        }

        return $list;
    }

    protected function _getChildCategories(&$list, $node, $level = 0)
    {
        $list[] = array(
            'name' => $node->getName(),
            'id' => $node->getId(),
            'level' => $level
        );

        if ($node->hasChildren()) {
            foreach ($node->getChildren() as $child) {
                $this->_getChildCategories($list, $child, $level + 1);
            }
        }
    }

    public function getHideLabel()
    {
        return $this->getElement()->getData('hideLabel') === true;
    }
}