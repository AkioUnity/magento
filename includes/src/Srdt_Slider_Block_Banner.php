<?php
class Srdt_Slider_Block_Banner extends Mage_Core_Block_Template
{
public function getBanners()
{
// 	echo "inside function";
// exit;
$collection=Mage::getModel('slider/slider')->getCollection()->addFieldToFilter('banner_status',1);
// echo "inside function";
// exit;
return $collection;
}
public function getVideoId($youtube_url)
{

$url = $youtube_url;
// Extracts the YouTube ID from various URL structures

	if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
    	$id = $match[1];
    	return $id;
	}
	else
		return false;

}




}  




