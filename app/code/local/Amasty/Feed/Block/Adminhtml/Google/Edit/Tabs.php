<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */
class Amasty_Feed_Block_Adminhtml_Google_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('googleTabs');
        $this->setDestElementId('edit_form');
//        $this->setTitle(Mage::helper('amfeed')->__('Categories'));
    }


    protected function _beforeToHtml()
    {
        $activeTab = 'category';


//        $categoryMapper = Mage::registry(Amasty_Feed_Model_Google::VAR_CATEGORY_MAPPER);
        $step = Mage::registry(Amasty_Feed_Model_Google::VAR_STEP);

        $step1 = 'empty'; $step2 = 'empty'; $step3 = 'empty'; $step4 = 'empty';

        switch($step){
            case "1":
                $step1 = 'category';
                $activeTab = $step1;
                break;
            case "2":
                $step1 = 'category';
                $step2 = 'basic';
                $activeTab = $step2;
                break;
            case "3":
                $step1 = 'category';
                $step2 = 'basic';
                $step3 = 'optional';
                $activeTab = $step3;
                break;
            case "4":
                $step1 = 'category';
                $step2 = 'basic';
                $step3 = 'optional';
                $step4 = 'delivery';
                $activeTab = $step4;
                break;
        }

        $tabs = array(
            'step1' => array(
                'block' => $step1,
                'label' => 'Step 1: Categories'
            ),
            'step2' => array(
                'block' => $step2,
                'label' => 'Step 2: Basic Product Information'
            ),
            'step3' => array(
                'block' => $step3,
                'label' => 'Step 3: Optional Product Information'
            ),
            'step4' => array(
                'block' => $step4,
                'label' => 'Step 4: Run and Upload'
            )
        );

        foreach ($tabs as $code => $config){
            $label = $config['label'];

            $disabled = isset($disabled[$code]) ? $disabled[$code] : false;
            $block = $this->getLayout()->createBlock('amfeed/adminhtml_google_edit_tab_' . $config['block']);

            $content = $block
                ->setTitle($label)
                ->toHtml();

            $this->addTab($code, array(
                'label'     => $label,
                'content'   => $content,
                'active' => $config['block'] == $activeTab,
                'disable' => $disabled
            ));
        }

        return parent::_beforeToHtml();
    }
}