<?php

if (!defined('MagicToolboxTemplateHelperClassLoaded')) {

define('MagicToolboxTemplateHelperClassLoaded', true);

class MagicToolboxTemplateHelperClass
{

    public static $extension = 'php';
    public static $path;
    public static $options;

    static function setExtension($extension)
    {
        self::$extension = $extension;
    }

    static function setPath($path)
    {
        self::$path = $path;
    }

    static function setOptions($options)
    {
        self::$options = $options;
    }

    static function prepareMagicScrollClass()
    {
        $magicscroll = self::$options->checkValue('magicscroll', 'Yes') ? ' MagicScroll' : '';
        if (!empty($magicscroll)) {
            $additionalClasses = self::$options->getValue('scroll-extra-styles');
            if (!empty($additionalClasses)) {
                $magicscroll = $magicscroll.' '.$additionalClasses;
            }
        }
        return $magicscroll;
    }

    static function render($name, $options = null)
    {
        $main = '';
        $thumbs = array();
        $pid = '';
        $magicscrollOptions = '';
        if (func_num_args() == 1) {
            $options = $name;
            $name = self::$options->getValue('template');
        }
        extract($options);

        $items = self::$options->getValue('items');
        $items = is_numeric($items) ? (int)$items : 0;
        if (count($thumbs) > $items) {
            $magicscroll = self::prepareMagicScrollClass();
        } else {
            $magicscroll = '';
        }

        ob_start();
        require(self::$path.DIRECTORY_SEPARATOR.preg_replace('/[^a-zA-Z0-9_]/is', '-', $name).'.tpl.'.self::$extension);
        //return str_replace("\n", ' ', str_replace("\r", ' ', ob_get_clean()));
        return ob_get_clean();
    }

}
}
