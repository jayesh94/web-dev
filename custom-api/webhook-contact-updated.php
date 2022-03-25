<?php

include '/home/199481.cloudwaysapps.com/qzjtfqubrr/public_html/vendor/autoload.php';  

use Mautic\MauticApi;
use Mautic\Auth\ApiAuth;

// ApiAuth->newAuth() will accept an array of Auth settings
$settings = array(
    'userName'   => 'admin',             // Create a new user       
    'password'   => 'SPTltd123!@#'              // Make it a secure password
);

// Initiate the auth object specifying to use BasicAuth
$initAuth = new ApiAuth();
$auth = $initAuth->newAuth($settings, 'BasicAuth');

$api = new MauticApi();
$apiUrl   = "https://mautic.splinth.com/mautic/api/";

$campaignApi = $api->newApi("campaigns", $auth, $apiUrl);

// https://mautic.splinth.com/mautic/custom-api/webhook-contact-updated.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	
	$data = json_decode(file_get_contents("php://input"),true);
	$contact_id = $data['mautic.lead_post_save_update'][0]['lead']['id'];
	$contact_mbu = $data['mautic.lead_post_save_update'][0]['lead']['modifiedByUser'];
	$contact_dm = $data['mautic.lead_post_save_update'][0]['lead']['dateModified'];
	$contact_added = $data['mautic.lead_post_save_update'][0]['lead']['dateAdded'];
	$contact_email = $data['mautic.lead_post_save_update'][0]['lead']['fields']['core']['email']['value'];
	$contact_status = $data['mautic.lead_post_save_update'][0]['lead']['fields']['core']['remarks']['value'];
	$contact_stage = $data['mautic.lead_post_save_update'][0]['lead']['fields']['core']['leads_stage']['value'];
	$contact_code_tag = $data['mautic.lead_post_save_update'][0]['lead']['fields']['core']['status_code']['value'];
	$contact_follow_up_dt = $data['mautic.lead_post_save_update'][0]['lead']['fields']['core']['org_date_time']['value'];
	
	$contact_stage_id = 0;
	
	$contact_dm_first = $contact_dm;
	$date = date_create($contact_dm);
	$contact_dm = date_format($date,"Y-m-d H:i:s");
	
	if (trim($contact_mbu) != ""){ // Whether a USER modified the Contact ELSE the webhook will be called every minute 
	
		//$contact_dm = "testing date TIme";
		//$contact_id = 3597;
		
		$servername = "localhost";
		$uname = "qzjtfqubrr";
		$password = "dGA6UfeEtD";
		$dbname = "qzjtfqubrr";
		
		$con = mysqli_connect($servername, $uname, $password, $dbname) ;
		if (mysqli_connect_errno($con) ) {
			echo "Failed to connect to MySQL: " . mysqli_connect_error() ;
		}
		
		//If the contact is updated then change the Date Last Modified
		$stmt = $con->prepare("UPDATE `leads` SET `date_last_modified` = ? WHERE `id` = ?");
		$stmt->bind_param("si", $contact_dm, $contact_id);
		$stmt->execute();
		if ($stmt->error) {
			echo "FAILURE! " . $stmt->error;
		}
		else {
			echo "Contact modified: ID:$contact_id, Date Modified:$contact_dm, Modified By:$contact_mbu";
		}
		$stmt->close();
		
		//When contact is updated; Check the Lead's Status and then change the System and Form Stage Accordingly
		switch ($contact_status) {
			case "Follow Up":
				$contact_stage_id = 10;
				$contact_stage = "3-Follow Up";
				break;
			case "Documentation":
				$contact_stage_id = 11;
				$contact_stage = "4-Documentation";
				break;
			case "Login":
				$contact_stage_id = 12;
				$contact_stage = "5-Login";
				break;
			case "Sanction":
				$contact_stage_id = 13;
				$contact_stage = "6-Sanction";
				break;
			case "Disbursement":
				$contact_stage_id = 14;
				$contact_stage = "7-Disbursement";
				break;
			default:
				//echo "\n Status is empty";
		}
		
		if ($contact_stage == "2-Attended" && $contact_stage_id == 0){
			$contact_stage_id = 9;
		}
		
		if ($contact_stage_id != 0){
			$stmt = $con->prepare("UPDATE `leads` SET `stage_id` = ?, `leads_stage` = ? WHERE `id` = ?");
			$stmt->bind_param("isi", $contact_stage_id, $contact_stage, $contact_id);
			$stmt->execute();
			if ($stmt->error) {
				echo "FAILURE! " . $stmt->error;
			}
			else {
				//echo " | Stages updated successfully";
			}
			$stmt->close();	
		}
		
		//change status to Attended based on the Code Tag status_code and Follow Up Date Time org_date_time
		if (($contact_follow_up_dt != "" || $contact_code_tag != "") && $contact_stage_id == 0){
			$contact_stage_id = 9;
			$contact_stage = "2-Attended";
			$stmt = $con->prepare("UPDATE `leads` SET `stage_id` = ?, `leads_stage` = ? WHERE `id` = ?");
			$stmt->bind_param("isi", $contact_stage_id, $contact_stage, $contact_id);
			$stmt->execute();
			if ($stmt->error) {
				echo "FAILURE! " . $stmt->error;
			}
			else {
				echo " | Stages updated successfully";
			}
			$stmt->close();	
		}
	
//**************** START - Check if field that was changed by the USER is status_code *****************************
// Update Follow Up Date based on the status_code
		
		$contact_dm = new DateTime($contact_dm_first);
		$contact_dm->sub(new DateInterval('PT330M'));
		
		$df = $contact_dm->format('Y-m-d H:i:s');
		//echo " | $df";
		
		$stmt = $con->prepare("SELECT * FROM `audit_log` WHERE `object_id` = ? AND `user_name` = ? AND `date_added` = ?");
		$stmt->bind_param("iss", $contact_id, $contact_mbu, $df);
		$stmt->execute();
		$result = $stmt->get_result();
		$row = $result->fetch_array(MYSQLI_ASSOC);
		$result->close();
		$stmt->close();
		
		/*
		$data = json_encode($row, true);
		echo $data;
		{"id":16941,"user_id":1,"user_name":"Splinth Technology","bundle":"lead","object":"lead","object_id":3399,"action":"update","details":"a:1:{s:6:\"fields\";a:1:{s:11:\"status_code\";a:2:{i:0;s:12:\"testing 1234\";i:1;s:4:\"1234\";}}}","date_added":"2020-09-19 14:17:49","ip_address":"27.63.12.231"}
		*/
		//echo $row['details'];
		
		if ($contact_follow_up_dt == ""){
			if (strpos($row['details'], '"status_code"') !== false) {
				// YES status_code was changed
				$contact_code_tag = strtolower(trim($contact_code_tag));
				echo $contact_code_tag;
				if ($contact_code_tag == "3p" || $contact_code_tag == "4p"){
					update_follow_up_date_gmt("P1D"); // Add 1 day
				} elseif ($contact_code_tag == "3"){
					update_follow_up_date_gmt("P3D"); // Add 3 days
				}
			}
		}


//**************** END - Check if field that was changed by the USER is status_code *****************************
	
	
	/*
	Check if contact was modified by the user. If YES then remove all the tags.
	*/
	//echo $contact_mbu."...";
	
		$campaignId = 22; // Remove tags "to-do", "escalated", "reminder", and "previous-lead"
		$response = $campaignApi->addContact($campaignId, $contact_id);
		if (!isset($response['success'])) {
			echo " | error removing tags";
		} else {
			echo " | all tags removed successfully";
		}
	}
}

function update_follow_up_date_gmt($interval){
	global $contact_id, $stmt, $con;
	$date_current = new DateTime();
	// $date_current->sub(new DateInterval('PT330M')); // Convert IST to GMT -330 mins (Commenting this out as the date is being Displayed in GMT time itself and only in Edit interface we are seeing the Time in IST. But our importance is for Frontend Display and not for backend)
	$date_current->add(new DateInterval($interval)); // Add interval to time
	
	$dc = $date_current->format('Y-m-d H:i:s');
	
	$stmt = $con->prepare("UPDATE `leads` SET `org_date_time` = ? WHERE `id` = ?");
	$stmt->bind_param("si", $dc, $contact_id);
	$stmt->execute();
	if ($stmt->error) {
		echo "FAILURE! " . $stmt->error;
	}
	else {
		//echo "Follow Up date updated successfully";
	}
	$stmt->close();
}

?>