<?php

//*********************** CONFIGURATION VARIABLES	*******************
$PassKey="02446"; //This variable is used for authentication of xml file

//******************	 START OF DATABASE TABLES	***************************
define('DB_SERVER', 'localhost');
define('DB_USER', 'pricebustersdb');
define('DB_PASS', 'SsKacN9_6');
define('DB_NAME', 'pricebustersdb');
$table_prefix = "";

define('EAV_ENTITY_TYPE',  $table_prefix . "eav_entity_type");
define('EAV_ATTRIBUTE_SET',  $table_prefix . "eav_attribute_set");
define('CATALOGINVENTORY_STOCK',  $table_prefix . "cataloginventory_stock");
define('CATALOG_CATEGORY_ENTITY_VARCHAR',  $table_prefix . "catalog_category_entity_varchar");
define('CORE_WEBSITE',  $table_prefix . "core_website");
define('CORE_STORE',  $table_prefix . "core_store");
define('CATALOG_PRODUCT_ENTITY', $table_prefix . "catalog_product_entity");
define('EAV_ATTRIBUTE', $table_prefix . "eav_attribute");
define('TAX_CLASS',  $table_prefix . "tax_class");
define('CATALOGINVENTORY_STOCK_ITEM', $table_prefix . "cataloginventory_stock_item");
define('CATALOGINVENTORY_STOCK_STATUS',  $table_prefix . "cataloginventory_stock_status");
define('CATALOG_PRODUCT_ENTITY_MEDIA_GALLERY',  $table_prefix . "catalog_product_entity_media_gallery");
define('CATALOG_PRODUCT_WEBSITE',  $table_prefix . "catalog_product_website");
define('CATALOG_CATEGORY_ENTITY', $table_prefix . "catalog_category_entity");
define('CATALOG_CATEGORY_PRODUCT_INDEX',  $table_prefix . "catalog_category_product_index");
define('CATALOG_PRODUCT_ENABLED_INDEX',  $table_prefix . "catalog_product_enabled_index");
define('EAV_ATTRIBUTE_OPTION',  $table_prefix . "eav_attribute_option");
define('EAV_ATTRIBUTE_OPTION_VALUE',  $table_prefix . "eav_attribute_option_value");
define('CATALOG_PRODUCT_ENTITY_INT',  $table_prefix . "catalog_product_entity_int");
define('CATALOG_CATEGORY_PRODUCT',  $table_prefix . "catalog_category_product");
define('CATALOG_PRODUCT_ENTITY_',  $table_prefix . "catalog_product_entity_");
define('CATALOG_CATEGORY_ENTITY_',  $table_prefix . "catalog_category_entity_");
define('CATALOG_PRODUCT_ENTITY_VARCHAR',  $table_prefix . "catalog_product_entity_varchar");
//***************************** END OF DATABASE TABLES	*******************

// these are the fields that will be defaulted to the current values in the database if they are not found in the incoming file
global $cat_val;
global $website;
global $usercount;
global $userdata;
global $cat_type_id;
global $store_arr;
global $tag;
global $base_cat;
$tag = 0;
$website=1;
$usercount=0;

// making connection with the database.
$dbhandle = mysql_connect(DB_SERVER, DB_USER, DB_PASS)
or die("Unable to connect to MySQL");
$db_selected = mysql_select_db(DB_NAME,$dbhandle);	

// finds the id of the product entity in the store	
global $product_type_id;
$sql = "Select entity_type_id from " . EAV_ENTITY_TYPE . " where entity_type_code = 'catalog_product'";
$result=mysql_query($sql);
$product_type_id = mysql_result($result,0);

global $include_in_menu_attribute_id;
$sql = "Select attribute_id from " . EAV_ATTRIBUTE . " where attribute_code = 'include_in_menu'";
$result=mysql_query($sql);
$include_in_menu_attribute_id = mysql_result($result,0);
//echo $include_in_menu_attribute_id;

// finds the id of the category entity in the store	
global $cat_type_id;
$sql = "Select entity_type_id from " . EAV_ENTITY_TYPE . " where entity_type_code = 'catalog_category'";
$result=mysql_query($sql);
$cat_type_id = mysql_result($result,0);

// finds the id of the set of attribuites we will use for the product we uses set called 'Default'		
global $product_attribute_set_id;
$sql = "Select attribute_set_id from " . EAV_ATTRIBUTE_SET . " where attribute_set_name = 'Default' and entity_type_id =" . $product_type_id;
$result=mysql_query($sql);
$product_attribute_set_id = mysql_result($result,0);

// finds the id of the set of attribuites we will use for the category we uses set called 'Default'
global $catagory_attribute_set_id;
$sql = "Select attribute_set_id from " . EAV_ATTRIBUTE_SET . " where attribute_set_name = 'Default' and entity_type_id =" . $cat_type_id;
$result=mysql_query($sql);
$catagory_attribute_set_id = mysql_result($result,0);

// finds the stock id for storing the quantity of the product
global $stock_id;
$sql = "SELECT * FROM " . CATALOGINVENTORY_STOCK . " where stock_name = 'Default'";
$result=mysql_query($sql);
$stock_id = mysql_result($result,0);
//	echo "</br> stock_id :: $stock_id </br>";

// Below code fetches the website code from the URL
if(isset($_GET["website"])) {
	$web = $_GET["website"];
	//echo "<br>" . $web . "</br>" ;
} else {
	echo "Could not determine the website";
	die();
}

// Below code fetches the store code from the URL
if(isset($_GET["stores"])) {
	$stores = $_GET["stores"];
	//echo "<br>" . $stores . "</br>" ;
} else {
	echo "Could not determine the stores";
	die();
}

// Below code fetches the base category (under this category all category comes) code from the URL
if(isset($_GET["category"])) {
	$s_category = $_GET["category"];
} else {
	echo "Could not determine the category";
	die();
}

$store;
$store_str = '';

// finds id of the base category	
$sql="SELECT entity_id FROM " . CATALOG_CATEGORY_ENTITY_VARCHAR . " WHERE value = '" . $s_category . "' and attribute_id =  (SELECT attribute_id FROM " . EAV_ATTRIBUTE . " WHERE attribute_code = 'name' and entity_type_id = " . $cat_type_id . ")";
$result = mysql_query($sql);
$base_cat = mysql_result($result,0);	

// finds id of the website
$sql = "SELECT website_id FROM " . CORE_WEBSITE . " where code = '". $web . "'";
$result = mysql_query($sql);
$website = mysql_result($result,0);
unset($web);   

// find id of all the stores	
$store_arr = explode(',',$stores);
unset($store);
foreach($store_arr as $store) {
	$store_str = $store_str . "'" . $store . "',";
}

unset($store);
$store=substr($store_str,0,-1);
$sql = "SELECT code,store_id FROM `" . CORE_STORE . "` where `code` IN (" . $store . ")"; 
//echo "$sql </br>";
$result=mysql_query($sql);
$i = 0;
while($a = mysql_fetch_array($result) ) {
	$store_arr[$i] = $a['store_id'] ;
	$i++;
}
//************************* END INITIALIZATION	**************************

if(isset($_POST['XML_INPUT_VALUE'])){
	$arr_xml = xml2php($_POST['XML_INPUT_VALUE']);
	requestType($arr_xml,$PassKey);
} else {
	echo "Xml request data needed to process this file";
	die();
}

function xml2php($xml_content) {
	$xml_parser = xml_parser_create();
	xml_parse_into_struct($xml_parser, $xml_content, $arr_vals);
	if(xml_get_error_code($xml_parser)!=false) {
		$xmlstr="<xmlPopulate>\n<xmlProductsImportResponse>";
		$xmlstr.="Error : ".xml_error_string(xml_get_error_code($xml_parser))." At Line No :  ".xml_get_current_line_number($xml_parser);
		$xmlstr.="</xmlProductsImportResponse>\n</xmlPopulate>";
		output_xml($xmlstr);
		exit;
	}
	xml_parser_free($xml_parser);
	return $arr_vals;
}

function requestType($array_haystack,$PassKey) {
	if ((!empty($array_haystack)) AND (is_array($array_haystack))){
		foreach ($array_haystack as $xml_key => $xml_value){
			
			//for Ping
			if(strtolower($xml_value["tag"])=="requesttype" && strtolower($xml_value["value"])=="ping"){
				$type="Checking for test database connection";
				$cat=strtolower($xml_value["value"]);
			}
			
			//For Product listing
			if(strtolower($xml_value["tag"])=="requesttype" && strtolower($xml_value["value"])=="getproducts"){
				$type="Display Product Listing";
				$cat=strtolower($xml_value["value"]);
			}

			//For Product import
			if(strtolower($xml_value["tag"])=="requesttype" && strtolower($xml_value["value"])=="productsimport"){
				$type="Import product to database";
				$cat=strtolower($xml_value["value"]);
			}
			
			// Added for SKU prefix
			if(strtolower($xml_value["tag"])=="skuprefix"){
				$skuPrefixVal = strtoupper($xml_value["value"]);
			}
			
			if(strtolower($xml_value["tag"])=="passkey"){
				$entered_key=strtolower($xml_value["value"]);
				break;
			}
		}
	}

	//This section checks if entered key in xml file is valid
	if($entered_key!=$PassKey){
		echo "<br>Error...invalid Key";
		exit;
	}
	
	switch($cat){
		case "ping": {
			Ping();
			break;
		}
		case "getproducts":	{
			GetProd($skuPrefixVal);
			break;		
		}
		case "productsimport": {
			ImportProduct($_POST['XML_INPUT_VALUE']);
			break;		
		}
	}
}

function output_xml($content){
	header("Content-Type: application/xml; charset=ISO-8859-1");
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: ". gmdate("D, d M Y H:i:s") ." GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	print $content;
}

// This ping the store to see, if we can connect to database to start updates.
function Ping(){
	$xml_str = "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n";
	$xml_str.="<xmlPopulateResponce>\n";
	$Ping_res=mysql_connect(DB_SERVER, DB_USER, DB_PASS);

	if(!$Ping_res)
		$xml_str.="<heartbeat>error</heartbeat>\n";
	else
		$xml_str.="<heartbeat>alive</heartbeat>\n";
	$xml_str.="</xmlPopulateResponce>";
	
	output_xml($xml_str);
}

// This function GetProducts SKU and images associated to it. It helps us to find products that needs to be updated 
// and images that needs to be send.
function GetProd($skuPrefixVal) {
	$likeSql = "";
	if(isset($skuPrefixVal) && $skuPrefixVal != "")
		$likeSql = "and pe.sku like '$skuPrefixVal%'";
	
	$xml_str = "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n";

	$sql_prod = "SELECT pe.sku sku, pev.value image, ss.qty quantity, ps.value status, ped.value price FROM 
		" . CATALOG_PRODUCT_ENTITY . " as pe 
		INNER JOIN " . CATALOGINVENTORY_STOCK_STATUS . " as ss ON pe.entity_id = ss.product_id 
		INNER JOIN " . CATALOG_PRODUCT_ENTITY_ . "int as ps ON pe.entity_id = ps.entity_id and ps.attribute_id 
		IN (SELECT attribute_id FROM ".EAV_ATTRIBUTE." where attribute_code = 'status') 
		INNER JOIN ".CATALOG_PRODUCT_ENTITY_."decimal as ped ON pe.entity_id = ped.entity_id and ped.attribute_id 
		IN (SELECT attribute_id FROM ".EAV_ATTRIBUTE." where attribute_code = 'price') 
		INNER JOIN " . CATALOG_PRODUCT_ENTITY_VARCHAR . " as pev ON pe.entity_id = pev.entity_id and pev.attribute_id 
		IN (SELECT attribute_id FROM " . EAV_ATTRIBUTE . " t1 
		INNER JOIN (SELECT entity_type_id from " . EAV_ENTITY_TYPE . " where entity_type_code = 'catalog_product') as t2 
		ON t1.entity_type_id = t2.entity_type_id where t1.attribute_code IN ('image')) " . $likeSql;

	$result = mysql_query($sql_prod);
	$xml_str .= "<xmlPopulate>\n";
	$xml_str .= "<xmlGetProductsResponse>\n";
	$count=0;
	while ($rowprod=mysql_fetch_array($result))	{
		$v_products_image = "&amp;";
		if($rowprod['image'] != "")
			$v_products_image = $rowprod['image'];
		
		$v_products_status = 1;
		if($rowprod['status'] == "2")
			$v_products_status = 0;
		$xml_str .= "<xmlProduct>\n<v_products_model><![CDATA[".$rowprod['sku']."]]></v_products_model>\n";
		$xml_str .= "<v_products_image>".$v_products_image."</v_products_image>\n";
		$xml_str .= "<v_products_quantity>".intval($rowprod['quantity'])."</v_products_quantity>\n";
		$xml_str .= "<v_products_status>".$v_products_status."</v_products_status>\n";
		$xml_str .= "<v_products_price>".$rowprod['price']."</v_products_price>\n</xmlProduct>\n";
		
		$count++;
	}
	$xml_str.="</xmlGetProductsResponse>\n</xmlPopulate>";
	output_xml($xml_str);
}

// This function finds does add and updates of the products.
function ImportProduct($array_haystack) {
	global $usercount;
	global $userdata;
	global $cat_val;
	global $website;
	global $product_type_id;
	global $product_attribute_set_id;
	global $stock_id;
	$full_update;
	$partial_update;
	$state='';

	// Parsing the XML request and stores data in the form of array.
	if (!($xml_parser = xml_parser_create())) 
		die("Couldn't create parser.");

	xml_set_element_handler( $xml_parser, "startElementHandler", "endElementHandler");
	xml_set_character_data_handler( $xml_parser, "characterDataHandler");

	xml_parse($xml_parser, $array_haystack);
	xml_parser_free($xml_parser);

	// "full_update" are the field that needs to be updated when doing the full product import
	$full_update = array("name","image","status","cost","price","weight","visibility","tax_class_id","description","short_description","small_image","dimension","model","thumbnail"); 

	// "partial_update" are the field that needs to be updated when doing the partial product import
	$partial_update = array("cost","price","status"); 

	// making the array of "id" and their "datatypes" of fields that needs to be updated so that it can be updated.
	$sql =  "SELECT att.attribute_id, att.backend_type,att.attribute_code FROM " . EAV_ATTRIBUTE . " as att INNER JOIN  " . EAV_ENTITY_TYPE . " as type ON (att.entity_type_id=type.entity_type_id ) where type.entity_type_code='catalog_product' and att.attribute_code IN ('name','image','status','cost','price','weight','visibility','tax_class_id','description','short_description','small_image','dimension','model','in_depth','thumbnail')";
	$result = mysql_query($sql);
	while($value = mysql_fetch_array($result)) {
		$attribute_values[$value["attribute_code"]]["attribute_id"] = $value["attribute_id"];
		$attribute_values[$value["attribute_code"]]["backend_type"] = $value["backend_type"];
	}
	unset($result);

	// finds skus that are present on the store, it helps up to identify products that needs only update and need not to be inserted. 
	$sku_in = "";
	for($i = 1; $i <= $usercount; $i++){
		if(isset($userdata[$i]["sku"]) && $userdata[$i]["sku"] != '') {
			if(strlen($sku_in) > 0)
				$sku_in .=",";
			$sku_in .= "'".$userdata[$i]["sku"]."'";
		}
	}
	
	$query="SELECT sku FROM " . CATALOG_PRODUCT_ENTITY;
	if(strlen($sku_in) > 0)
		$query .= " where sku in (".$sku_in.")";

	$result_b = mysql_query($query);
	$counter=0;
	$skus = array();
	while($result = mysql_fetch_array($result_b)){
		$skus[$counter]=mysql_real_escape_string($result["sku"]) ;
		$counter ++;
	}

	// finds the id of "Taxable Goods" to make products taxable
	$query="SELECT class_id FROM `" . TAX_CLASS . "` WHERE `class_name` = 'Taxable Goods'";
	$result=mysql_query($query);
	$tax_class_id = mysql_result($result,0);

	// finds the id of "media_gallery" for inserting images.
	$sql="SELECT attribute_id from " . EAV_ATTRIBUTE . " where attribute_code = 'media_gallery' and entity_type_id =" . $product_type_id;
	$result=mysql_query($sql);
	$media_gallery=mysql_result($result,0);
	$xmlstr="<xmlPopulate>\n<xmlProductsImportResponse>\n";

	for ($i=1; $i <= $usercount; $i++){
		$xmlstr.="<xmlProductImport>";
		
		// Updating Product status
		if(strcasecmp($userdata[$i]["status"], "InActive") == 0)
			$userdata[$i]["status"] = 2;
		elseif(strcasecmp($userdata[$i]["status"], "Active") == 0)
			$userdata[$i]["status"] = 1;
		
		// Updating Stock status
		$status = 0 ;
		if($userdata[$i]["quantity"] == 0 )
			$status = 0;
		else
			$status = 1;
		
		$userdata[$i]["visibility"] = 4;
		$userdata[$i]["tax_class_id"] = $tax_class_id;
		$userdata[$i]["model"] = $userdata[$i]["sku"];
		$userdata[$i]["dimension"] = '';
		
		$cat_val = CategoryAttributeId();
		$partial_update = array("cost","price","status"); 
		//Download image to magento
		if(isset($userdata[$i]["image"]) && $userdata[$i]["image"] != ""){
			//Calculate image location
			$location = "media/catalog/product/isimages/".basename($userdata[$i]["image"]);
			//$file = file_get_contents($userdata[$i]["image"]);
			//file_put_contents($location,$file);
			get_file($userdata[$i]["image"],$location);
			$userdata[$i]["image"] = "/isimages/".basename($userdata[$i]["image"]);
			$userdata[$i]["small_image"] = $userdata[$i]["image"];
			$userdata[$i]["thumbnail"] = $userdata[$i]["image"];
			$partial_update = array("cost","price","status","image","small_image","thumbnail");
		}
		
		// If number of tages are greater the 6 then we consider it as full product import else just as normal update. 
		if($userdata[$i]["tag_count"] > 9){
			$attribute_list = $full_update;
			
			if(isset($userdata[$i]["v_products_length"]))
				$userdata[$i]["dimension"] = $userdata[$i]["v_products_length"]  . " x " . $userdata[$i]["v_products_width"]  . " x " . $userdata[$i]["v_products_height"];
			
			$userdata[$i]["name"] = str_replace('\\"','"',$userdata[$i]["name"]);
			$userdata[$i]["name"] = str_replace('\\\\"','"',$userdata[$i]["name"]);
			$userdata[$i]["description"] = str_replace('\\"','"',$userdata[$i]["description"]);
			$userdata[$i]["description"] = str_replace('\\\\"','"',$userdata[$i]["description"]);
			$userdata[$i]["short_description"] = trimForShortDesc($userdata[$i]["description_short"]);
		} else {
			$attribute_list = $partial_update;
		}
		
		$xmlstr.="<v_products_model>" . $userdata[$i]["sku"] . "</v_products_model>\n"; 

		if($userdata[$i]["sku"] != '') {
			// If sku present in the database we do update of insert it as new.	
			if(in_array($userdata[$i]["sku"],$skus)) {
				// We send  this string as response to find out how many products are inserted or added as new.	
				$xmlstr.="<v_status>UPDATE</v_status>\n";

				// Generic Code for the updating product attributes
				// Here we do the updates products fields on the basis array of "id" and their "datatypes" we made above.
				// Table that needs to be updated is find out as "CATALOG_PRODUCT_ENTITY_<datatype>" and id is field that is being updated.
				foreach($attribute_list as $val) {
					if($attribute_values[$val]["backend_type"] == 'int' || $attribute_values[$val]["backend_type"] == 'decimal') {
						if($attribute_values[$val]["backend_type"] == 'int' && $userdata[$i][$val] == '')
							$userdata[$i][$val] = 0.0000;
						else if($attribute_values[$val]["backend_type"] == 'decimal' && $userdata[$i][$val] == '')
							$userdata[$i][$val] = 0.0;

						$sql_update = "UPDATE " . CATALOG_PRODUCT_ENTITY . " as pe INNER JOIN " . CATALOG_PRODUCT_ENTITY_ . $attribute_values[$val]["backend_type"] . " as ped ON
						pe.entity_id = ped.entity_id and ped.attribute_id IN (SELECT attribute_id FROM " . EAV_ATTRIBUTE . " t1 INNER JOIN 
						(SELECT entity_type_id from " . EAV_ENTITY_TYPE . " where entity_type_code = 'catalog_product') as t2
						ON t1.entity_type_id = t2.entity_type_id where t1.attribute_code = '" . $val . "') SET  
						ped.value=" . $userdata[$i][$val]  .  " where pe.sku='" . $userdata[$i]["sku"] . "'";
					} else {
						$sql_update = "UPDATE " . CATALOG_PRODUCT_ENTITY . " as pe INNER JOIN " . CATALOG_PRODUCT_ENTITY_ .  $attribute_values[$val]["backend_type"] . " as ped ON pe.entity_id = ped.entity_id and ped.attribute_id IN (SELECT attribute_id FROM " . EAV_ATTRIBUTE . " t1 INNER JOIN (SELECT entity_type_id from " . EAV_ENTITY_TYPE . " where entity_type_code = 'catalog_product') as t2 ON t1.entity_type_id = t2.entity_type_id where t1.attribute_code = '" . $val. "') SET ped.value='" . $userdata[$i][$val]  .  "' where pe.sku='" . $userdata[$i]["sku"] . "'";
					}	
					$result=mysql_query($sql_update);
				}
					
				$sql= "SELECT entity_id from " . CATALOG_PRODUCT_ENTITY . " where sku = '" . $userdata[$i]["sku"] . "'";
				$result = mysql_query($sql);
				$w_entity  = mysql_result($result,0);
				
				// If it's full product import we update or create manufacturer and Category
				if($userdata[$i]["tag_count"] > 9) {
					insertManufacturer($w_entity,$userdata[$i]["v_manufacturers_name"],"update");
					
					// Fetches the category id if it already present or creates new
					$cat_id = CategoryLookup($userdata[$i]["categories_1"]);
					// If main category is not found skip product
					if($cat_id["id"] == '')
						continue;
									
					$sub_cat_id;
					$levelSecond_id = "";
					$levelthird_cat_id;
					$levelthird_id = "";
					checkAndAddAttribute($cat_id["id"]);
					
					if(isset($userdata[$i]["categories_2"]) && $userdata[$i]["categories_2"] != '') {
						// Fetches the sub category id if it already present or creates new 
						$sub_cat_id = SubCategoryLookup($userdata[$i]["categories_2"],$cat_id["id"],$cat_id["path"]);
						// creating comma seperated string of the categories under which products will be visible. This string is 
						// stored under the table  "CATALOG_PRODUCT_ENTITY"
						$catString = $cat_id["id"] . "," . $sub_cat_id["id"];
						$indexPath=$sub_cat_id["path"];
						$levelSecond_id=$sub_cat_id["id"];
					}
					
					// If new product category is defind then we fetches the category id if it already present or creates new
					if(isset($userdata[$i]["categories_3"]) && $userdata[$i]["categories_3"] != '') {
						$levelthird_cat_id = SubCategoryLookup($userdata[$i]["categories_3"],$sub_cat_id["id"],$sub_cat_id["path"]);
						//updating comma seperated string of the categories if 3rd category is found
						$catString = $sub_cat_id["id"] . "," . $levelthird_cat_id["id"];
						$indexPath=$levelthird_cat_id["path"];
						$levelthird_id=$levelthird_cat_id["id"];
						checkAndAddAttribute($levelthird_cat_id["id"]);
					}
					
					categoryProductIndex($indexPath,$cat_id["id"], $levelSecond_id,$levelthird_id,$w_entity);
					
					// To be inserted for all the products
					if($cat_id["id"] != "") {
					   $sql_update = "Delete from " . CATALOG_CATEGORY_PRODUCT . " where product_id = ".  $w_entity  ;
					   $result = mysql_query($sql_update);
					   $query = "INSERT INTO `" . CATALOG_CATEGORY_PRODUCT . "` (`category_id`, `product_id`, `position`) VALUES ( " . $cat_id["id"] . "," . $w_entity . ", 0)";
					   $result =  mysql_query($query);
					}
					
					if($levelSecond_id != "") {
						$query = "INSERT INTO `" . CATALOG_CATEGORY_PRODUCT . "` (`category_id`, `product_id`, `position`) VALUES ( " . $levelSecond_id . "," . $w_entity . ", 0)";
						$result =  mysql_query($query);
					}
					
					if($levelthird_id != "") {
						$query = "INSERT INTO `" . CATALOG_CATEGORY_PRODUCT . "` (`category_id`, `product_id`, `position`) VALUES ( " . $levelthird_id . "," . $w_entity . ", 0)";
						$result =  mysql_query($query);
					}
				}
					
				// Updating Product Category association
				updateProductIndexes($userdata[$i]["status"],$w_entity);

				// Updating Stock Quantity
				$sql_update = "UPDATE " . CATALOGINVENTORY_STOCK_ITEM . " SET is_in_stock=".$status.", qty = " . $userdata[$i]["quantity"] . " where product_id = (Select entity_id 
				from " . CATALOG_PRODUCT_ENTITY . " where sku = '" . $userdata[$i]["sku"] . "')";
				$result = mysql_query($sql_update); 
				
				$sql_update = "UPDATE " . CATALOGINVENTORY_STOCK_STATUS . " SET qty = " . $userdata[$i]["quantity"] . ",  stock_status = " . $status .  " where product_id = (Select entity_id 
				from " . CATALOG_PRODUCT_ENTITY . " where sku = '" . $userdata[$i]["sku"] . "')";
				$result = mysql_query($sql_update); 
					
				//image association in media gallery table required for product images to be visible
				if(isset($userdata[$i]["image"]) && $userdata[$i]["image"] != '') {
					$sql_update = "Delete from " . CATALOG_PRODUCT_ENTITY_MEDIA_GALLERY . " where attribute_id = ".  $media_gallery  . " and entity_id = " . $w_entity ;
					$result = mysql_query($sql_update);
					
					$sql_update = "INSERT INTO " . CATALOG_PRODUCT_ENTITY_MEDIA_GALLERY . " (attribute_id,entity_id,value) VALUES (" . $media_gallery . "," . $w_entity . ",'" . $userdata[$i]["image"] . "')";
					$result = mysql_query($sql_update);
					
					if($userdata[$i]["small_image"] != '') {
						$sql_update = "INSERT INTO " . CATALOG_PRODUCT_ENTITY_MEDIA_GALLERY . " (attribute_id,entity_id,value) VALUES (" . $media_gallery . "," . $w_entity. ",'" . $userdata[$i]["small_image"] . "')";
						$result = mysql_query($sql_update);
					}
				}
			} else {
				// Adding product as New if it's not found in store database
				$xmlstr.="<v_status>NEW</v_status>\n";
				
				// Fetches the category id if it already present or creates new
				$cat_id = CategoryLookup($userdata[$i]["categories_1"]);
				
				// If main category is not found skip product
				if($cat_id["id"] == '')
					continue;

				checkAndAddAttribute($cat_id["id"]);
				$sub_cat_id;
				$levelSecond_id = ""; 
				$levelthird_cat_id;
				$levelthird_id = "";
				if(isset($userdata[$i]["categories_2"]) && $userdata[$i]["categories_2"] != '') {
					// Fetches the sub category id if it already present or creates new 
					$sub_cat_id = SubCategoryLookup($userdata[$i]["categories_2"],$cat_id["id"],$cat_id["path"]);
					checkAndAddAttribute($sub_cat_id["id"]);
					// creating comma seperated string of the categories under which products will be visible. This string is 
					// stored under the table  "CATALOG_PRODUCT_ENTITY"
					$catString = $cat_id["id"] . "," . $sub_cat_id["id"];
					$indexPath=$sub_cat_id["path"];
					$levelSecond_id=$sub_cat_id["id"];
				}
				
				// If new product category is defind then we fetches the category id if it already present or creates new
				if(isset($userdata[$i]["categories_3"]) && $userdata[$i]["categories_3"] != '') {
					$levelthird_cat_id = SubCategoryLookup($userdata[$i]["categories_3"],$sub_cat_id["id"],$sub_cat_id["path"]);
					//updating comma seperated string of the categories if 3rd category is found
					$catString = $sub_cat_id["id"] . "," . $levelthird_cat_id["id"];
					$indexPath=$levelthird_cat_id["path"];
					$levelthird_id=$levelthird_cat_id["id"];
					checkAndAddAttribute($levelthird_cat_id["id"]);
				}
				// Inserting new product
				$sqlInsert = "INSERT INTO " . CATALOG_PRODUCT_ENTITY . " (entity_type_id, attribute_set_id, type_id, sku, created_at, updated_at, has_options) VALUES 
				(" . $product_type_id . "," . $product_attribute_set_id . ", 'simple','" . $userdata[$i]["sku"] . "', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 0)";
				$result = mysql_query($sqlInsert);
				
				// Fecthing entity_id of the new product to populate attributes in other product attributes
				$entity_result="SELECT entity_id from " . CATALOG_PRODUCT_ENTITY . " where sku = '" . $userdata[$i]["sku"] . "'";
				$result = mysql_query($entity_result);	
				$new_entity = mysql_result($result,0);
				
				// Creating association of the product with the site on which it's visible     
				siteProductIndex($new_entity);
				
				// Creating association of the product with the category on which it's visible     
				categoryProductIndex($indexPath,$cat_id["id"], $sub_cat_id["id"],$levelthird_id,$new_entity);
				
				// Finding id of the product
				$quey = "(SELECT attribute_id FROM " . EAV_ATTRIBUTE . " t1 INNER JOIN (SELECT entity_type_id from 	" . EAV_ENTITY_TYPE . " where entity_type_code = 'catalog_product') as t2 ON t1.entity_type_id = t2.entity_type_id where t1.attribute_code ='manufacturer')";
				$aaa = mysql_query($quey);
				$attribute_id = mysql_result($aaa,0);
															
				// To be inserted for all the products
				if($cat_id["id"] != "") {
				   $query = "INSERT INTO `" . CATALOG_CATEGORY_PRODUCT . "` (`category_id`, `product_id`, `position`) VALUES ( " . $cat_id["id"] . "," . $new_entity . ", 0)";
				   $result =  mysql_query($query);
				}
				
				if($sub_cat_id["id"] != "") {
					$query = "INSERT INTO `" . CATALOG_CATEGORY_PRODUCT . "` (`category_id`, `product_id`, `position`) VALUES ( " . $sub_cat_id["id"] . "," . $new_entity . ", 0)";
					$result =  mysql_query($query);
				}
				
				if($levelthird_cat_id["id"] != "") {
					$query = "INSERT INTO `" . CATALOG_CATEGORY_PRODUCT . "` (`category_id`, `product_id`, `position`) VALUES ( " . $levelthird_cat_id["id"] . "," . $new_entity . ", 0)";
					$result =  mysql_query($query);
				}
				
				// Generic Code for the Inserting product attributes
				// Here we do the updates products fields on the basis array of "id" and their "datatypes" we made above.
				// Table that needs to be updated is find out as "CATALOG_PRODUCT_ENTITY_<datatype>" and id is field that is being updated.
				foreach($attribute_list as $val) {
					if($attribute_values[$val]["backend_type"] == 'int' || $attribute_values[$val]["backend_type"] == 'decimal') {
						if($attribute_values[$val]["backend_type"] == 'int' && $userdata[$i][$val] == '')
							$userdata[$i][$val] = 0.0000;
						else if($attribute_values[$val]["backend_type"] == 'decimal' && $userdata[$i][$val] == '')
							$userdata[$i][$val] = 0.0;
						
						$sql = "INSERT INTO `" . CATALOG_PRODUCT_ENTITY_ . $attribute_values[$val]["backend_type"] . "` (`entity_type_id`, `attribute_id`, `store_id`, `entity_id`, `value`) VALUES
						(" .  $product_type_id . "," . $attribute_values[$val]["attribute_id"]. ",0," . $new_entity .  "," . $userdata[$i][$val] .")";	
					} else {
						$sql = "INSERT INTO `" . CATALOG_PRODUCT_ENTITY_ . $attribute_values[$val]["backend_type"] . "` (`entity_type_id`, `attribute_id`, `store_id`, `entity_id`, `value`) VALUES
						(" . $product_type_id . ",". $attribute_values[$val]["attribute_id"]. ",0," . $new_entity .  ",'" . $userdata[$i][$val] . "')";
					}	
					$result=mysql_query($sql);
				}
					
				// Associating product with the website on which it is visible
				$sql="INSERT INTO `" . CATALOG_PRODUCT_WEBSITE . "` (`product_id`, `website_id`) VALUES (". $new_entity . "," . $website . ")";
				mysql_query($sql);
				// Entries in the stock tables
				insertManufacturer($new_entity,$userdata[$i]["v_manufacturers_name"],"insert");
				$query = "INSERT INTO " . CATALOGINVENTORY_STOCK_ITEM . " (product_id, stock_id, qty, min_qty, use_config_min_qty, is_qty_decimal,backorders,
				use_config_backorders,min_sale_qty,use_config_min_sale_qty,max_sale_qty,use_config_max_sale_qty,
				is_in_stock,low_stock_date,	notify_stock_qty,use_config_notify_stock_qty,manage_stock,use_config_manage_stock,
				stock_status_changed_auto) VALUES(" . $new_entity . "," . $stock_id ."," . $userdata[$i]["quantity"] . ", 0.0000, 1, 0, 0, 1, 1.0000, 1, 0.0000, 1, ".$status.", '0000-00-00 00:00:00', NULL, 1, 0, 1, 0)";
				// echo "<br> line 437 >>> " . $query . "</br>";
				$result = mysql_query($query);
				$query = "INSERT INTO `" . CATALOGINVENTORY_STOCK_STATUS . "` (`product_id`, `website_id`, `stock_id`, `qty`, `stock_status`) VALUES
				(" . $new_entity . "," . $website . "," . $stock_id . "," . $userdata[$i]["quantity"] . ", ".$status.")";
				$result = mysql_query($query); 

				// image association in media gallery table required for product images to be visible
				if(isset($userdata[$i]["image"]) && $userdata[$i]["image"] != ''){	
					$sql_update = "INSERT INTO " . CATALOG_PRODUCT_ENTITY_MEDIA_GALLERY . " (attribute_id,entity_id,value) VALUES (" . $media_gallery . "," . $new_entity . ",'" . $userdata[$i]["image"] . "')";
					$result = mysql_query($sql_update);
					
					if($userdata[$i]["small_image"] != '') {
						$sql_update = "INSERT INTO " . CATALOG_PRODUCT_ENTITY_MEDIA_GALLERY . " (attribute_id,entity_id,value) VALUES (" . $media_gallery . "," . $new_entity. ",'" . $userdata[$i]["small_image"] . "')";
						$result = mysql_query($sql_update);
					}
				}
			}
		} else {
			$xmlstr.="<v_status>Missing Model Number</v_status>\n";
		}
		$xmlstr.="</xmlProductImport>";
	}
	$xmlstr.="</xmlProductsImportResponse>\n</xmlPopulate>";
    
    include("./clearcache.php");
	
	output_xml($xmlstr);
}

// finding the attribute id of category attribute like 'name' and 'url_key'
function CategoryAttributeId() {
	global $cat_val;
	global $cat_type_id;
	$sql="SELECT attribute_id,attribute_code FROM `" . EAV_ATTRIBUTE . "` WHERE entity_type_id = " .   $cat_type_id . " and `attribute_code` IN ('name','url_key')";
	$result=mysql_query($sql);
	while($cat_att = mysql_fetch_array($result)) {
		 $cat_val[$cat_att["attribute_code"]]= $cat_att["attribute_id"];
	}
	return $cat_val;
}

// finds category of the product.
function CategoryLookup($p_cat_value) {
	global $base_cat;  	
	// finds all the categories with the name of the category
	$cat_entity = findCategory($p_cat_value );
	$cat_entity_id = $cat_entity[0]["entity_id"];
	// if we have some values then we check if it's the child of the base category in that
	// case only it's a main category
	if($cat_entity_id != '') {			
		// echo "Many Sub Match Main Catfound";
		$cat_details_arr["id"]='';
		for($i=0;$i<count($cat_entity);$i++) {
			// find the path and parent id of all the categories that matches the name of the ctagory				
			$sql = "SELECT path,parent_id FROM " . CATALOG_CATEGORY_ENTITY . " where entity_id = " . $cat_entity[$i]["entity_id"];
			$result = mysql_query($sql);
			while($cat_details = mysql_fetch_array($result)) {
				// if parent of the category is same as base category then we return that id				
				if($cat_details["parent_id"] == $base_cat) {
					$cat_details_arr["path"]= $cat_details["path"];
					$cat_details_arr["id"]= $cat_entity[$i]["entity_id"];
				}
			}
		}
		// if we do not find any such category we create new
		if($cat_details_arr["id"] == '')
			$cat_details_arr = createCategory($p_cat_value);
			
	} else { // if no value is retured then we create new category
		// echo "creating sub cat";
		$cat_details_arr = createCategory($p_cat_value);
	}

	return $cat_details_arr;
}

function createCategory ($p_cat_value) { 
	global $base_cat;
	$parent_cat_id= $base_cat;
	$p_cat_path="1/" . $base_cat;
	global $cat_type_id;
	global $cat_val;
	global $catagory_attribute_set_id;
	
	// finds the id of the new category				
	$sql = "SELECT MAX(entity_id) max FROM " . CATALOG_CATEGORY_ENTITY . " ";
	$result = mysql_query($sql);
	$row =  mysql_fetch_array($result);
	$max_subCategory_id = $row['max']+1;
	
	// creating the path of the new category		
	$p_cat_path = $p_cat_path . "/" . $max_subCategory_id; 						
	
	// splitting string to count category level
	$cat_path_arr = explode('/', $p_cat_path); 			
	$length = count($cat_path_arr) -1;
	
	// creating category attributes.			
	$sql="INSERT INTO `" . CATALOG_CATEGORY_ENTITY . "` (`entity_id`, `entity_type_id`, `attribute_set_id`, `parent_id`, `created_at`, `updated_at`, `path`, `position`, `level`, `children_count`) VALUES
	(" . $max_subCategory_id . "," . $cat_type_id . "," . $catagory_attribute_set_id . "," . $parent_cat_id . ", CURRENT_TIMESTAMP , CURRENT_TIMESTAMP,'" . $p_cat_path ."',". $max_subCategory_id ."," . $length .",0)";
	$result=mysql_query($sql);		
	$p_category_id=$max_subCategory_id;
	
	// Update Parent children count of the category
	updateChildrenCount($p_cat_path,$p_category_id);
	
	// Setting the Category Attributes
	setCatAttribute($cat_type_id,$cat_val,$p_category_id,$p_cat_value);
	
	// Making category active
	makeCatActive($p_category_id);  
	
	// Creating the array to return so that product can be associated
	$cat_details["path"]=$p_cat_path;
	$cat_details["id"]=$p_category_id;
	return $cat_details;
}

// Updating the children count of all the category
function updateChildrenCount($cat_path,$current_category_id) {
	$cat_path_arr = explode('/', $cat_path);		
	foreach($cat_path_arr as $node) {
		if($node != $current_category_id ) {
			$sql="Select children_count from " . CATALOG_CATEGORY_ENTITY . " where entity_id =" . $node ;
			$result=mysql_query($sql);
			$p_children_count= mysql_result($result,0);
			$p_children_count = $p_children_count + 1;
			$sql="Update " . CATALOG_CATEGORY_ENTITY . " Set children_count=" . $p_children_count . " where  entity_id =" . $node;
			$result=mysql_query($sql);
			unset($p_children_count);
		}
	}
}

function checkAndAddAttribute($cat_id) {
	global $cat_type_id;
	global $include_in_menu_attribute_id;
			
	$sql = "SELECT COUNT(*) FROM ".CATALOG_CATEGORY_ENTITY_."int WHERE ATTRIBUTE_ID='".$include_in_menu_attribute_id."' AND ENTITY_ID='".$cat_id."'";
	//echo "<br/>".$sql;
	$result=mysql_query($sql);		
	//$count = mysql_fetch_array($result)['COUNT(*)'];
	$count = mysql_result($result,0);
	//echo "count ".$count;
	if ($count > 0)
		$sql = "UPDATE " . CATALOG_CATEGORY_ENTITY_ . "int SET VALUE=1 WHERE ATTRIBUTE_ID='".$include_in_menu_attribute_id."' AND ENTITY_ID='".$cat_id."'";
	else
		$sql="INSERT INTO `" . CATALOG_CATEGORY_ENTITY_ . "int` (`entity_type_id`, `attribute_id`, `store_id`, `entity_id`, `value`) VALUES
		(" . $cat_type_id . ",".$include_in_menu_attribute_id.", 0," . $cat_id . ", 1)";

	//echo "<br/>".$sql;
	$result=mysql_query($sql);
}

// finds all the categories with the name of the sub-category		
function SubCategoryLookup($s_cat_value,$parent_cat_id,$p_cat_path) {    	
	$cat_entity = findCategory($s_cat_value );
	$cat_entity_id = $cat_entity[0]["entity_id"];
	if($cat_entity_id != '') {		
		$cat_details_arr["id"]='';
		// if we have some values then we check if it's the child of the parent category in that
		// case only it's a desired sub-category
		for($i=0;$i<count($cat_entity);$i++) {
			$sql = "SELECT path,parent_id FROM " . CATALOG_CATEGORY_ENTITY . " where entity_id = " . $cat_entity[$i]["entity_id"];
			$result = mysql_query($sql);
			while($cat_details = mysql_fetch_array($result)) {
				// checks parent category of the category					
				if($cat_details["parent_id"] == $parent_cat_id) {
					$cat_details_arr["path"]= $cat_details["path"];
					$cat_details_arr["id"]= $cat_entity[$i]["entity_id"];
				}
			}
		}
		// if not found then creates new category			
		if($cat_details_arr["id"] == '')
			$cat_details_arr = createSubCategory($s_cat_value,$parent_cat_id,$p_cat_path,$s_cat_value);

	} else {
		// if not found then creates new category			
		$cat_details_arr = createSubCategory($s_cat_value,$parent_cat_id,$p_cat_path,$s_cat_value);
	}

	return $cat_details_arr;
}
	
function createSubCategory ($s_cat_value,$parent_cat_id,$p_cat_path,$s_cat_value) { 
	global $cat_type_id;
	global $cat_val;
	global $catagory_attribute_set_id;
	$sql = "SELECT MAX(entity_id) max FROM " . CATALOG_CATEGORY_ENTITY . " ";
	$result = mysql_query($sql);
	$row =  mysql_fetch_array($result);
	$max_subCategory_id = $row['max']+1;
	
	// creating path of the category		
	$s_cat_path = $p_cat_path . "/" . $max_subCategory_id; 						
	$cat_path_arr = explode('/', $s_cat_path);
	
	// creationg level of the category		
	$length = count($cat_path_arr) -1;
	$sql="INSERT INTO `" . CATALOG_CATEGORY_ENTITY . "` (`entity_id`, `entity_type_id`, `attribute_set_id`, `parent_id`, `created_at`, `updated_at`, `path`, `position`, `level`, `children_count`) VALUES
	(" . $max_subCategory_id . "," . $cat_type_id . "," . $catagory_attribute_set_id ."," . $parent_cat_id . ", CURRENT_TIMESTAMP , CURRENT_TIMESTAMP,'" . $s_cat_path ."',". $max_subCategory_id .",$length,0)";
	$result=mysql_query($sql);		
	$sub_category_id=$max_subCategory_id;
	
	// Updating children count		
	updateChildrenCount($s_cat_path,$sub_category_id);
	
	// Setting the category attributes  		
	setCatAttribute($cat_type_id,$cat_val,$sub_category_id,$s_cat_value);
	
	// making category active		
	makeCatActive($sub_category_id);
	
	$cat_details["path"]=$s_cat_path;
	$cat_details["id"]=$sub_category_id;

	return $cat_details;    
}

function setCatAttribute($cat_type_id,$cat_val,$sub_category_id,$s_cat_value) {
	// Inserting the values in name and url key		
	$sql="INSERT INTO `" . CATALOG_CATEGORY_ENTITY_VARCHAR . "` (`entity_type_id`, `attribute_id`, `store_id`, `entity_id`, `value`) VALUES
	(" . $cat_type_id . ", " . $cat_val['name'] . ", 0," . $sub_category_id . ", '" . $s_cat_value . "')";
	$result=mysql_query($sql);
	
	$s_cat_value = str_replace(' ','-',$s_cat_value);		
	$sql="INSERT INTO `" . CATALOG_CATEGORY_ENTITY_VARCHAR . "` (`entity_type_id`, `attribute_id`, `store_id`, `entity_id`, `value`) VALUES
	(" . $cat_type_id . "," . $cat_val['url_key']. ", 0," . $sub_category_id . ", '" .  strtolower($s_cat_value) . "')";
	$result=mysql_query($sql);		
}
	
function findCategory($cat) {
	global $cat_type_id;
	global $cat_val;
	// Finding entity_id of all the category with the given name		
	$sql = "Select distinct a.entity_id,a.value, b.level from " . CATALOG_CATEGORY_ENTITY_VARCHAR . " a, " . CATALOG_CATEGORY_ENTITY . " b where a.attribute_id = " .  $cat_val['name'] ." and a.entity_type_id = " . $cat_type_id . " and value = '" . $cat . "' and a.entity_id = b.entity_id order by b.level";
	$result = mysql_query($sql);
	$i=0;
	while ($rowprod=mysql_fetch_array($result)){	
		$cat_entity[$i]['entity_id'] = $rowprod['entity_id'];
		$cat_entity[$i]['value'] = $rowprod['value'];
		$cat_entity[$i]['level'] = $rowprod['level'];
		$i++;
	}
	return $cat_entity;
}
	
function makeCatActive($cat_entity) {
	global $cat_type_id;
	$cat_attrib = array("is_active"); 
	// Setting the "is_active" attribute of the category		
	$sql =  "SELECT att.attribute_id, att.backend_type,att.attribute_code FROM " . EAV_ATTRIBUTE . " as att INNER JOIN  " . EAV_ENTITY_TYPE . " as type ON (att.entity_type_id=type.entity_type_id ) where type.entity_type_code='catalog_category' and att.attribute_code IN ('is_active')";
	$result = mysql_query($sql);
	$attribute_values;
	$attribute_counter=0;
	while($value=mysql_fetch_array($result)) {
		$attribute_values[$value["attribute_code"]]["attribute_id"] = $value["attribute_id"];
		$attribute_values[$value["attribute_code"]]["backend_type"] = $value["backend_type"];
	}
	
	foreach($cat_attrib as $val) {
		$sql = "INSERT INTO `" . CATALOG_CATEGORY_ENTITY_ . $attribute_values[$val]["backend_type"] . "` (`entity_type_id`, `attribute_id`, `store_id`, `entity_id`, `value`) VALUES
		(" . $cat_type_id . ", " . $attribute_values[$val]["attribute_id"]. " , 0,  " . $cat_entity .  " ,1)";
		$result=mysql_query($sql);
	}
}
	
function categoryProductIndex($cat_path, $new_category_id,$new_subcategory_id,$levelthird_id,$new_entity){
	global $website;
	global $store_arr;

	// Setting the "visibilty" attribute of the category to "4" for all the hierarchy nodes		
	$sql="Select store_id,website_id from " . CORE_STORE . " where is_active=1 and website_id = " . $website . " and store_id > 0";
	$store_result = mysql_query($sql);
	$cat_path_arr = explode('/', $cat_path);
	for($j=0;$j < count($store_arr);$j++) {   
		foreach($cat_path_arr as $node) {
			if($node == $new_category_id || $node == $new_subcategory_id || $node == $levelthird_id)
				$parent = 1 ;
			else
				$parent = 0;
			
			if($node != "") {
				$sql="INSERT INTO `" . CATALOG_CATEGORY_PRODUCT_INDEX . "` (`category_id`, `product_id`, `position`, `is_parent`, `store_id`, `visibility`) VALUES
				(" . $node . "," . $new_entity . ", 0, " . $parent . "," . $store_arr[$j] . ", 4)";
				//echo "<br/>".$sql;
				$result=mysql_query($sql);
			}
		}
	}
}

function siteProductIndex($new_entity) {
	global $website;
	global $store_arr;
	// Creating the association of the product with the store and setting "visibilty" to 4		
	$sql="Select store_id,website_id from " . CORE_STORE . " where is_active=1 and website_id = " . $website . " and store_id > 0";
	$store_result = mysql_query($sql);
	for($j=0;$j < count($store_arr);$j++) {    		
		$sql="INSERT INTO `" . CATALOG_PRODUCT_ENABLED_INDEX . "` (`product_id`, `store_id`, `visibility`) VALUES
		(" . $new_entity . "," . $store_arr[$j] . ", 4)";
		$result=mysql_query($sql);
	}
}

// XML Parsing function
function startElementHandler ($parser,$name,$attrib) {
	global $usercount;
	global $state;
	$state = $name;
	global $tag ;
	if($name==strtoupper("xmlproduct")) { 
		$usercount++;
		$tag = 0;
	}
}

// XML Parsing function
function endElementHandler ($parser,$name) {
	global $usercount;
	global $userdata;
	global $state;
	global $tag;
	$state='';
	if($name==strtoupper("xmlproduct")) { 
		$userdata[$usercount]["tag_count"] = $tag;
		//$usercount++;
	}
}

// XML Parsing function
function characterDataHandler ($parser, $data) {
	global $usercount;
	global $userdata;
	global $state;
	global $tag;
	if (!$state)
		return;

	$data = mysql_real_escape_string($data);
	
	if ($state==strtoupper("v_products_model")) { $userdata[$usercount]["sku"] = $data; $tag++;}
	if ($state==strtoupper("v_products_price")) { $userdata[$usercount]["price"] = $data; $tag++;}
	if ($state==strtoupper("v_products_quantity")) { $userdata[$usercount]["quantity"] = $data; $tag++;}
	if ($state==strtoupper("v_products_msrp")) { $userdata[$usercount]["v_products_msrp"] = $data; $tag++;}
	if ($state==strtoupper("v_products_cost")) { $userdata[$usercount]["cost"] = $data; $tag++;}
	if ($state==strtoupper("v_status")) { $userdata[$usercount]["status"] = $data; $tag++;}
	if ($state==strtoupper("v_products_image")) { $userdata[$usercount]["image"] = $data; $tag++;}
	if ($state==strtoupper("v_products_name_1")) { $userdata[$usercount]["name"] = $data; $tag++;}
	if ($state==strtoupper("v_products_description_1")) { $userdata[$usercount]["description"] = $data; $tag++;}
	if ($state==strtoupper("v_products_weight")) { $userdata[$usercount]["weight"] = $data; $tag++;}
	if ($state==strtoupper("v_manufacturers_id")) {$userdata[$usercount]["v_manufacturers_id"] = $data; $tag++;}
	if ($state==strtoupper("v_manufacturers_name")) {$userdata[$usercount]["v_manufacturers_name"] = $data; $tag++;}
	if ($state==strtoupper("v_products_upc")) {$userdata[$usercount]["v_products_upc"] = $data; $tag++;}
	if ($state==strtoupper("v_products_thumbnail")) {$userdata[$usercount]["small_image"] = $data; $tag++;}
	if ($state==strtoupper("v_categories_name_1")) {$userdata[$usercount]["categories_1"] = $data; $tag++;}
	if ($state==strtoupper("v_categories_name_2")) {$userdata[$usercount]["categories_2"] = $data; $tag++;}
	if ($state==strtoupper("v_categories_name_3")) {$userdata[$usercount]["categories_3"] = $data; $tag++;}
	if ($state==strtoupper("v_products_length")) {$userdata[$usercount]["length"] = $data; $tag++;}
	if ($state==strtoupper("v_products_width")) {$userdata[$usercount]["width"] = $data; $tag++;}
	if ($state==strtoupper("v_products_height")) {$userdata[$usercount]["height"] = $data; $tag++;}
	if ($state==strtoupper("v_dropshipper_id")) {$userdata[$usercount]["v_dropshipper_id"] = $data; $tag++;}
	if ($state==strtoupper("v_dropshipper_prefix")) {$userdata[$usercount]["v_dropshipper_prefix"] = $data; $tag++;}
	if ($state==strtoupper("v_dropshipper_name")) {$userdata[$usercount]["v_dropshipper_name"] = $data; $tag++;}
	if ($state==strtoupper("v_products_description_short")) {$userdata[$usercount]["description_short"] = $data; $tag++;}
}

function insertManufacturer($entity_id,$m_name, $action) {
	global $product_type_id;
	$max_value_id;
	
	// Finding the attribute id of manufacturer
	$sql="SELECT attribute_id from " . EAV_ATTRIBUTE . " where attribute_code = 'manufacturer' and entity_type_id =" . $product_type_id;
	$result=mysql_query($sql);
	$manufact_eav_id=mysql_result($result,0);
	
	// Looking if manufacturer already exist with the name       
	$sql="SELECT option_id FROM " . EAV_ATTRIBUTE_OPTION_VALUE . " where value = '" . $m_name . "' 
	and option_id IN (SELECT option_id from " . EAV_ATTRIBUTE_OPTION . " where attribute_id =" . $manufact_eav_id . ")";
	// echo $sql;
	$result = mysql_query($sql); 
	// If exist then returns	
	if( mysql_num_rows($result) > 0 ) {
		$max_option_id = mysql_result($result,0);
	} else { // If manufacturer does not exist then creating	
		// echo "Other";
		$sql="SELECT MAX(option_id) FROM " . EAV_ATTRIBUTE_OPTION . "";
		$result = mysql_query($sql);
		$max_option_id=mysql_result($result,0);
		$max_option_id=$max_option_id+1;
		// As manufacturer shown in dropdown we create new option
		$sql="INSERT INTO `" . EAV_ATTRIBUTE_OPTION . "` (`option_id`, `attribute_id`, `sort_order`) VALUES
		(" . $max_option_id . "," . $manufact_eav_id . ",0)";
		$result = mysql_query($sql);
		$sql="SELECT MAX(value_id) FROM " . EAV_ATTRIBUTE_OPTION_VALUE . "";
		// echo "</br>$sql</br>";
		$result = mysql_query($sql);
		$max_value_id=mysql_result($result,0);
		$max_value_id=$max_value_id+1;
		// Name of the manufacturer is inserted in the below table		
		$sql="INSERT INTO `" . EAV_ATTRIBUTE_OPTION_VALUE . "` (`value_id`, `option_id`, `store_id`, `value`) VALUES
		(" . $max_value_id . "," .  $max_option_id . ",0,'" . $m_name . "')";
		// echo "</br>$sql</br>";
		$result=mysql_query($sql);
	}

	// Associating the option_id (manufacturer) with the product	
	if($action == "insert") {
		// echo "inserting...." .  $action ;
		$sql="INSERT INTO `" . CATALOG_PRODUCT_ENTITY_INT . "` (`entity_type_id`, `attribute_id`, `store_id`, `entity_id`, `value`) VALUES
		(". $product_type_id . "," . $manufact_eav_id . ",0," . $entity_id . "," . $max_option_id . ")";
		$result = mysql_query($sql);
	} elseif($action == "update"){
		// echo "updating...." . $action ;
		$sql="UPDATE " . CATALOG_PRODUCT_ENTITY_INT . " SET value =". $max_option_id . " WHERE entity_type_id =" . $product_type_id . "  
		and attribute_id =". $manufact_eav_id . " and  entity_id =" . $entity_id ;
		$result = mysql_query($sql);
	} 	
}

// Trimming the description to make Short Description
function trimForShortDesc($var){
	$short_desc;
	$len =  strlen($var);
	if($len > 250 && !($len < 300)) {
		$short_desc = substr($var, 0 , 250);
		$short_desc = $short_desc . "...";
	} else {
		$short_desc  = $var;
	}
	return $short_desc;
}

// When product is made in-active we need to "CATALOG_CATEGORY_PRODUCT_INDEX" to make product
// not visible under category on the store
function updateProductIndexes($status, $entity) {
	if($status == 1) {
		$sql = "UPDATE " . CATALOG_CATEGORY_PRODUCT_INDEX . " set visibility = 4 where `product_id`= " . $entity ;
		$result = mysql_query($sql);		
	} elseif($status == 2) {
		$sql = "UPDATE " . CATALOG_CATEGORY_PRODUCT_INDEX . " set visibility = 1 where `product_id`= " . $entity ;
		$result = mysql_query($sql);					
	}
}
	
function updatecategoryProductIndex($new_entity){
	global $website;
	global $store_arr;
	global $base_cat;
	// finds the category ids where products are visible
	$sql = "SELECT `category_ids` FROM " . CATALOG_PRODUCT_ENTITY . " where `entity_id`= " . $new_entity;
	$result = mysql_query($sql);
	$category_id = mysql_result($result,0);
	$cat_id_arr = explode(',', $category_id);
	$length = count($cat_id_arr);
	$cat_id = "";
	if($length > 0 )
		$cat_id = $cat_id_arr[$length-1];
	
	if($cat_id != "") {
		$sql = "SELECT path FROM " . CATALOG_CATEGORY_ENTITY . " WHERE `entity_id` = " . $cat_id;
		$result = mysql_query($sql);
		$category_path="";
		if( mysql_num_rows($result) > 0 )
			$category_path = mysql_result($result,0);

		// creating association to make product visible when made active		
		if($category_path != "" ){
			$sql="Select store_id,website_id from " . CORE_STORE . " where is_active=1 and website_id = " . $website . " and store_id > 0";
			$store_result = mysql_query($sql);
			$cat_path_arr = explode('/', $category_path);
			for($j=0;$j < count($store_arr);$j++) {   
				foreach($cat_path_arr as $node) {
					if($node == "1" || $node == $base_cat ) 
						$parent = 0 ;
					else 
						$parent = 1;
					
					if($node != "") {
						$sql="INSERT INTO `" . CATALOG_CATEGORY_PRODUCT_INDEX . "` (`category_id`, `product_id`, `position`, `is_parent`, `store_id`, `visibility`) VALUES
						(" . $node . "," . $new_entity . ", 0, " . $parent . "," . $store_arr[$j] . ", 4)";
						$result=mysql_query($sql);
					}				
				}
			} 
		}
	} 
}
	
function get_file($file, $newfilename){
	$out = fopen($newfilename, 'wb');
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_FILE, $out);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_URL, $file);
	curl_exec($ch);   
	curl_close($ch);   
}

mysql_close($dbhandle);
?>
