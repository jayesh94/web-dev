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

$campaignId = 38; // Campaign to Add appointment mailing campaign

/*
Read Contacts from Active Leads
*/
$searchFilter = "segment:active-leads";
$contacts = $contactApi->getList($searchFilter,0,$active_lead_limit);

$date_current = new DateTime();

foreach ($contacts['contacts'] as $key => $value) {
	
	$hasAptTags = false;
	
	$contactId = $key;
	
	$tags = $value['tags'];
	foreach ($tags as $tag){
		if ($tag['tag'] == "appointment"){
			$hasAptTags = true;
		}
	}
	
	if (!$hasAptTags){
		$contact_apt = $value['fields']['core']['appointment_date_time']['value'];
		$dbtimestamp = strtotime($contact_apt);
		if ($dbtimestamp > time() && $dbtimestamp - time() < 15 * 60) {
			add_contact_to_apt_camp($key);
		}
	}
	
}

function add_contact_to_apt_camp($contactId){
	global $campaignApi, $campaignId;
	
	$response = $campaignApi->addContact($campaignId, $contactId);
	if (!isset($response['success'])) {
		// handle error
	} else {
		echo "$contactId added to upcoming appointment emails campaign successfully <br>";
	}
	
}


?>