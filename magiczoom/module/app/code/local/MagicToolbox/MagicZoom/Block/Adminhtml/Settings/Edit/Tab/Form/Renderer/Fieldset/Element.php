<?php

class MagicToolbox_MagicZoom_Block_Adminhtml_Settings_Edit_Tab_Form_Renderer_Fieldset_Element extends Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('magiczoom/element.phtml');
    }

}
