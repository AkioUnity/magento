<?php

class Potato_FullPageCache_Model_Processor_Block_Session_Messages extends Potato_FullPageCache_Model_Processor_Block_Session
{
    protected $_messageStoreTypes = array(
        'core/session',
        'customer/session',
        'catalog/session',
        'checkout/session',
        'tag/session'
    );

    /**
     * @param $block
     *
     * @return mixed
     */
    public function getBlockHtml($block)
    {
        foreach ($this->_messageStoreTypes as $type) {
            $this->_addMessagesToBlock($type, Mage::app()->getLayout()->getMessagesBlock());
        }
        return parent::getBlockHtml($block);
    }

    /**
     * @param                          $messagesStorage
     * @param Mage_Core_Block_Messages $block
     *
     * @return $this
     */
    protected function _addMessagesToBlock($messagesStorage, Mage_Core_Block_Messages $block)
    {
        if ($storage = Mage::getSingleton($messagesStorage)) {
            $block->addMessages($storage->getMessages(true));
            $block->setEscapeMessageFlag($storage->getEscapeMessages(true));
        }
        return $this;
    }

    /**
     * @param $data
     * @param $index
     *
     * @return $this
     */
    public function save($data, $index)
    {
        //don't save message to cache
        $data['html'] = '';
        return parent::save($data, $index);
    }
}