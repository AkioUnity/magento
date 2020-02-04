<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


/**
 * Class Compress.php
 *
 * @author Artem Brunevski
 */
class Amasty_Feed_Model_Source_Compress
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $optionArray = array();
        $arr = $this->toArray();
        foreach($arr as $value => $label){
            $optionArray[] = array(
                'value' => $value,
                'label' => $label
            );
        }
        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $options =  array(
            Amasty_Feed_Model_Profile::COMPRESS_NONE => __('None'),
            Amasty_Feed_Model_Profile::COMPRESS_ZIP => __('Zip'),
            Amasty_Feed_Model_Profile::COMPRESS_GZ => __('Gz'),
            Amasty_Feed_Model_Profile::COMPRESS_BZ => __('Bz')
        );

        return $options;
    }
}
