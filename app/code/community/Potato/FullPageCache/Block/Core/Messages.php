<?php

class Potato_FullPageCache_Block_Core_Messages extends Mage_Core_Block_Messages
{
    /**
     * set open and closed tags
     *
     * @return string
     */
    public function getGroupedHtml()
    {
        $html = trim(parent::getGroupedHtml());
        if (!$this->_frameOpenTag) {
            $observer = new Varien_Event_Observer();
            $observer->setBlock($this);
            Mage::getModel('po_fpc/observer')->setFrameTags($observer);
            if ($this->_frameOpenTag) {
                $html = '<'.$this->_frameOpenTag.'>'.$html.'<'.$this->_frameCloseTag.'>';
            }
        }
        return $html;
    }
}