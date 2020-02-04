<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


/**
 * Class Zip.php
 *
 * @author Artem Brunevski
 */
class Amasty_Feed_Model_Compressor_Zip extends Mage_Archive_Abstract implements Mage_Archive_Interface
{
    /**
     * Pack file.
     *
     * @param string $source
     * @param string $destination
     *
     * @return string
     */
    public function pack($source, $destination)
    {
        $type = 'Zip';
        if (!class_exists('\ZipArchive')) {
            throw new Mage_Exception('zip file extension is not supported');
        }

        $zip = new ZipArchive();
        $zip->open($destination, ZipArchive::CREATE);
        $zip->addFile($source, basename($source));
        $zip->close();
        return $destination;
    }

    /**
     * Unpack file.
     *
     * @param string $source
     * @param string $destination
     *
     * @return string
     */
    public function unpack($source, $destination)
    {
        $zip = new ZipArchive();
        $zip->open($source);
        $filename = $zip->getNameIndex(0);
        $zip->extractTo(dirname($destination), $filename);
        rename(dirname($destination).'/'.$filename, $destination);
        $zip->close();
        return $destination;
    }
}