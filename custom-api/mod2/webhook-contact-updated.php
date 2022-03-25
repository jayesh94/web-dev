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
	
	
	//addToLog(file_get_contents("php://input"));
	
	$data = json_decode(file_get_contents("php://input"),true);
	$contact_id = $data['mautic.lead_post_save_update'][0]['lead']['id'];
	$contact_mbu = $data['mautic.lead_post_save_update'][0]['lead']['modifiedByUser'];
	$contact_dm = $data['mautic.lead_post_save_update'][0]['lead']['dateModified'];
	$contact_added = $data['mautic.lead_post_save_update'][0]['lead']['dateAdded'];
	$contact_doc_date = $data['mautic.lead_post_save_update'][0]['lead']['fields']['social']['documentation_date']['value'];
	
	
	$contact_status = $data['mautic.lead_post_save_update'][0]['lead']['fields']['core']['remarks']['value'];
	$contact_stage = $data['mautic.lead_post_save_update'][0]['lead']['fields']['core']['leads_stage']['value'];
	$contact_code_tag = $data['mautic.lead_post_save_update'][0]['lead']['fields']['core']['status_code']['value'];
	$contact_follow_up_dt = $data['mautic.lead_post_save_update'][0]['lead']['fields']['core']['org_date_time']['value'];
	
	$contact_stage_id = 0;
	
	$contact_dm_first = $contact_dm;
	$date = date_create($contact_dm);
	$contact_dm = date_format($date,"Y-m-d H:i:s");
	
	if (trim($contact_mbu) != ""){ // Whether a USER modified the Contact ELSE the webhook will be called every minute 
		
		if (trim($contact_doc_date) == ""){
			
			$servername = "localhost";
			$uname = "qzjtfqubrr";
			$password = "dGA6UfeEtD";
			$dbname = "qzjtfqubrr";
			
			$con = mysqli_connect($servername, $uname, $password, $dbname) ;
			if (mysqli_connect_errno($con) ) {
				echo "Failed to connect to MySQL: " . mysqli_connect_error() ;
			}
		
			//**************** START - Check if field that was changed by the USER is remarks *****************************
			// Alos check whether the status was changed to "Documentation"
			// Update Follow Up Date based on the remarks
				
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
			{"id":34127,"user_id":1,"user_name":"Splinth Technology","bundle":"lead","object":"lead","object_id":136,"action":"update","details":"a:1:{s:6:\"fields\";a:1:{s:7:\"remarks\";a:2:{i:0;s:5:\"Login\";i:1;s:13:\"Documentation\";}}}","date_added":"2020-11-11 13:23:44","ip_address":"223.189.27.109"}
			*/
			//echo $row['details'];
			
			//addToLog(json_encode($row, true));
			
			if (strpos($row['details'], '"remarks"') !== false) {
				// YES remarks was changed
				if (strpos($row['details'], '"Documentation"') !== false) {
				// YES remarks was changed to Documentation
				
					$date_current = new DateTime();
					// $date_current->sub(new DateInterval('PT330M')); // Convert IST to GMT -330 mins (Commenting this out as the date is being Displayed in GMT time itself and only in Edit interface we are seeing the Time in IST. But our importance is for Frontend Display and not for backend)
					//$date_current->add(new DateInterval($interval)); // Add interval to time
					
					$dc = $date_current->format('Y-m-d');
					
					$stmt = $con->prepare("UPDATE `leads` SET `documentation_date` = ? WHERE `id` = ?");
					$stmt->bind_param("si", $dc, $contact_id);
					$stmt->execute();
					if ($stmt->error) {
						echo "FAILURE! " . $stmt->error;
					}
					else {
						//echo "Follow Up date updated successfully";
					}
					$stmt->close();
					
					
					$campaignId = 31; // Add tag "process" to add the lead to Active Process Lead
					$response = $campaignApi->addContact($campaignId, $contact_id);
					if (!isset($response['success'])) {
						echo " | error adding tag";
					} else {
						echo " | process tag added successfully";
					}
				}
			}
			
			//**************** END - Check if field that was changed by the USER is remarks *****************************
		}
		
		/*
		Check if contact was modified by the user. If YES then remove all the tags.
		*/
	
		$campaignId = 35; // If tag "process"; Remove tags "to-do-process", "escalated-process" and "reminder-process"
		$response = $campaignApi->addContact($campaignId, $contact_id);
		if (!isset($response['success'])) {
			echo " | error removing tags";
		} else {
			echo " | all tags removed successfully";
		}
	}
}

function addToLog($data){
	// Add the Contect to Log file
	file_put_contents('logs.txt', PHP_EOL , FILE_APPEND | LOCK_EX);
	file_put_contents('logs.txt', $data.PHP_EOL , FILE_APPEND | LOCK_EX);
}

?>