<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */
class Amasty_Feed_Model_Source_Time extends Varien_Object
{
    public function toOptionArray($vl = true)
    {
        $stTime = strtotime('2010-10-10');
        $times = array();
        
        for($time = 0; $time < 24 * 60; $time += 30){
            if ($vl) {
                $times[] = array(
                    'value' => $time,
                    'label' => date('g:i A', $stTime + ($time * 60))
                );
            } else {
                $times[$time] = date('g:i A', $stTime + ($time * 60));
            }
            
        }
        
        return $times;
    }
}