<?php
/**
 * Magebird.com
 *
 * @category   Magebird
 * @package    Magebird_Popup
 * @copyright  Copyright (c) 2018 Magebird (http://www.Magebird.com)
 * @license    http://www.magebird.com/licence
 * Any form of ditribution, sell, transfer forbidden see licence above 
 */
use \Magebird\MailChimp;
use \Magebird\Remarkety;
class Magebird_Popup_Model_Subscriber extends Mage_Core_Model_Abstract {

    public function _construct() {
        $this->_init('magebird_popup/subscriber');
    }

    public function load($id, $field = null) {
        return parent::load($id, $field);
    }

    function mailCoupon($email, $coupon) {
        $emailTemplate = Mage::getModel('core/email_template')
                ->loadDefault('popup_coupon_newsletter');
        $emailTemplate->setSenderName(Mage::getStoreConfig('trans_email/ident_general/name'));
        $emailTemplate->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email'));
        $emailTemplateVariables = array();
        $emailTemplateVariables['coupon_code'] = $coupon;
        $emailTemplate->send($email, null, $emailTemplateVariables);
    }

    function subscribeKlaviyo($listId, $email, $firstName, $lastName) {
        $url = "https://a.klaviyo.com/api/v1/list/$listId/members";
        $doubleOptin = Mage::getStoreConfigFlag('magebird_popup/services/klaviyo_double_option');
        $doubleOptin = $doubleOptin ? "true" : "false";
        $resp = null;
        $apiKey = Mage::getStoreConfig('magebird_popup/services/klaviyo_key');
        $data = http_build_query(array("api_key" => $apiKey,
            "email" => $email,
            "properties" => '{ "$first_name" : "' . $firstName . '", "$last_name" : "' . $lastName . '" }',
            "confirm_optin" => $doubleOptin
        ));

        $headers = "Content-type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen($data) . "\r\n";
        $options = array("http" => array("method" => "POST", "header" => $headers, "content" => $data));
        $context = stream_context_create($options);
        $resp = @file_get_contents($url, false, $context, 0, 1000);
        $resp = json_decode($resp, true);
        $response['success'] = true;
        if (!$resp) {
            if (function_exists('curl_version')) {
                $fields = array("api_key" => $apiKey,
                    "email" => $email,
                    "properties" => '{ "$first_name" : "' . $firstName . '", "$last_name" : "' . $lastName . '" }',
                    "confirm_optin" => $doubleOptin
                );
                $ch = @curl_init($url);
                @curl_setopt($ch, CURLOPT_POST, true);
                @curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
                @curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $resp = @curl_exec($ch);
                @curl_close($ch);
                $resp = json_decode($resp, true);
            }
        }
        if (!isset($resp['already_member']) && !isset($resp['person'])) {
            $response['success'] = false;
            $response['msg'] = __("Wrong api key or list id");
        } elseif ($resp['status']==404) {
            $response['success'] = false;
            $response['msg'] = $resp['message'];        
        } elseif ($resp['already_member']) {
            $response['success'] = false;
            $response['msg'] = __("You are already subscribed");
        } 
        return $response;
    }

    function subscribeCC($email, $ccListId, $firstName, $lastName) {
        require_once Mage::getBaseDir('lib') . '/magebird/popup/ConstantContact/CC.php';
        require_once Mage::getBaseDir('lib') . '/magebird/popup/ConstantContact/CC.php';
        $apiKey = Mage::getStoreConfig('magebird_popup/services/cc_key');
        $token = Mage::getStoreConfig('magebird_popup/services/cc_token');
        $cc = new cc($apiKey, $token);
        $api = $cc->subscribe($ccListId, array('email' => $email, 'firstName' => $firstName, 'lastName' => $lastName));
        $response['success'] = true;
        if (isset($api['error'])) {
            $response['success'] = false;
            $response['msg'] = __($api['error']);
        }
        return $response;
    }

    function subscribeMailjet($listId, $email, $firstName, $lastName) {
        require_once(Mage::getBaseDir('lib') . '/magebird/popup/Mailjet/php-mailjet-v3-simple.class.php');
        $apiKey = Mage::getStoreConfig('magebird_popup/services/mailjet_key');
        $secretKey = Mage::getStoreConfig('magebird_popup/services/mailjet_secret_key');
        //$doubleOptin = Mage::getStoreConfigFlag('magebird_popup/services/mailjet_double_option');

        $mj = new Mailjet($apiKey, $secretKey);
        $params = array(
            "method" => "POST",
            "ID" => $listId
        );
        $name = $lastName ? $firstName . " " . $lastName : $firstName;
        $contact = array(
            "Email" => $email,
            "Name" => $name,
            "Action" => "addforce"
        );
        $params = array_merge($params, $contact);
        $result = $mj->contactslistManageContact($params);
        $response['success'] = true;
        if (!$result || $result->Count<1) {
            $response['success'] = false;
            $response['msg'] = __("Wrong api/secret key or list id");
        }
        return $response;
    }

    function subscribeMailchimp($listId, $email, $firstName, $lastName, $coupon) {
        require_once(Mage::getBaseDir('lib') . '/magebird/popup/Mailchimp/MailChimp.php');
        $api = new MailChimp(Mage::getStoreConfig('magebird_popup/services/mailchimp_key'));    
        $doubleOptin = Mage::getStoreConfigFlag('magebird_popup/services/mailchimp_double_option');

        $groups = false;
        $groupName = Mage::app()->getRequest()->getParam('groupName');
        $groupValue = Mage::app()->getRequest()->getParam('groupValue');

        $groupIds = array();
        if ($groupName) {
            $result = $api->get("/lists/$listId/interest-categories");
            if(isset($result['categories'])){
              foreach($result['categories'] as $groupCat){
                if($groupCat['title']==$groupName){
                  $groupCatId = $groupCat['id'];
                  $groups = $api->get("/lists/$listId/interest-categories/$groupCatId/interests");
                  foreach($groups['interests'] as $group){
                    if($group['name']==$groupValue || (is_array($groupValue) && in_array($group['name'], $groupValue))){
                      $groupIds[$group['id']] = true;
                    }
                  } 
                  break;
                }
              } 
            }       
        }

        $status = $doubleOptin ? 'pending' : 'subscribed';
        $groups = false;
        if(!$firstName) $firstName = '';
        if(!$lastName) $lastName = '';
        $mergeVar = array(
            'FNAME' => $firstName,
            'LNAME' => $lastName
        );

        $extraFields = Mage::app()->getRequest()->getParam('extra_fields');
        if (is_array($extraFields)) {
            foreach ($extraFields as $field => $value) {
                $mergeVar[$field] = $value;
            }
        }
        $mergeVar['POPUP_COUP'] = $coupon;
        
        $params = array(
            				'email_address' => $email,
            				'merge_fields' => $mergeVar,
            				'status' => $status
            			);
        if($groupIds){
          $params['interests'] = $groupIds;
        }
        $result = $api->post("/lists/$listId/members", $params);
        return $result;
    }

    function subscribeGetResponse($listId, $email, $firstName, $lastName, $coupon) {
        require_once(Mage::getBaseDir('lib') . '/magebird/popup/GetResponse/GetResponseAPI.class.php');
        $api = new GetResponse(Mage::getStoreConfig('magebird_popup/services/getresponse_key'));
        if ($coupon) {
            $add = $api->addContact($listId, $firstName . " " . $lastName, $email, 'standard', 0, array('POPUP_COUPON' => $coupon));
        } else {
            $add = $api->addContact($listId, $firstName . " " . $lastName, $email, 'standard', 0);
        }
        return $add;
    }
    
    function subscribeRemarkety($email, $firstName, $lastName) {
        require_once(Mage::getBaseDir('lib') . '/magebird/popup/Remarkety/Remarkety.php');
        $storeId = Mage::getStoreConfig('magebird_popup/services/remarkety_store_id');
        $sendOptin = Mage::getStoreConfig('magebird_popup/services/remarkety_send_optin');
        $sendOptin = $sendOptin ? 'true' : 'false';
        $remarkety = new Remarkety($storeId);
        $response = $remarkety->subscribe($email, $sendOptin, $firstName, $lastName);
        return $response;
    }       

    function subscribeActiveCampaign($listId, $email, $firstName, $lastName, $coupon) {
        require_once(Mage::getBaseDir('lib') . '/magebird/popup/ActiveCampaign/ActiveCampaign.class.php');
        $key = Mage::getStoreConfig('magebird_popup/services/activecampaign_key');
        $url = Mage::getStoreConfig('magebird_popup/services/activecampaign_url');
        $customField = Mage::app()->getRequest()->getParam('custom_field_name');
        $customFieldValue = Mage::app()->getRequest()->getParam('custom_field_value');
        $formId = Mage::app()->getRequest()->getParam('form_id');
        $ac = new ActiveCampaign($url, $key);
        $contact = array(
            "email" => $email,
            "first_name" => $firstName,
            "last_name" => $lastName,
            "p[{$listId}]" => $listId,
            "status[{$listId}]" => 1, // "Active" status
        );

        if ($customField && $customFieldValue) {
            $contact["field[" . $customField . ",0]"] = $customFieldValue;
        }
        if ($formId)
            $contact['form'] = $formId;

        $contact_sync = $ac->api("contact/add", $contact);
        $response['success'] = true;
    		if(!(int)$contact_sync->success){     
          if($contact_sync->error){
            $response['msg'] = __($contact_sync->error);
          }else{
            $response['msg'] = "An unexpected problem occurred with the API request.";
          }                
    			$response['success'] = false;
    		}  

        return $response;
    }

    function subscribeSendy($listId, $email, $firstName, $coupon) {
        $sendy = Mage::getStoreConfig('magebird_popup/services/enablesendy');
        if ($sendy) {
            require_once(Mage::getBaseDir('lib') . '/magebird/popup/Sendy/SendyPHP.php');
            $apiKey = Mage::getStoreConfig('magebird_popup/services/sendy_key');
            $url = Mage::getStoreConfig('magebird_popup/services/sendy_url');
            $config = array(
                'api_key' => $apiKey, //your API key is available in Settings
                'installation_url' => $url, //Your Sendy installation
                'list_id' => $listId
            );
            $sendy = new SendyPHP($config);
            if ($coupon) {
                $results = $sendy->subscribe(array(
                    'name' => $firstName,
                    'email' => $email,
                    'POPUP_COUPON' => $coupon
                ));
            } else {
                $results = $sendy->subscribe(array(
                    'name' => $firstName,
                    'email' => $email
                ));
            }
        } else {
            return array('status' => false, 'message' => 'Sendy is not enabled. Go to System->Configuration->Popup->Newsletter services to enable it or remove Sendy List Id from Newsletter widget.');
        }
        return $results;
    }

    function subscribeCampaignMonitor($listId, $email, $firstName, $lastName, $coupon) {
        require_once(Mage::getBaseDir('lib') . '/magebird/popup/Campaignmonitor/csrest_subscribers.php');
        $auth = array('api_key' => Mage::getStoreConfig('magebird_popup/services/campaignmonitor_key'));
        $wrap = new CS_REST_Subscribers($listId, $auth);
        $result = $wrap->add(array(
            'EmailAddress' => $email,
            'Name' => $firstName . " " . $lastName,
            'CustomFields' => array(
                array(
                    'Key' => 'POPUP_COUPON',
                    'Value' => $coupon
                )
            ),
            'Resubscribe' => true
        ));

        return $result;
    }

    function subscribeMailerLite($listId, $email) {
        require_once(Mage::getBaseDir('lib') . '/magebird/popup/MailerLite/ML_Subscribers.php');
        $auth = Mage::getStoreConfig('magebird_popup/services/mailerlite_apiKey');

        $wrap = new ML_Subscribers($auth);
        $isSubsribed = $wrap->get($email);
        $isSubsribed = json_decode($isSubsribed, true);

        if (isset($isSubsribed["message"])) {
            $wrap->setId($listId);

            $result = $wrap->add(array(
                'email' => $email,
            ));

            return json_decode($result, true);
        } elseif (isset($isSubsribed["error"])) {
            return $isSubsribed;
        } else {
            $response['isSubsribed'] = __("You are already subscribed");
            return $response;
        }
    }

    function subscribeAweber($listId, $email, $firstName, $lastName) {
        try {
            require_once(Mage::getBaseDir('lib') . '/magebird/popup/AWeber/aweber_api/aweber_api.php');

            $consumerSecret = Mage::getStoreConfig('magebird_popup/services/aweber_consumerKey');
            $consumerKey = Mage::getStoreConfig('magebird_popup/services/aweber_consumerSecret');
            $accToken = Mage::getStoreConfig('magebird_popup/services/aweber_token_key');
            $accSecret = Mage::getStoreConfig('magebird_popup/services/aweber_token_secret');

            $aweber = new AWeberAPI($consumerKey, $consumerSecret);
            $account = $aweber->getAccount($accToken, $accSecret);

            $isSubsribed = $account->findSubscribers(array('email' => $email));
            if ($isSubsribed[0] != null) {
                return array("msg" => __("You are already subscribed"), "error" => true);
            }

            $account_id = $account->id;
            $listURL = "/accounts/{$account_id}/lists/{$listId}";
            $list = $account->loadFromUrl($listURL);
            $params = array(
                "email" => $email,
                "name" => $firstName . " " . $lastName
            );
            $subscribers = $list->subscribers;
            $subscribers->create($params);

            return true;
        } catch (Exception $exc) {
            return array("error" => true, "msg" => $exc->message);
        }
    }

    function subscribeEmma($email, $emmaGroupIds, $firstName, $lastName) {
        require_once(Mage::getBaseDir('lib') . '/magebird/popup/Emma/Emma.php');
        $publicApiKey = Mage::getStoreConfig('magebird_popup/services/emma_public_key');
        $privateApiKey = Mage::getStoreConfig('magebird_popup/services/emma_private_key');
        $sendOptin = Mage::getStoreConfig('magebird_popup/services/emma_send_optin');
        $sendOptin = $sendOptin ? true : false;
        $accountId = Mage::getStoreConfig('magebird_popup/services/emma_account_id');
        $emma = new Emma($accountId, $publicApiKey, $privateApiKey);
        $response = $emma->subscribe($emmaGroupIds, $email, array("first_name" => $firstName, "last_name" => $lastName), $sendOptin);
        return $response;
    }

    function subscribeDotmailer($email, $addressId, $firstName, $lastName) {
        require_once(Mage::getBaseDir('lib') . '/magebird/popup/dotmailer/Dotmailer.php');
        $apiEmail = Mage::getStoreConfig('magebird_popup/services/dotmailer_email');
        $password = Mage::getStoreConfig('magebird_popup/services/dotmailer_password');

        $data = array(
            'Email' => $email,
            'EmailType' => 'Html',
            'dataFields' => array(
                array(
                    'Key' => 'FIRSTNAME',
                    'Value' => $firstName),
                array(
                    'Key' => 'FULLNAME',
                    'Value' => $firstName . " " . $lastName),
                array(
                    'Key' => 'LASTNAME',
                    'Value' => $lastName),
            )
        );
        $dotmailer = new dotmailer($apiEmail, $password);
        $response = $dotmailer->subscribe($addressId, $data);
        return $response;
    }

    function subscribeNuevomailer($email, $listIds, $firstName, $lastName, $templateId) {
        require_once(Mage::getBaseDir('lib') . '/magebird/popup/nuevomailer/api.php');
        $url = Mage::getStoreConfig('magebird_popup/services/nuevomailer_url');
        $apiKey = Mage::getStoreConfig('magebird_popup/services/nuevomailer_api_key');
        $api = new api($url, $apiKey);
        $optin = Mage::getStoreConfig('magebird_popup/services/nuevomailer_optin');
        $optin = $optin ? -1 : 0;
        $response = $api->subscribe($email, $listIds, $firstName, $lastName, $optin, $templateId);

        return true;
    }

    function subscribeIconneqt($iconneqtListId, $email) {
        require_once Mage::getBaseDir('lib') . '/magebird/popup/iconneqt/src/Iconneqt/Api/Rest/Iconneqt.php';
        $iconnectUrl = Mage::getStoreConfig('magebird_popup/services/iconneqt_url');
        $iconnectUser = Mage::getStoreConfig('magebird_popup/services/iconneqt_username');
        $iconnectPassword = Mage::getStoreConfig('magebird_popup/services/iconneqt_password');
        $confirmed = Mage::getStoreConfig('magebird_popup/services/iconneqt_confirmed') ? true : false;
        $iconneqt = new Iconneqt\Api\Rest\Iconneqt($iconnectUrl, $iconnectUser, $iconnectPassword);
        $fields = array();
        $fieldsParams = $this->getRequest()->getParams();
        foreach ($fieldsParams as $key => $value) {
            if (is_int($key)) {
                $fields[$key] = $value;
            }
        }
        $subscriber = array(
            'emailaddress' => $email,
            'fields' => $fields,
        );
        if (!$confirmed) {
            $subscriber = array_merge($subscriber, array(
                'confirmed' => false,
                'confirmdate' => null,
                'confirmip' => null,
            ));
        }

        $list = $iconneqt->getList($iconneqtListId);
        if (!$list) {
            $response['success'] = false;
            $response['msg'] = __("Wrong Iconneqt List id or login data");
            return $response;
        }
        if ($list->hasSubscriber($email)) {
            $response['success'] = false;
            $response['msg'] = __("You are already subscribed");
            return $response;
        }

        try {
            $res = $iconneqt->postListSubscriber($iconneqtListId, $subscriber);
            $response['success'] = true;
        } catch (Exception $e) {
            $response['success'] = false;
            $response['msg'] = "Could not add subscriber: {$e->getCode()} {$e->getMessage()}";
        }


        return $response;
    }

    function subscribePhplist($email, $listId) {
        require_once(Mage::getBaseDir('lib') . '/magebird/popup/phplist/restApi.php');
        $confirmed = Mage::getStoreConfig('magebird_popup/services/phplist_confirmed');

        if (!$adminUrl = Mage::getStoreConfig('magebird_popup/services/phplist_url')) {
            return array('status' => 2, 'error' => "Missing phpList url");
        }
        if (!$username = Mage::getStoreConfig('magebird_popup/services/phplist_username')) {
            return array('status' => 2, 'error' => "Missing phpList username");
        }
        if (!$password = Mage::getStoreConfig('magebird_popup/services/phplist_password')) {
            return array('status' => 2, 'error' => "Missing phpList password");
        }
        $config = array('adminUrl' => $adminUrl,
            'username' => $username,
            'password' => $password
        );
        $api = new restApi($config);
        $response = $api->subscribe($email, $listId, $confirmed);
        return $response;
    }

    //delete old emails to prevent table overgrowth
    function cleanOldEmails() {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $table = Mage::getSingleton("core/resource")->getTableName('magebird_popup_subscriber');
        $where = array();
        $ago2months = strtotime("-4 month");
        $where[] = $connection->quoteInto('date_created < ?', $ago2months);
        $connection->delete($table, $where);
    }

    //delete subscriber from table to not get another coupon code again
    function deleteTempSubscriber($email) {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $table = Mage::getSingleton("core/resource")->getTableName('magebird_popup_subscriber');
        $where[] = $connection->quoteInto('subscriber_email = ?', $email);
        $connection->delete($table, $where);
    }

}
