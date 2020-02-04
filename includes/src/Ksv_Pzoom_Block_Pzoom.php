<?php
class Ksv_Pzoom_Block_Pzoom extends Mage_Catalog_Block_Product_View_Media
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getPzoom()     
     { 
        if (!$this->hasData('pzoom')) {
            $this->setData('pzoom', Mage::registry('pzoom'));
        }
        return $this->getData('pzoom');
    }
	public function getConfig($att) 
	{
		$config = Mage::getStoreConfig('pzoom');
		if (isset($config['pzoom_config']) ) {
			$value = $config['pzoom_config'][$att];
			return $value;
		} else {
			throw new Exception($att.' value not set');
		}
	}
}
