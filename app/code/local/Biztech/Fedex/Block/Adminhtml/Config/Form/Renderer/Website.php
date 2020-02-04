<?php
    class Biztech_Fedex_Block_Adminhtml_Config_Form_Renderer_Website extends Mage_Adminhtml_Block_System_Config_Form_Field
    {

        protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
        {
            $html      = '';
            $data      = Mage::getStoreConfig('fedex/activation/data');
            $ele_value = explode(',', str_replace($data, '', Mage::helper('core')->decrypt($element->getValue())));
            $ele_name  = $element->getName();
            $ele_id    = $element->getId();
            $element->setName($ele_name . '[]');
            $data_info = Mage::helper('fedex')->getDataInfo();
            //if(isset($data_info['dom']) &&  intval($data_info['c']) > 0  &&  intval($data_info['suc']) == 1){
            if(isset($data_info->dom) &&  intval($data_info->c) > 0  &&  intval($data_info->suc) == 1){
                foreach (Mage::app()->getWebsites() as $website) {
                    $url = $website->getConfig('web/unsecure/base_url');
                    $url = Mage::helper('fedex')->getFormatUrl(trim(preg_replace('/^.*?\/\/(.*)?\//', '$1', $url)));
                    //foreach($data_info['dom'] as $web){
                    foreach($data_info->dom as $web){
                        if($web->dom == $url && $web->suc == 1)    {
                            $element->setChecked(false);
                            $id = $website->getId();

                            $name = $website->getName();
                            $element->setId($ele_id.'_'.$id);
                            $element->setValue($id);
                            if(in_array($id, $ele_value) !== false){
                                $element->setChecked(true);
                            }
                            if ($id!=0) {
                                $html .= '<div><label>'.$element->getElementHtml().' '.$name.' </label></div>';
                            }
                        }
                    }
                }
            }else{
                $html = sprintf('<strong class="required">%s</strong>', $this->__('Please enter a valid key'));
            }
            return $html;
        }
}