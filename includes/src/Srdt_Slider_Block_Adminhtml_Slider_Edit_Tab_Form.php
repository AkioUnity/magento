<?php

class Srdt_Slider_Block_Adminhtml_Slider_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {


        protected function _prepareForm() {

        $form = new Varien_Data_Form();
        $this->setForm($form);
        $dataObj = new Varien_Object();
        if (Mage::getSingleton('adminhtml/session')->getBannerData()) {
            $data = Mage::getSingleton('adminhtml/session')->getBannerData();
            Mage::getSingleton('adminhtml/session')->setBannerData(null);
        } elseif (Mage::registry('banner_data'))
            $data = Mage::registry('banner_data')->getData();
        if (isset($data)) {
            $dataObj->addData($data);
        }
        $fieldset = $form->addFieldset('banner_form', array('legend' => Mage::helper('srdt_slider')->__('Banner information')));
        $data = $dataObj->getData();
        $inStore = $this->getRequest()->getParam('store');
		$fieldset->addField('banner_id', 'hidden', array(
            'required' => false,
            'name' => 'banner_id',

        ));
        $fieldset->addField('banner_title', 'text', array(
            'label' => Mage::helper('srdt_slider')->__('Banner Title'),
            'required' => true,
            'name' => 'banner_title',
        ));

		$fieldset->addField('banner_type', 'select', array(
            'label' => Mage::helper('srdt_slider')->__('Banner Type'),
            'required' => true,
            'class'=>'required-entry validate-select',
            'name' => 'banner_type',
			//'id' => 'my_select',
			'onchange' => 'checkBannerType(this.value)',
                  'values' => array(
                
                    array(
                    'value' => '',
                    'label' => Mage::helper('srdt_slider')->__('Select Banner Type'),
                ),
				array(
                    'value' => 2,
                    'label' => Mage::helper('srdt_slider')->__('Images'),
                ),
				array(
                    'value' => 1,
                    'label' => Mage::helper('srdt_slider')->__('Video'),
                ),
                
            ),
        ));
        if (isset($data['banner_image']) && $data['banner_image']) {
             $data['banner_image'] = 'bannerslider' . '/' .$data['banner_image'];
        }
        $fieldset->addField('banner_image', 'image', array(
            'label' => Mage::helper('srdt_slider')->__('Banner Image'),
            'required' => false,
            //'class'=>'required-entry',
            'name' => 'banner_image',
        ));
		
                $fieldset->addField('banner_caption', 'text', array(
            'label' => Mage::helper('srdt_slider')->__('Banner Caption'),
            'required' => true,
            'name' => 'banner_caption',
        ));
                          $fieldset->addField('banner_url', 'text', array(
            'label' => Mage::helper('srdt_slider')->__('Redirect Url'),
            'required' => true,
            'name' => 'banner_url',
        ));
                $fieldset->addField('youtube_url', 'text', array(
            'label' => Mage::helper('srdt_slider')->__('Youtube Url'),
            'required' => true,
            'name' => 'youtube_url',
        ));


         $fieldset->addField('banner_status', 'select', array(
            'label' => Mage::helper('srdt_slider')->__('Banner Status'),
            'required' => true,
            'class'=>'required-entry validate-select',
            'name' => 'banner_status',
                  'values' => array(

                          array(
                    'value' => 1,
                    'label' => Mage::helper('srdt_slider')->__('Enabled'),
                ),

                array(
                    'value' => 0,
                    'label' => Mage::helper('srdt_slider')->__('Disabled'),
                ),
            ),
        ));


    $form->setValues($data);

    return parent::_prepareForm();
    }



    protected function _prepareLayout() {
        // echo "forms prepare layout";
        // exit;
        $return = parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        return $return;
    }

}

echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>';
echo '<script type="text/javascript" src="'. Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'frontend/base/default/js/srdt/jquery.noconflict.js"></script>';
echo "<script type=\"text/javascript\">

		function checkBannerType(id)
        {
            if(id==1)
            {
            $(\"banner_caption\").closest(\"tr\").hide();
            $(\"banner_image\").closest(\"tr\").hide();
            $(\"banner_url\").closest(\"tr\").hide();
            $(\"banner_caption\").removeClassName('required-entry');
            $(\"banner_image\").removeClassName('required-entry');
            $(\"banner_url\").removeClassName('required-entry');
            $(\"youtube_url\").closest(\"tr\").show();
            $(\"youtube_url\").addClassName('required-entry');
            }
            else
            {
            $(\"banner_caption\").closest(\"tr\").show();
            $(\"banner_image\").closest(\"tr\").show();
            $(\"banner_url\").closest(\"tr\").show();
            $(\"banner_caption\").addClassName('required-entry');
       
            $(\"banner_url\").addClassName('required-entry');
            $(\"youtube_url\").closest(\"tr\").hide();
            $(\"youtube_url\").removeClassName('required-entry');
            }
        }
        jQuery(window).load(function() {
            var id=jQuery(\"#banner_type\").val();
            checkBannerType(id)
});
</script>";

