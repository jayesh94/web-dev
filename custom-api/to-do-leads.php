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

$campaignId = 23; // Campaign to add "to-do" tag to contact. 

/*
Read Contacts from Active Leads
*/
$searchFilter = "segment:active-leads";
$contacts = $contactApi->getList($searchFilter,0,$active_lead_limit);

$date_current = new DateTime();

foreach ($contacts['contacts'] as $key => $value) {
	//echo $key. '<br>';
	$hasAnyTags = false;
	//if ($key == 3529){
		
		$contact_fud = $value['fields']['core']['org_date_time']['value'];
		$tags = $value['tags'];
		foreach ($tags as $tag){
			//echo $tag['tag'].'<br>';
			if ($tag['tag'] == "to-do"){
				$hasAnyTags = true;
			}
			if ($tag['tag'] == "escalated"){
				$hasAnyTags = true;
			}
			if ($tag['tag'] == "reminder"){
				$hasAnyTags = true;
			}
			if ($tag['tag'] == "previous-lead"){
				$hasAnyTags = true;
			}
		}
		
		if (!$hasAnyTags){
			if ($contact_fud == ""){
				//echo "Add to to-do Leads";	
				add_todo_tag($key);
				
			} else {
				$date_fud = new DateTime($contact_fud);
				$dc = $date_current->format('Y-m-d');
				$df = $date_fud->format('Y-m-d');
				// $date_fud->add(new DateInterval('PT330M')); // Convert GMT to IST - No need as saving FUD in IST Only
				
				if ($dc == $df){
					//echo "Add to to-do Leads";
					add_todo_tag($key);
				} else {
					// End Code
				}
			}
		}
		
	//}
}

//************* START - Check Escalations [11AM to 7 PM]*************
$date_current = new DateTime();
$cdW = $date_current->format('l');
if (trim($cdW) != 'Sunday'){
	$ct = $date_current->format('H:i');
	if ((('11:00' < $ct) && ($ct < '19:00'))){
		include 'escalations.php';
	}
}
//************* END - Check Escalations [11AM to 7 PM]*************

function add_todo_tag($contactId){
	global $campaignApi, $campaignId;
	
	$response = $campaignApi->addContact($campaignId, $contactId);
	if (!isset($response['success'])) {
		// handle error
	} else {
		echo "$contactId added to 'to-do' tag campaign successfully <br>";
	}
	
}


?>