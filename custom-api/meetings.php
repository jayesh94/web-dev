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

$campaignId = 27; // Campaign to Add meeting mailing campaign

/*
Read Contacts from Active Leads
*/
$searchFilter = "segment:active-leads";
$contacts = $contactApi->getList($searchFilter,0,$active_lead_limit);

$date_current = new DateTime();

foreach ($contacts['contacts'] as $key => $value) {
	
	$contactId = $key;
	
	//if ($key == 3339){
		$contact_status = $value['fields']['core']['remarks']['value'];
		$contact_fud = $value['fields']['core']['org_date_time']['value'];
		$date_fud = new DateTime($contact_fud);
		$dc = $date_current->format('Y-m-d');
		$df = $date_fud->format('Y-m-d');
		if (($dc == $df) && (trim($contact_status == 'Meeting'))){
			//echo "Add to to-do Leads";
			add_contact_to_meeting_camp($key);
		} else {
			// End Code
		}
	//}
}

function add_contact_to_meeting_camp($contactId){
	global $campaignApi, $campaignId;
	
	$response = $campaignApi->addContact($campaignId, $contactId);
	if (!isset($response['success'])) {
		// handle error
	} else {
		echo "$contactId added to Meeting emails campaign successfully <br>";
	}
	
}


?>