<?php
class Biztech_Fedex_Block_Calendar extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('fedex/calendar.phtml');
    }
 
    protected function _prepareLayout()
    {
        $this->_injectCalendarControlJsCSSInHTMLPageHead();
 
        return parent::_prepareLayout();        
    }    
 
    private function _injectCalendarControlJsCSSInHTMLPageHead()
    {
        $this->getLayout()->getBlock('head')->append(
            $this->getLayout()->createBlock(
                'Mage_Core_Block_Html_Calendar',
                'html_calendar',
                array('template' => 'page/js/calendar.phtml')
            )
        );
 
        $this->getLayout()->getBlock('head')
                ->addItem('js_css', 'calendar/calendar-win2k-1.css')
                ->addJs('calendar/calendar.js')
                ->addJs('calendar/calendar-setup.js');
 
        return $this;
    }
}