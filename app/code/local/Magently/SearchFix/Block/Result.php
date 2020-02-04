<?php

class Magently_SearchFix_Block_Result extends Mage_CatalogSearch_Block_Result {
    public function setListCollection()
    {
        $this->getListBlock()
        ->setCollection($this->_getProductCollection());
       return $this;
    }

    protected function _getProductCollection()
    {
        if (is_null($this->_productCollection)) {
            $this->_productCollection = Mage::getSingleton('catalogsearch/layer')->getProductCollection();
        }

        return $this->_productCollection;
    }
}