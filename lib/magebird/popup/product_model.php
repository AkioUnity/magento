<?php
class product_model{

  public function getProductSql($productId,$storeId,$prefix){  
      $storeId = intval($storeId);
      $productId = intval($productId);
      $sql = "
      SELECT * FROM (
            SELECT
                ce.sku,
                ea.attribute_id,
                ea.attribute_code,
                ea.default_value,
                CASE ea.backend_type
                   WHEN 'varchar' THEN ce_varchar.value
                   WHEN 'int' THEN ce_int.value
                   WHEN 'text' THEN ce_text.value
                   WHEN 'decimal' THEN ce_decimal.value
                   WHEN 'datetime' THEN ce_datetime.value
                   ELSE ea.backend_type
                END AS value,
                ea.is_required AS required,
                csi.qty as stockQty,
                EAOV.value AS option_value
            FROM ".$prefix."catalog_product_entity AS ce
            LEFT JOIN ".$prefix."eav_attribute AS ea
                ON ce.entity_type_id = ea.entity_type_id
            LEFT JOIN ".$prefix."catalog_product_entity_varchar AS ce_varchar
                ON ce.entity_id = ce_varchar.entity_id
                AND ea.attribute_id = ce_varchar.attribute_id
                AND ea.backend_type = 'varchar'
                AND ce_varchar.store_id=$storeId
            LEFT JOIN ".$prefix."catalog_product_entity_int AS ce_int
                ON ce.entity_id = ce_int.entity_id
                AND ea.attribute_id = ce_int.attribute_id
                AND ea.backend_type = 'int'
                AND ce_int.store_id=$storeId
            LEFT JOIN ".$prefix."catalog_product_entity_text AS ce_text
                ON ce.entity_id = ce_text.entity_id
                AND ea.attribute_id = ce_text.attribute_id
                AND ea.backend_type = 'text'
                AND ce_text.store_id=$storeId
            LEFT JOIN ".$prefix."catalog_product_entity_decimal AS ce_decimal
                ON ce.entity_id = ce_decimal.entity_id
                AND ea.attribute_id = ce_decimal.attribute_id
                AND ea.backend_type = 'decimal'
                AND ce_decimal.store_id=$storeId
            LEFT JOIN ".$prefix."catalog_product_entity_datetime AS ce_datetime
                ON ce.entity_id = ce_datetime.entity_id
                AND ea.attribute_id = ce_datetime.attribute_id
                AND ea.backend_type = 'datetime' 
                AND ce_datetime.store_id=$storeId
            LEFT JOIN ".$prefix."eav_attribute_option EAO ON EAO.attribute_id = ea.attribute_id AND ce_int.value=EAO.option_id
            LEFT JOIN ".$prefix."eav_attribute_option_value EAOV ON EAOV.option_id = EAO.option_id AND EAOV.store_id=0
            LEFT JOIN ".$prefix."cataloginventory_stock_item csi ON csi.product_id=ce.entity_id
            WHERE ce.entity_id = $productId 
          ) AS tab";   
          return $sql;
  }
  public function getProduct($productId,$storeId,$pdo,$prefix,$helper){              
    if(!$productId) return false;
    if(isset($this->products[$productId])) return $this->products[$productId];
    
    $sql = $this->getProductSql($productId,$storeId,$prefix);      
    $results = $pdo->query($sql);
    $results->setFetchMode(PDO::FETCH_ASSOC);    
    $product = array();   
    
    foreach ($results as $key => $row) {                          
      if($row['attribute_code']=='small_image' || $row['attribute_code']=='thumbnail'){
        $product[$row['attribute_id']]['value'] = $helper->getParam('baseUrl')."/media/catalog/product/".$row['value'];      
      }elseif($row['attribute_code']=='sku'){  
        $product[$row['attribute_id']]['value'] = $row['sku'];
      }elseif($row['option_value']){                
        $product[$row['attribute_id']]['value'] = $row['option_value'];          
      }else{
        //skip this line, otherwise value will never be null and it will never 
        //take value from default store scope
        //if(is_null($row['value'])) $row['value'] = $row['default_value'];
        $product[$row['attribute_id']]['value'] = $row['value'];
      }        
      $product[$row['attribute_id']]['option_value'] = $row['option_value'];
      $product[$row['attribute_id']]['attribute_code'] = $row['attribute_code'];
      if($key==0){
        $stock = $row['stockQty'];
      }
    }
    
    
    if($storeId!=0){        
      $sql2 = $this->getProductSql($productId,0,$prefix);   
      $results = $pdo->query($sql2);
      $results->setFetchMode(PDO::FETCH_ASSOC);    
      //var_dump(count($results));
      foreach ($results as $key => $row) {  
        if($row['attribute_code']=='small_image' || $row['attribute_code']=='thumbnail'){
          $product[$row['attribute_id']]['value'] = $helper->getParam('baseUrl')."/media/catalog/product/".$row['value'];      
        }elseif($row['attribute_code']=='sku'){
          $product[$row['attribute_id']]['value'] = $row['sku'];      
        }elseif($product[$row['attribute_id']]['value']==null){ //if not null value is taken from local store scope
          if($row['attribute_code']=='sku'){
            $product[$row['attribute_id']]['value'] = $row['sku'];
          }elseif($row['option_value']){
            $product[$row['attribute_id']]['value'] = $row['option_value'];
          }else{    
            if(is_null($row['value']) && isset($row['default_value'])){
              $row['value'] = $row['default_value'];
            }          
            $product[$row['attribute_id']]['value'] = $row['value'];
          }      
          $product[$row['attribute_id']]['option_value'] = $row['option_value'];
        }                  
      } 
    }    
    
    foreach($product as $attr => $val){
      if($val['attribute_code']=="price"){
        //$val['value'] = $this->getCurrencyPrice($helper,$pdo,$prefix,$helper->getPopupCookie('currency'),$helper->getPopupCookie('csy'),$helper->getPopupCookie('cfo'),$helper->getPopupCookie('bc'),$val['value']);
      }
      $productData[$val['attribute_code']] = $val['value'];
    } 
    $productData['stock'] = $productData['qty'] = $stock;
    $productData['product_id'] = $productId;
    $productData['id'] = $productId;
    $this->products[$productId] = $productData;  
    return $productData;
  }
  
  function getCurrencyPrice($helper,$pdo,$prefix,$currency,$currencySymbol,$currencyFormat,$baseCurrency,$price){
    //basecurrency hasn't been added to cookie through observer
    if(!$baseCurrency){
      $baseCurrency = $helper->getParam('bc');
      $currency = $helper->getParam('cc');
      $currencySymbol = $helper->getParam('cs');
      $currencyFormat = $helper->getParam('cf');
      var_dump($currencySymbol);
    }
    $sql = "SELECT rate FROM ".$prefix."directory_currency_rate WHERE currency_from=:from AND currency_to=:to";
    $statement = $pdo->prepare($sql); 
    $binds = array(':from' => $baseCurrency,':to'=>$currency);
    $statement->execute($binds);
    $rate = $statement->fetch(PDO::FETCH_COLUMN, 0); 
    $price = $price*$rate;
    $price = number_format($price, 2); //number_format($price, 2, ',', ' ')
    if($currencySymbol==substr($currencyFormat,0,1)){
      return $currencySymbol.$price;
    }else{
      return $price.$currencySymbol;
    }    
  }
    
  
  function getProductCategories($productId,$storeId,$pdo,$prefix){
    if(!$productId) return false; 
    $sql = "SELECT DISTINCT category_id FROM ".$prefix."catalog_category_product WHERE product_id = ".intval($productId);
    $results = $pdo->query($sql);
    $results = $results->fetchAll(PDO::FETCH_COLUMN, 0);
    return $results;  
  }  
    
}