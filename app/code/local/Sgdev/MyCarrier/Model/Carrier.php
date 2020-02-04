<?php

class Sgdev_MyCarrier_Model_Carrier extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface {

    protected $_code = 'sgdev_mycarrier';
    /**
     * Collect rates and add White glove delivery if 
     * shipping zone is inside 50miles of radius of stores.
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request) {
        $result = Mage::getModel('shipping/rate_result');   
        /* @var $result Mage_Shipping_Model_Rate_Result */
        $shipping_distance = $this->shortest_shipping_distance_of_stores($request->getDestPostcode());
        if($shipping_distance && $shipping_distance > 0 && $shipping_distance <= 50) {
            $result->append($this->_getStandardShippingRate());
        } else {
            //echo "Not Shipping in this area.";
        }
        return $result;
    }

    /**
     * Calculate Shortest shipping distance
     */
    public function shortest_shipping_distance_of_stores($shippingZip) {
        $read = Mage::getModel('core/resource')->getConnection('core_read');

        $query_1 = "SELECT * FROM `zip_30720_radius` WHERE `zip_code` = '" . $shippingZip . "'";
        $store_30720_distance_data  = $read->fetchAll($query_1);
        if(count($store_30720_distance_data) == 1) {
            $store_30720_distance = $store_30720_distance_data[0]['distance'];
        } else {
            $store_30720_distance = false;
        }

        $query_2 = "SELECT * FROM `zip_30728_radius` WHERE `zip_code` = '" . $shippingZip . "'";
        $store_30728_distance_data  = $read->fetchAll($query_2);
        if(count($store_30728_distance_data) == 1) {
            $store_30728_distance = $store_30728_distance_data[0]['distance'];
        } else {
            $store_30728_distance = false;
        }

        if($store_30720_distance && $store_30728_distance) {
            return min($store_30720_distance, $store_30728_distance); 
        } else if(!$store_30720_distance && $store_30728_distance) {
            return $store_30728_distance;
        } else if($store_30720_distance && !$store_30728_distance) {
            return $store_30720_distance;
        } else {
            return false;
        }
    }


     /**
     * Calculate Shortest shipping distance using latitude and londitude 
     */
    public function shortest_shipping_distance_of_stores_using_latlng() {
        $unit = $this->getConfigData('systemunit');
        $shippingZipCode = Mage::getSingleton('core/session')->getZipCode();
        $shipping_cords = $this->getLatLng($shippingZipCode);
        //print_r($shipping_cords);
        $store_1_cords = $this->getLatLng($this->getConfigData('store_1_zip'));
        //print_r($store_1_cords);
        if($store_1_cords) {
            $store_1_distance = $this->haversineGreatCircleDistance($store_1_cords['lat'], $store_1_cords['lng'], $shipping_cords['lat'], $shipping_cords['lng'], $unit);
        }
        $store_2_cords = $this->getLatLng($this->getConfigData('store_2_zip'));
        //print_r($store_2_cords);
        if($store_2_cords) {
            $store_2_distance = $this->haversineGreatCircleDistance($store_2_cords['lat'], $store_2_cords['lng'], $shipping_cords['lat'], $shipping_cords['lng'], $unit); 
        }
        if($store_1_cords && $store_2_cords) {
            return min($store_1_distance, $store_2_distance); 
        } else {
            return false;
        }    
    }

    /**
     * Using CURL
     * Get User's slipping location's latitude and longitude by 
     * user's zip code using GOOGLE MAP API
     * @param string $zip zip code of user's shipping address
     * @return array lat and lng array.
     */
    public function getLatLng($zip) {  
        $read = Mage::getModel('core/resource')->getConnection('core_read');
        $write = Mage::getModel('core/resource')->getConnection('core_write');
        $zip_cords_query = "SELECT * FROM `zip_lat_lang_sgdev` WHERE `zip` = '" . $zip . "'";
        $zip_cords  = $read->fetchAll($zip_cords_query);
        $zipFound = count($zip_cords);
        if( $zipFound == 0 && $this->getConfigData('google_api') != '') {
            $key = $this->getConfigData('google_api');
            $ziptolatlng = curl_init();
            curl_setopt($ziptolatlng, CURLOPT_URL, "https://maps.googleapis.com/maps/api/geocode/json?address=".urlencode($zip)."&key=".$key);
            curl_setopt($ziptolatlng, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ziptolatlng, CURLOPT_RETURNTRANSFER, true);
            $ziptolatlng_response = curl_exec($ziptolatlng);
            $result = json_decode($ziptolatlng_response, true);
            //print_r($result);
            if($result['status'] == 'OK') {
                $location = $result['results'][0]['geometry']['location'];
                $query = "INSERT INTO `zip_lat_lang_sgdev` "
                    . "(zip, lat, lng) VALUES "
                    . "('".$zip."', ".$location['lat'].", ".$location['lng'].")";
                $write->query($query);                
                return $location;
            } else {
                return false;
            }
        } else if( $zipFound == 1 ) {
            return Array (
                'lat' => $zip_cords[0]['lat'],
                'lng' => $zip_cords[0]['lng']
            );
        } else {
            return false;
        }
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
        } else if(strtoupper($unit) == 'MI') {
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
  
    protected function _getStandardShippingRate() {
        $rate = Mage::getModel('shipping/rate_result_method');
        /* @var $rate Mage_Shipping_Model_Rate_Result_Method */

        $rate->setCarrier($this->_code);
        /**
         * getConfigData(config_key) returns the configuration value for the
         * carriers/[carrier_code]/[config_key]
         */
        $rate->setCarrierTitle($this->getConfigData('title'));

        $rate->setMethod('standard');
        $rate->setMethodTitle('Standard');
        
        $rate->setPrice($this->getConfigData('shipping_cost'));
        $rate->setCost(0);

        return $rate;
    }

    protected function _getExpressShippingRate() {
        $rate = Mage::getModel('shipping/rate_result_method');
        /* @var $rate Mage_Shipping_Model_Rate_Result_Method */
        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod('express');
        $rate->setMethodTitle('Express (Next day)');
        $rate->setPrice(12.99);
        $rate->setCost(0);
        return $rate;
    }
    
    

    protected function _getFreeShippingRate()
    {
        $rate = Mage::getModel('shipping/rate_result_method');
        /* @var $rate Mage_Shipping_Model_Rate_Result_Method */
        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod('free_shipping');
        $rate->setMethodTitle('Free Shipping (3 - 5 days)');      
        $rate->setPrice(0);
        $rate->setCost(0);
        return $rate;       
    }
    
    public function getAllowedMethods() {
        return array(
            'standard' => 'Standard',
            'express' => 'Express',
            'free_shipping' => 'Free Shipping',
        );
    }

}
