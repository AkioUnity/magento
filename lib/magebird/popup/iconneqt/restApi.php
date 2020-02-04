<?php
/*
The MIT License (MIT)

Copyright (c) 2018, Advanced CRMMail Technology B.V., Netherlands

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/
class restApi
{     
    protected $adminUrl;
    protected $password;
    protected $username;

    public function __construct($iconnectUrl, $iconnectUser, $iconnectPassword)
    {
        $this->adminUrl = $iconnectUrl;
        $this->username = $iconnectPassword;
        $this->password = $iconnectPassword;
        $this->curl     = curl_init();
    }
    
    function subscribe($email,$listId,$confirmed=1){ 
       $url = $this->adminUrl . "admin/?";
       $ch = curl_init();
       $data["login"] = $this->username;
       $data["password"] = $this->password;
       curl_setopt($ch, CURLOPT_POST, 1);
       curl_setopt($ch, CURLOPT_URL, $url);    
       curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
       curl_setopt($ch, CURLOPT_COOKIEFILE, "/tmp/emptyfile.txt");  
       $result = curl_exec($ch);
       if(strpos($result, 'login-form')!==false){
          $error = "phpList error: Wrong login";
          $status = 2;
       }elseif(!$result){
          $error = "phpList error: Please check url";
          $status = 2;
       }else{
          $post_data["email"] = $email;
          $post_data["emailconfirm"] = $email;
          $post_data["htmlemail"] = "1";
          $post_data["list[$listId]"] = "signup";
          $post_data["subscribe"] = "Subscribe";
          $post_data["makeconfirmed"] = $confirmed;
          $url = $this->adminUrl . "?p=subscribe&isPopup=1";
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_URL, $url);    
          curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          $result = curl_exec($ch);
          $error = null;
          $status = 1;                   
       }           
 
       return array('status'=>$status,'error'=>$error);
    }

}