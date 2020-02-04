<?php
require_once(Mage::getBaseDir('lib') . '/magebird/popup/MaxMind/Db/Reader2.php');
require_once(Mage::getBaseDir('lib') . '/magebird/popup/MaxMind/Db/Reader/Decoder2.php');
require_once(Mage::getBaseDir('lib') . '/magebird/popup/MaxMind/Db/Reader/InvalidDatabaseException2.php');
require_once(Mage::getBaseDir('lib') . '/magebird/popup/MaxMind/Db/Reader/Metadata2.php');
require_once(Mage::getBaseDir('lib') . '/magebird/popup/MaxMind/Db/Reader/Util2.php'); 
class Magebird_Popup_Block_Adminhtml_Renderer_Country extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
  public function render(Varien_Object $row) {      
    $reader = new Reader2(Mage::getBaseDir('lib') . '/magebird/popup/MaxMind/GeoLite2-Country.mmdb' );
    $ipData = $reader->get($row->getUserIp());                    
    return Mage::helper('magebird_popup')->__($ipData['country']['names']['en']);
  }
}