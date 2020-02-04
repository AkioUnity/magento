<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */
class Amasty_Feed_Block_Adminhtml_Profile_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id'; 
        $this->_blockGroup = 'amfeed';
        $this->_controller = 'adminhtml_profile';
        
        $this->_removeButton('reset'); 
        
        $this->_addButton('save_and_continue', array(
                'label'     => Mage::helper('amfeed')->__('Save and Continue Edit'),
                'onclick'   => 'saveAndContinueEdit()',
                'class' => 'save'
            ), 10);

        $this->_formScripts[] = "function saveAndContinueEdit(){ editForm.submit($('edit_form').action + 'continue/edit') }";
        
        if (Mage::registry('amfeed_profile')) {
            $feed = Mage::registry('amfeed_profile');
            $id = $feed->getId();
            if ($id) {
                
                $this->_addButton('generate', array(
                    'label'     => Mage::helper('amfeed')->__('Generate'),
                    'onclick'   => 'am_feed_object.request('. $id .')',
                    'class'     => 'save',
                ), -100);


        
                if (file_exists($feed->geOutputPath())) {
                    $downloadUrl = $feed->getDownloadUrl();

                    $this->_addButton('download', array(
                        'label'     => Mage::helper('amfeed')->__('Download'),
                        'onclick'   => 'setLocation(\'' . $downloadUrl . '\')',
                        'class'     => 'save',
                    ), -200);
                }
            }
        } else {
            $model = Mage::getModel('amfeed/profile');
            Mage::register('amfeed_profile', $model);
        }
    }

    public function getHeaderText()
    {
        $model = Mage::registry('amfeed_profile');
        if ($model->getId()) {
            return Mage::helper('amfeed')->__("Edit Feed `%s`", $this->htmlEscape($model->getTitle()));
        }
        else {
            return Mage::helper('amfeed')->__('New Feed');
        }
    }

    public function getFormHtml()
    {
        $formHtml = parent::getFormHtml();

        if (Mage::app()->getRequest()->getParam('force_generate', false)
            && Mage::registry('amfeed_profile'))
        {
            $feed = Mage::registry('amfeed_profile');
            if ($feed->getId()){
                $formHtml .= '<script>
                        Event.observe(window, "load", function(){
                            am_feed_object.request('. $feed->getId() .')
                        })
                </script>';
            }
        }

        return $formHtml;
    }
    
}