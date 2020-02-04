<?php

class Potato_FullPageCache_Model_Processor_Router_Default
{
    /**
     * called before generate blocks
     *
     * @return $this
     */
    public function beforeLayoutGenerateBlocks()
    {
        return $this;
    }

    /**
     * called after block generated
     *
     * @return $this
     */
    public function afterLayoutGenerateBlocks()
    {
        //init message block
        Mage::app()->getLayout()->getMessagesBlock();
        return $this;
    }

    /**
     * @return $this
     */
    public function dispatchEvents()
    {
        return $this;
    }
}