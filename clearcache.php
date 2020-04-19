<?php

	$mageFilename = './app/Mage.php';
	require_once $mageFilename;	
//	umask(0);
//	Mage::run();

	// clean overall cache
	Mage::app()->cleanCache();

	// clear 'refresh_catalog_rewrites':
	//Mage::getSingleton('catalog/url')->refreshRewrites();

	//  clear 'clear_images_cache':
	//Mage::getModel('catalog/product_image')->clearCache();

	//  clear 'refresh_layered_navigation':
	//Mage::getSingleton('catalogindex/indexer')->plainReindex();
?>
