<?php
namespace Magebird;

class Remarkety
{
    var $storeId;

    public function __construct($storeId)
    {
        $this->storeId = $storeId;
    }

    public function subscribe($email, $doubleOptin, $firstName, $lastName)
    {                          
      $url = "https://app.remarkety.com/api/v1/stores/".$this->storeId."/contacts";
      $post = '{
        "email": "'.$email.'",
        "marketingAllowed": true,
        "doubleOptin": '.$doubleOptin.'
      }';  
      
      // setup and execute the cURL command
      $request = curl_init($url);
      curl_setopt($request, CURLOPT_FRESH_CONNECT, true);
      curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($request, CURLOPT_HEADER, 0);
      curl_setopt($request, CURLOPT_POST, 1);
      curl_setopt($request, CURLOPT_POSTFIELDS, $post);
      curl_setopt($request, CURLOPT_HTTPHEADER, array('Content-type: application/json'));        
      curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($request, CURLOPT_TIMEOUT, 10);
      //curl_setopt($request, CURLOPT_FOLLOWLOCATION, 0);
      $http_code = curl_getinfo($request, CURLINFO_HTTP_CODE);
      $resp = json_decode(curl_exec($request));
      if($resp && isset($resp->status) && $resp->status=='OK'){
        $response['status'] = 1;
      }else{
        $response['status'] = 2;
        $response['error'] = "Please check if your store id is correct.";
      }
      return $response;
    }


}
