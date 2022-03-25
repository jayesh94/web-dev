<?php

// https://mautic.splinth.com/mautic/custom-api/escalations.php
// Adding contacts to escalations and reminders
	
include '/home/199481.cloudwaysapps.com/qzjtfqubrr/public_html/vendor/autoload.php';  

use Mautic\MauticApi;
use Mautic\Auth\ApiAuth;

include_once 'config.php';

// ApiAuth->newAuth() will accept an array of Auth settings
$settings = array(
    'userName'   => 'admin',             // Create a new user       
    'password'   => 'pass'              // Make it a secure password
);

// Initiate the auth object specifying to use BasicAuth
$initAuth = new ApiAuth();
$auth = $initAuth->newAuth($settings, 'BasicAuth');

// Nothing else to do ... It's ready to use.
// Just pass the auth object to the API context you are creating.

$api = new MauticApi();
$apiUrl   = "https://mautic.splinth.com/mautic/api/";

$contactApi = $api->newApi('contacts', $auth, $apiUrl);
$segmentApi = $api->newApi("segments", $auth, $apiUrl);
$campaignApi = $api->newApi("campaigns", $auth, $apiUrl);

$campaignIdEsc = 17; // Campaign to add "escalated" tag to contact. 
$campaignIdRem = 25; // Campaign to add "reminder" tag to contact. 

/*
Read Contacts from Active Leads
*/
$searchFilter = "segment:active-leads";
$contacts = $contactApi->getList($searchFilter,0,$active_lead_limit);

$date_current = new DateTime();
$contact_fud = '';

foreach ($contacts['contacts'] as $key => $value) {
	//echo $key. '<br>';
	$hasToDoTag = false;
	$hasReminderTag = false;
	$hasEscalatedTag = false;
	$hasPreviousLeadTag = false;
	$contactId = $key;
	
	//if ($key == 3544){
		
		$contact_da = $value['dateAdded'];
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
		
		//************************** START *********************************
		if (($hasToDoTag || $hasReminderTag) && !$hasEscalatedTag && !$hasPreviousLeadTag) {
			// ************* to-do Lead ************
			if ($contact_fud == ""){ // NEW Lead
				// ************* New Lead ************
				// IF CT - DA > 1hr
				if (gl_ct_2hrs_1hrs($contact_da, 'ct', 'one')) {
					if ($hasReminderTag){
						if (gl_ct_2hrs_1hrs($contact_da, 'ct', 'two')) {
							add_escalated_tag_send_email($contactId);
						} else {
							// END Code
						}
					} else {
						add_reminder_tag($contactId);
					}
				} else {
					// END Code
				}
				// ************* New Lead ************
				
			} else {
				// ************* Old Lead ************
				//IF FUD - CT > 1hr & NOT "reminder"
				if (gl_ct_2hrs_1hrs($contact_fud, 'main', 'one')) {
					// END Code
				} else {
					if ($hasReminderTag){
						//IF FUD > CT
						if (is_fud_gt_ct($contact_fud)){
							add_escalated_tag_send_email($contactId);
						} else {
							// END Code
						}
					} else {
						add_reminder_tag($contactId);
					}
				}
				// ************* Old Lead ************
			}
			// ************* to-do Lead ************
		} elseif (($hasToDoTag || $hasReminderTag) && !$hasEscalatedTag) {
			// ************* previous-lead Lead ************
			//IF today 6PM - CT > 1 hr
			$date_current = new DateTime();
			$dc = $date_current->format('Y-m-d');
			$today_18 = $dc." 18:00:00";
			if (gl_ct_2hrs_1hrs($today_18, 'main', 'one')) {
				// END Code
			} else {
				if ($hasReminderTag){
					//IF FUD > CT
					if (is_fud_gt_ct($today_18)){
						add_escalated_tag_send_email($contactId);
					} else {
						// END Code
					}
				} else {
					add_reminder_tag($contactId);
				}
			}
			// ************* previous-lead Lead ************
		}
		//************************** END *********************************
	//}
}

function add_reminder_tag($contactId){
	global $campaignApi, $campaignIdRem;
	
	$response = $campaignApi->addContact($campaignIdRem, $contactId);
	if (!isset($response['success'])) {
		// handle error
	} else {
		echo "$contactId added to 'reminder' tag campaign successfully <br>";
	}
}

function add_escalated_tag_send_email($contactId){
	global $campaignApi, $campaignIdEsc, $contact_fud;
	
	$date_current = new DateTime();
	$ct = $date_current->format('H:i');
	
	if (!(('10:00' < $ct) && ($ct < '12:00'))){
		$response = $campaignApi->addContact($campaignIdEsc, $contactId);
		if (!isset($response['success'])) {
			// handle error
		} else {
			echo "$contactId added to 'escalated' tag campaign successfully <br>";
		}
	} elseif ($contact_fud != ""){
		$response = $campaignApi->addContact($campaignIdEsc, $contactId);
		if (!isset($response['success'])) {
			// handle error
		} else {
			echo "$contactId added to 'escalated' tag campaign successfully <br>";
		}
	}
}

/*
Greater = true
Less = false
*/
function gl_ct_2hrs_1hrs($mainDateTime, $cOrm, $oneOrtwo) {
	
    $time_c = strtotime("now Asia/Kolkata");
	$time_main = strtotime($mainDateTime);
    
    echo $time_main."<br>";
    echo date('c',$time_main)."<br>"; // ISO 8601 format
    echo $time_c."<br>";
    echo date('c',$time_c)."<br><br>";
    
	$interval = 1*60*60; // Default
	
	if ($oneOrtwo == "one"){
		$interval = 1*60*60; // 1 hours
	} elseif ($oneOrtwo == "two"){
		$interval = 2*60*60; // 2 hours
	}
	
	if ($cOrm == 'ct'){
		$diff = $time_c - $time_main;
		echo "$diff <br>";
		if ($time_c - $time_main > $interval){
			echo "Current time is $interval hours Greater than $mainDateTime.<br>";
			return true;
		} else {
			echo "Current time is NOT $interval hours Greater than $mainDateTime.<br>";
			return false;
		}
	} elseif ($cOrm == 'main'){
		$diff = $time_main - $time_c;
		echo "$diff <br>";
		if ($time_main - $time_c > $interval){
			echo "$mainDateTime is $interval hours Greater than Current time.<br>";
			return true;
		} else {
			echo "$mainDateTime is NOT $interval hours Greater than Current time.<br>";
			return false;
		}
	}
}

function is_fud_gt_ct($mainDateTime){
	$time_c = strtotime("now Asia/Kolkata");
	$time_main = strtotime($mainDateTime);
    
    echo $time_main."<br>";
    echo date('c',$time_main)."<br>"; // ISO 8601 format
    echo $time_c."<br>";
    echo date('c',$time_c)."<br><br>";
	
	if ($time_c > $time_main){
		return true;
	} else {
		return false;
	}
}

?>