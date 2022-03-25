<?php

// https://mautic.splinth.com/mautic/custom-api/escalations.php

include '/home/199481.cloudwaysapps.com/qzjtfqubrr/public_html/vendor/autoload.php';  

use Mautic\MauticApi;
use Mautic\Auth\ApiAuth;

session_start();

include_once 'config.php';

$settings = array(
    'userName'   => 'admin',             // Create a new user       
    'password'   => 'pass'              // Make it a secure password
);

$initAuth = new ApiAuth();
$auth = $initAuth->newAuth($settings, 'BasicAuth');

$api = new MauticApi();
$apiUrl   = "https://mautic.splinth.com/mautic/api/";

$contactApi = $api->newApi('contacts', $auth, $apiUrl);
$campaignApi = $api->newApi("campaigns", $auth, $apiUrl);

$campaignId = 26; // Campaign to Add "previous-lead" tag

/*
Read Contacts from Active Leads
*/
$searchFilter = "segment:active-leads";
$contacts = $contactApi->getList($searchFilter,0,$active_lead_limit);

$date_current = new DateTime();

foreach ($contacts['contacts'] as $key => $value) {
	//echo $key. '<br>';
	$hasToDoTag = false;
	$hasReminderTag = false;
	$hasEscalatedTag = false;
	$hasPreviousLeadTag = false;
	$contactId = $key;
	
	//if ($key == 3529){
		
		$contact_fud = $value['fields']['core']['org_date_time']['value'];
		$tags = $value['tags'];
		foreach ($tags as $tag){
			
			if ($tag['tag'] == "to-do"){
				$hasToDoTag = true;
			}
			if ($tag['tag'] == "reminder"){
				$hasReminderTag = true;
			}
			if ($tag['tag'] == "escalated"){
				$hasEscalatedTag = true;
			}
			if ($tag['tag'] == "previous-lead"){
				$hasPreviousLeadTag = true;
			}
		}
		
		if (($hasToDoTag || $hasReminderTag) && !$hasEscalatedTag && !$hasPreviousLeadTag){
			if ($contact_fud == ""){
				//echo "Add to previous-lead Leads";	
				add_previous_lead_tag($key);
			} else {
				// END Code
			}
		} else {
			// END Code
		}
	//}
}

function add_previous_lead_tag($contactId){
	global $campaignApi, $campaignId;
	
	$response = $campaignApi->addContact($campaignId, $contactId);
	if (!isset($response['success'])) {
		// handle error
	} else {
		echo "$contactId added to 'previous-lead' tag campaign successfully <br>";
	}
	
}


?>