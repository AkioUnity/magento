<?php
class Biztech_Fedex_Block_Adminhtml_Pickup_Renderer_State extends  Varien_Data_Form_Element_Abstract
{
    protected $_element;

    public function getElementHtml()
    {   
		$html = '';
		$html = $html.'<select id="pickup_state" style="display:none;" name="pickup_state" ><option value="">Please select region, state or province</option></select>'.'<script>$("pickup_state").setAttribute("defaultValue",  "43");</script>'.'<input title="State/Province" class="input-text" style="display:none;" id="pickup_state_text" name="pickup_state_text">'.'<script>var billingRegionUpdater = new RegionUpdater("pickup_country", "pickup_state_text", "pickup_state", '.Mage::helper("directory")->getRegionJson().', undefined, "zipcode");</script>';
		return $html;
    }
}