<?php
/**
 * File: Calculate Distacne between two coordinate.
 * @SGDEV
 * v:1.0
 * Google API KEY: AIzaSyB6u4EnHX_IDFEE582mOdsPCPUM5EtX7K8
 * Pricebuster two stores zip: 30720 and zip: 30728
 */
error_reporting(E_ALL); 
ini_set('display_errors', 1);
ini_set('max_execution_time', 0);
echo "<pre>";
echo "Calculate distacne between two co-ordinate <br>";
/** 
 * Store 1 Location
 * @Address: Dalton, GA 30720, USA
 * @ZIP: 30720
 * @LAT: 34.748686
 * @LNG: -84.992155
 */
$store_location_1 = Array (
  'lat' => 34.748686,
  'lng' => -84.992155
);
/** 
 * Store 2 Location
 * @Address: LaFayette, GA 30728, USA
 * @ZIP: 30728
 * @LAT: 34.702296
 * @LNG: -85.210127
 */
$store_location_2 = Array (
  'lat' => 34.702296,
  'lng' => -85.210127
);
/**
 * User's Location
 * @Address: Cohutta, GA 30710, USA
 * @ZIP: 30728
 * @LAT: 34.954851
 * @LNG: -84.919308
 */
$shippingZip = '750341';
//$user_location = getLatLng($shippingZip);
$user_location = Array (
  'lat' => 34.954851,
  'lng' => -84.919308
);
$unit = 'km'; //Distance unit
$distance_from_store_1 = haversineGreatCircleDistance($store_location_1['lat'], $store_location_1['lng'], $user_location['lat'], $user_location['lng'], $unit);
$distance_from_store_2 = haversineGreatCircleDistance($store_location_2['lat'], $store_location_2['lng'], $user_location['lat'], $user_location['lng'], $unit);
$shortest_distance = min($distance_from_store_1,$distance_from_store_2);
if($shortest_distance <= 50) {
  if($distance_from_store_1 < $distance_from_store_2) {
    echo "Lowest shipping distance from store1 is " . $distance_from_store_1 . $unit . "<br>";
  } else {
    echo "Lowest shipping distance from store2 is " . $distance_from_store_2 . $unit . "<br>";
  }
} else {
  echo "Not Shipping in this area.";
}

/* ------------------------- /// FUNTIONS \\\ --------------------- */
/**
 * Get User's slipping location's latitude and longitude by 
 * user's zip code using GOOGLE MAP API
 * @param string $zip zip code of user's shipping address
 * @return array lat and lng array.
 */
function getLatLng($zip){
  $url = "https://maps.googleapis.com/maps/api/geocode/json?address=".urlencode($zip)."&key=AIzaSyB6u4EnHX_IDFEE582mOdsPCPUM5EtX7K8";
  $result_string = file_get_contents($url);
  echo 'result_string';
  print_r($result_string);
  $result = json_decode($result_string, true);
  return $result['results'][0]['geometry']['location'];
}
/**
 * Calculates the great-circle distance between two points, with
 * the Haversine formula.
 * @param float $latitudeFrom Latitude of start point in [deg decimal]
 * @param float $longitudeFrom Longitude of start point in [deg decimal]
 * @param float $latitudeTo Latitude of target point in [deg decimal]
 * @param float $longitudeTo Longitude of target point in [deg decimal]
 * @param string $unit Mean unit of distance [default m]
 * @return float Distance between points in [as per unit] 
 * Earth radius : km = 6371, m = 6371000, mile = 3959 
 */
function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $unit = 'm') {
  if(strtoupper($unit) == 'KM') {
    $earthRadius = 6371;
  } else if(strtoupper($unit) == 'MILE') {
    $earthRadius = 3959;
  } else {
    $earthRadius = 6371000;
  }
  // convert from degrees to radians
  $latFrom = deg2rad($latitudeFrom);
  $lonFrom = deg2rad($longitudeFrom);
  $latTo = deg2rad($latitudeTo);
  $lonTo = deg2rad($longitudeTo);    
  //Delta difference
  $latDelta = $latTo - $latFrom;
  $lonDelta = $lonTo - $lonFrom;  
  //Haversine formula
  $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
  $distance = $angle * $earthRadius;
  return round($distance, 2);
}
