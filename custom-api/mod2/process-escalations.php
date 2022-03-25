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

$campaignIdEsc = 33; // Campaign to add "escalated-process" tag to contact. 
$campaignIdEscMail = 36; // Campaign to add "escalated-process" tag to contact and send Escalation email ONLY to the owner. 
$campaignIdRem = 32; // Campaign to add "reminder-process" tag to contact. 

/*
Read Contacts from Active Leads
*/
$searchFilter = "segment:active-process-leads";
$contacts = $contactApi->getList($searchFilter,0,$active_lead_limit);

$date_current = new DateTime();
$contact_fud = '';
$contact_status = '';

$nextStageLoginQuery = false;
$nextStageProcessQuery = false;
$nextStageLegalValuation = false;
$hasReminderTag = false;
$contactId = 136;
$contactData = "";

foreach ($contacts['contacts'] as $key => $value) {
	//echo $key. '<br>';
	$hasToDoTag = false;
	$hasReminderTag = false;
	$hasEscalatedTag = false;
	$hasPreviousLeadTag = false;
	$contactId = $key;
	$contactData = $value;
	
	$nextStageSentToBank = false; // If yes then also check for reminder in 1 day.
	$nextStageLoginQuery = false; // If yes then also check for reminder in 1 day.
	$nextStageProcessQuery = false; // If yes then only add to escalation bucket but no email.
	$nextStageLegalValuation = false; // If yes then add to escalation bucket and send email ONLY to the owner.
	
	//if ($key == 5626){
		
		$contact_da = $value['dateAdded'];
		$contact_fud = $value['fields']['core']['org_date_time']['value'];
		$contact_status = $value['fields']['core']['remarks']['value'];
		
		$contact_documentation_date = $value['fields']['social']['documentation_date']['value'];
		$contact_sb_date = $value['fields']['social']['sb_date']['value'];
		$contact_query_received = $value['fields']['social']['query_received']['value'];
		$contact_login_date = $value['fields']['social']['login_date']['value'];
		$contact_bank_reference_number = $value['fields']['social']['bank_reference_number']['value'];
		$contact_verified = $value['fields']['social']['verified']['value'];
		$contact_lv_query = $value['fields']['social']['lv_query']['value'];
		$contact_process_query = $value['fields']['social']['process_query']['value'];
		
		$tags = $value['tags'];
		foreach ($tags as $tag){
			
			if ($tag['tag'] == "to-do-process"){
				$hasToDoTag = true;
			}
			if ($tag['tag'] == "reminder-process"){
				$hasReminderTag = true;
			}
			if ($tag['tag'] == "escalated-process"){
				$hasEscalatedTag = true;
			}
		}
		
		//************************** START *********************************
		if (($hasToDoTag || $hasReminderTag) && !$hasEscalatedTag) {
			// ************* to-do Lead ************
			
			echo $contact_status;
			//Check the Lead's Status and then get the input for logic accordingly
			switch ($contact_status) {
				case "Documentation":
					$nextStageSentToBank = true;
					$escalation_threshold = 7; //7 days till next stage - Sent to Bank
					checkEscalation($contact_documentation_date, $escalation_threshold, $contact_sb_date); // Internal Escalation
					break;
				case "Sent to Bank":
					$nextStageLoginQuery = true;
					$escalation_threshold = 4; //4 days till next stage - Sent to Bank Reminder/Escalate
					$reminder_threshold = 2; // For reminder
					checkReminderEscalation($contact_sb_date, $reminder_threshold, $escalation_threshold, $contact_query_received);
					break;
				case "Login Query":
					$escalation_threshold = 1; //1 days till next stage - login 
					break;
				case "Login":
					$escalation_threshold = 4;
					break;
				case "Verification":
					$escalation_threshold = 5;
					$reminder_threshold = 3; // For reminder
					$nextStageLegalValuation = true;
					checkReminderEscalation($contact_login_date, $reminder_threshold, $escalation_threshold, $contact_lv_query);
					break;
				case "Legal & Valuation":
					$escalation_threshold = 10;
					$reminder_threshold = 8; // For reminder
					$nextStageProcessQuery = true; // credit query
					checkReminderEscalation($contact_login_date, $reminder_threshold, $escalation_threshold, $contact_process_query);
					break;
				case "Process Query":
					$contact_stage_id = 19;
					$contact_stage = "10-Process Query";
					break;
				case "Sanction":
					$contact_stage_id = 13;
					$contact_stage = "11-Sanction";
					break;
				case "Disbursement":
					$contact_stage_id = 14;
					$contact_stage = "12-Disbursement";
					break;
				default:
					//echo "\n Status is empty";
			}
		}
		//************************** END *********************************
	//}
}

function checkReminderEscalation($referenceDate, $reminder_threshold, $escalation_threshold, $fieldToCheck){
	global $hasReminderTag;
	if ($referenceDate != ""){	
		if (!$hasReminderTag){
			if (isTimeElapsed($referenceDate, $reminder_threshold)){
				if ($fieldToCheck == ""){
					add_reminder_tag();
					send_reminder_escalation_emails();
					echo "Add lead to reminder and send emails";
				}
			}
		} else {
			if (isTimeElapsed($referenceDate, $escalation_threshold)){
				if ($fieldToCheck == ""){
					add_escalated_tag();
					send_reminder_escalation_emails();
					echo "Add lead to escalated and send emails";
				}
			}
		}
	}
}

function checkEscalation($referenceDate, $escalation_threshold, $fieldToCheck){
	if ($referenceDate != ""){	
		if (isTimeElapsed($referenceDate, $escalation_threshold)){
			if ($fieldToCheck == ""){
				add_escalated_tag_and_mail();
				echo "escalate the lead and send email ONLY to the owner";
			}
		}
	}
}

/**
function checkEscalation($referenceDate, $threshold, $fieldToCheck){
	global $contact_status, $nextStageLoginQuery, $nextStageProcessQuery, $nextStageSendToBank, $hasReminderTag;
	if ($referenceDate != ""){	
		if ($nextStageLoginQuery && !$hasReminderTag){
			if (isTimeElapsed($referenceDate, 1)){
				if ($fieldToCheck == ""){
					add_reminder_tag();
					# Send emails to both owner and partner
					send_reminder_emails();
					echo "Add lead to reminder and send emails";
				}
			}
		} else {
			if (isTimeElapsed($referenceDate, $threshold)){
				if ($fieldToCheck == ""){
					if ($nextStageProcessQuery){
						echo "add the lead to escalated bucket but no emails";
						add_escalated_tag();
					} elseif ($nextStageSendToBank) {
						add_escalated_tag_and_mail();
						echo "escalate the lead and send email ONLY to the owner";
					} else {
						echo "escalate the lead and send emails";
						add_escalated_tag();
						send_escalation_emails();
						# Send emails to both owner and partner
					}
				}
			}
		}
	}
}
**/

function send_reminder_escalation_emails(){
	global $contactData, $nextStageLoginQuery, $nextStageLegalValuation, $nextStageProcessQuery;
		
	include 'mail/process-mails.php';
}

function add_escalated_tag_and_mail(){
	global $campaignApi, $campaignIdEscMail, $contactId;
	
	$response = $campaignApi->addContact($campaignIdEscMail, $contactId);
	if (!isset($response['success'])) {
		// handle error
	} else {
		echo "$contactId added to 'escalated-process' tag campaign successfully <br>";
	}
}

function add_escalated_tag(){
	global $campaignApi, $campaignIdEsc, $contactId;
	
	$response = $campaignApi->addContact($campaignIdEsc, $contactId);
	if (!isset($response['success'])) {
		// handle error
	} else {
		echo "$contactId added to 'escalated-process' tag campaign successfully <br>";
	}
}

function add_reminder_tag(){
	global $campaignApi, $campaignIdRem, $contactId;
	
	$response = $campaignApi->addContact($campaignIdRem, $contactId);
	if (!isset($response['success'])) {
		// handle error
	} else {
		echo "$contactId added to 'reminder-process' tag campaign successfully <br>";
	}
}

function isTimeElapsed($referenceDate, $threshold){
	global $contactId;
	date_default_timezone_set("Asia/Kolkata");
	$date1 = date_create(date("Y-m-d"));
	echo "<br> $contactId <br>";
	echo date("Y-m-d")."<br>";
	$date2 = date_create($referenceDate);
	echo $date2->format('Y-m-d H:i:s')."<br>";
	$diff=date_diff($date1,$date2);
	$days = $diff->format("%d");
	echo "$days <br>";
	if ($days >= $threshold){
		return true;
	} else {
		return false;
	}
}

?>