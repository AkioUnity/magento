<?php
class Srdt_Slider_Block_Adminhtml_Catalog_Category_Edit_Buttons 
    extends Mage_Adminhtml_Block_Catalog_Category_Edit_Form {
    public function addButtons()
    {
// echo "abhishek Dixit<pre>";
// print_r($this->getParentBlock());
// exit;

        $this->addAdditionalButton('export', array(
                        'label' => $this->helper('srdt_slider')->__('Export'),
                        'class' => 'add',
                        'onclick' => 'alert(\'It works\')' //change the action of the button to what you need
        ));
        // return $this;
    }
}