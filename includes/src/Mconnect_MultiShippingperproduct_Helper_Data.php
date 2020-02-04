<?php
/**
 * M-Connect Solutions.
 *
 * NOTICE OF LICENSE
 *

 *
 * @category   Catalog
 * @package    Mconnect_MultiShippingperproduct
 * @author     M-Connect Solutions (http://www.mconnectsolutions.com, http://www.mconnectmedia.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mconnect_MultiShippingperproduct_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isEnabled() {
        return Mage::getStoreConfig('carriers/shippingperproduct/active');
    }
    public function isMage13() {
        $i = Mage::getVersion();
        $i = explode(".", $i);
        if ($i[1] == 3) { // Check for Magento 1.3.x.x
            return true;
        } else {
            return false;
        }
    }

    public function isMage141Plus() {
        $i = Mage::getVersion();
        $i = explode(".", $i);
        if ($i[1] == 4 && $i[2] != 0) { // Check for Magento 1.4.1.x or 1.4.2.0
            return true;
        } else if ($i[1] == 5) { // Check for Magento 1.5.x.x
                return true;
            } else {
                return false;
        }
    }
    
}