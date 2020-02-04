<?php
  /******************************************************************************************************************
   * Source: http://www.emvee-solutions.com/blog/magento-custom-reindex-script/
   * 
   * Custom Magento re-indexing script
   ******************************************************************************************************************/


  //Place this file in your Magento root folder, or modify the require once to match your directory.
   
  //increase execution time to 900seconds for larger stores
  ini_set('max_execution_time', 900);
   
  //require Magento
  require_once 'app/Mage.php';
  $app = Mage::app('admin');
  umask(0);
   
  //set error reporting
  error_reporting(E_ALL & ~E_NOTICE);
  Mage::setIsDeveloperMode(true);
   
  //array of indexes - You probably won`t use them all, modify this for your needs
  $indexcodes = array("cataloginventory_stock","catalogsearch_fulltext","catalog_category_flat","catalog_category_product",
  "catalog_product_attribute","catalog_product_attribute","catalog_product_flat","catalog_product_price",
  "catalog_url","groupscatalog2_category","groupscatalog2_product","tag_summary");
   
  //reindex
  foreach ($indexcodes as $index) {
     print 'Reindex  ' . $index;
     try {
        $process = Mage::getModel('index/indexer')->getProcessByCode($index);
        $process->reindexAll();
     } catch(Exception $e) {
        echo 'Oops, error while reindexing index' . $index . '<br/>';
        print($e->getMessage());
     }
  }