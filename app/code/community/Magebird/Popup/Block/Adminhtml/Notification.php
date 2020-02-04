<?php
/**
 * Magebird.com
 *
 * @category   Magebird
 * @package    Magebird_Popup
 * @copyright  Copyright (c) 2014 Magebird (http://www.Magebird.com)
 * @license    http://www.magebird.com/licence
 * Any form of ditribution, sell, transfer forbidden see licence above 
 */
class Magebird_Popup_Block_Adminhtml_Notification extends Mage_Adminhtml_Block_Template
{
    //Required for important critical bugfixes
    public function getNotifications(){  
      $tableName = Mage::getSingleton("core/resource")->getTableName('magebird_notifications');
		  $table = (boolean) Mage::getSingleton('core/resource')->getConnection('core_write')->showTableStatus(trim($tableName,'`'));
      if(!$table) return null;
            
      $configModel = Mage::getModel('core/config_data');    
      $lastCheck = $configModel->load('magebird/notifications/last_check', 'path')->getData('value');      
      if(!$lastCheck || $lastCheck<strtotime('-14 days')){        
        $this->requestNotifications($lastCheck);
        Mage::getModel('core/config')->saveConfig('magebird/notifications/last_check', time());
      }
      
      $tableName = Mage::getSingleton("core/resource")->getTableName('magebird_notifications');
      $query = "SELECT * FROM $tableName WHERE dismissed <> 1;";
      $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
      $results = $connection->fetchAll($query);
      return $results;
            
      
    }
    
    protected function requestNotifications($lastCheck){
      $data=http_build_query(array("extension"=>"popup","lastCheck"=>$lastCheck));      
      if(function_exists('curl_version')){
        $ch = @curl_init();  
        @curl_setopt($ch, CURLOPT_URL, "https://www.magebird.com/notifications/check.php?".$data); 
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        $resp = @curl_exec($ch); 
        @curl_close($ch);               
      } 
          
      if($resp==null){
        $headers  = "Content-type: application/x-www-form-urlencoded\r\nContent-Length: ".strlen($data)."\r\n";
        $options = array("http" => array("method"=>"POST","header"=>$headers,"content"=>$data));
        $context = stream_context_create($options); 
        $resp=@file_get_contents("https://www.magebird.com/notifications/check.php",false,$context,0,10000);              
      } 
      
      
      $notifs = json_decode($resp);
      if(!$notifs) return;
      foreach($notifs as $notif){
        $values[]= "(?,?,?)";
        $binds[]= $notif->origin_id;
        $binds[]= $notif->is_critical;
        $binds[]= $notif->notification; 
      }
      
      $tableName = Mage::getSingleton("core/resource")->getTableName('magebird_notifications');      
      $sql = "INSERT IGNORE INTO $tableName (origin_id,is_critical,notification) VALUES ".implode(",",$values).";";
      $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
      $connection->query($sql,$binds);                            
    }
      
}