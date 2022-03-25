<?php

// https://mautic.splinth.com/mautic/custom-api/escalations.php

include '/home/199481.cloudwaysapps.com/qzjtfqubrr/public_html/vendor/autoload.php';  

use Mautic\MauticApi;
use Mautic\Auth\ApiAuth;

session_start();

include_once 'config.php';

$settings = array(
    'userName'   => 'admin',             // Create a new user       
    'password'   => 'SPTltd123!@#'              // Make it a secure password
);

$initAuth = new ApiAuth();
$auth = $initAuth->newAuth($settings, 'BasicAuth');

$api = new MauticApi();
$apiUrl   = "https://mautic.splinth.com/mautic/api/";

$contactApi = $api->newApi('contacts', $auth, $apiUrl);
$campaignApi = $api->newApi("campaigns", $auth, $apiUrl);

$toDoCampaignId = 34; // Campaign to add "to-do-process" tag to contact. 

/*
Read Contacts from Active Process Leads
*/
$searchFilter = "segment:active-process-leads";
$contacts = $contactApi->getList($searchFilter,0,$active_lead_limit);

$date_current = new DateTime();

foreach ($contacts['contacts'] as $key => $value) {
	//echo $key. '<br>';
	$hasAnyTags = false;
	//if ($key == 5626){
		
		$contact_fud = $value['fields']['core']['org_date_time']['value'];
		
		$tags = $value['tags'];
		foreach ($tags as $tag){
			//echo $tag['tag'].'<br>';
			if ($tag['tag'] == "to-do-process"){
				$hasAnyTags = true;
			}
			if ($tag['tag'] == "escalated-process"){
				$hasAnyTags = true;
			}
			if ($tag['tag'] == "reminder-process"){
				$hasAnyTags = true;
			}
		}
		
		if (!$hasAnyTags){
			add_todo_tag($key);
		}
		
	//}
}

//************* START - Check Escalations [6:00PM to 6:02 PM]*************
$date_current = new DateTime();
$cdW = $date_current->format('l');
if (trim($cdW) != 'Sunday'){
	$ct = $date_current->format('H:i');
	if ((('11:55' < $ct) && ($ct < '20:30'))){
		include 'process-escalations.php';
	}
}
//************* END - Check Escalations [6:00PM to 6:02 PM]*************

function add_todo_tag($contactId){
	global $campaignApi, $toDoCampaignId;
	
	$response = $campaignApi->addContact($toDoCampaignId, $contactId);
	if (!isset($response['success'])) {
		// handle error
	} else {
		echo "$contactId added to 'to-do-process' tag campaign successfully <br>";
	}
	
}


?>