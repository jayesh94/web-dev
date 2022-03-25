<?php

// https://mautic.splinth.com/mautic/custom-api/webhooks/webhook-fb-offline-api.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	
	$data = json_decode(file_get_contents("php://input"),true);
	
	$contact_id = $data['mautic.lead_post_save_update'][0]['lead']['id'];
	$contact_mbu = $data['mautic.lead_post_save_update'][0]['lead']['modifiedByUser'];
	$contact_source = $data['mautic.lead_post_save_update'][0]['lead']['fields']['core']['f_source']['value'];
	$contact_lead_feedback = $data['mautic.lead_post_save_update'][0]['lead']['fields']['core']['lead_feedback']['value'];
	$contact_numeric_lead_feedback = $data['mautic.lead_post_save_update'][0]['lead']['fields']['core']['numeric_lead_feedback']['value'];
	
	echo "$contact_id <br>";
	echo "$contact_lead_feedback <br>";
	echo "$contact_numeric_lead_feedback <br>";
	if (trim($contact_mbu) != "" && strtolower($contact_source) == "facebook"){ // Whether a USER modified the Contact ELSE the webhook will be called every minute 
		if (trim($contact_lead_feedback) != ""){ // Whether Lead Feedback was updated. 
			// Set the lead_value according to the contact_numeric_lead_feedback
			if (trim($contact_lead_feedback) != "Poor Profile"){
				if (trim($contact_numeric_lead_feedback) == "0" or trim($contact_numeric_lead_feedback) == ""){
					$lead_value = 10;
				} else {
					$lead_value = $contact_numeric_lead_feedback;
				}
				uploadOfflineFacebookConversion($data, $lead_value);
			} else {
				if (!(trim($contact_numeric_lead_feedback) == "0" or trim($contact_numeric_lead_feedback) == "")){
					$lead_value = $contact_numeric_lead_feedback;
					uploadOfflineFacebookConversion($data, $lead_value);
				}
			}
		}
	}
}

function uploadOfflineFacebookConversion($data, $lead_value) {
	
	$contact_id = $data['mautic.lead_post_save_update'][0]['lead']['id'];
	$contact_created_time = $data['mautic.lead_post_save_update'][0]['lead']['fields']['core']['created_time']['value']; 
	$contact_leadgen_ct = $data['mautic.lead_post_save_update'][0]['lead']['fields']['core']['leadgen_ct']['value']; 
	
	$contact_full_name = $data['mautic.lead_post_save_update'][0]['lead']['fields']['core']['full_name']['value'];
	$contact_email = $data['mautic.lead_post_save_update'][0]['lead']['fields']['core']['email']['value'];
	$contact_phone = $data['mautic.lead_post_save_update'][0]['lead']['fields']['core']['phone']['value'];
	$contact_lead_feedback = $data['mautic.lead_post_save_update'][0]['lead']['fields']['core']['lead_feedback']['value'];
	$contact_numeric_lead_feedback = $data['mautic.lead_post_save_update'][0]['lead']['fields']['core']['numeric_lead_feedback']['value'];
	
	// Get UNIX time from the contact_created_time AND set the event_time
	$date = date_create($contact_created_time);
	$contact_ct = date_format($date,"Y-m-d H:i:s");
	$contact_created_time_unix = strtotime($contact_ct);
	
	if (trim($contact_leadgen_ct) != ""){
		$event_time = $contact_leadgen_ct;
	} else {
		$event_time = $contact_created_time_unix;
	}
	
	// Check the email
	$word = "nomail";
	if(strpos($contact_email, $word) !== false){
		//echo "Word Found!";
		$contact_email = "";
	}
	
	// Get contact_first_name & contact_last_name from contact_full_name
	$contact_name = extractName($contact_full_name);
	$contact_first_name = $contact_name[0];
	$contact_last_name = $contact_name[1];
	
	$fb_access_token = "EAAD1s4AdZCJgBACGcgCRcZCjn5eZB5h3RNZC8EOWL1hQZAlL0jHMglwtsTBzb2UPCBV2Dtwq4hvqUlg59NUPCbJkzWSGDkEk22Nnc5hf7rinQHXb0HCWUlLZAHhw6aEjmZAExo6SoY1EpANcynbu5JLRwUIOT9E1AZB4H2UsPA0Y0xWCKqcWuECZB";

	$offline_event_set_id = 314300759836161;
	
	// Set Unique update tag
	$date = new DateTime(); 
	$date->format('Y-m-d_H:i:s.u');
	$upload_tag = $date->format('Y-m-d_H:i:s.u')."-".$contact_id;
	
	//$email = "asodekar.atul@rediffmail.com";
	//$phone = "+919860309670";
	//$first_name = "Atul";
	//$last_name = "Asodekar";
	//$country = "India";
	//$order_date_time = 1603243035;
	//$currency = "INR";
	//$event_name = "Lead";
	//$lead_value = 2700;
	
	$country = "India";
	$currency = "INR";
	$event_name = "Lead";
	
	echo "lead_value:$lead_value<br>";
	
	$data=array();
	$data["match_keys"]["email"]=hash("sha256",$contact_email);
	$data["match_keys"]["phone"]=hash("sha256",$contact_phone);
	$data["match_keys"]["fn"]=hash("sha256",$contact_first_name);
	$data["match_keys"]["ln"]=hash("sha256",$contact_last_name);
	$data["match_keys"]["country"]=hash("sha256",$country);
	$data["event_time"] = $event_time;
	$data["event_name"] = $event_name;
	$data["order_id"] = $contact_id; // For Offline Event Data Deduplication
	$data["item_number"] = $contact_id; // For Offline Event Data Deduplication
	$data["custom_data"]["lead_feedback"] = trim($contact_lead_feedback); 
	$data["currency"] = $currency;
	$data["value"] = $lead_value;
	
	
	// LEAD DATA
	$data_json = json_encode(array($data));
	$fields = array();
	$fields['access_token'] = $fb_access_token;
	$fields['upload_tag'] = $upload_tag; 
	$fields['data'] = $data_json;
	//$fields['data'] = array($data);
	
	// Add the Contect to Log file
	file_put_contents('logs.txt', PHP_EOL , FILE_APPEND | LOCK_EX);
	file_put_contents('logs.txt', $contact_id.PHP_EOL , FILE_APPEND | LOCK_EX);
	//file_put_contents('logs.txt', $data_json.PHP_EOL , FILE_APPEND | LOCK_EX);
	file_put_contents('logs.txt', json_encode($fields).PHP_EOL , FILE_APPEND | LOCK_EX);
	 
	$ch = curl_init();
	curl_setopt_array($ch, array(
	// Replace with your offline_event_set_id
	CURLOPT_URL => "https://graph.facebook.com/v8.0/".$offline_event_set_id."/events", 
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => "",
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 30,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => "POST",
	CURLOPT_POSTFIELDS =>  http_build_query($fields),
	CURLOPT_HTTPHEADER => array(
		"cache-control: no-cache",
		"Accept: application/json"  ),
	));
	
	$result = curl_exec($ch);
	echo "\nResult encode";
	echo ($result);
	if (curl_errno($ch)) {
		echo 'Error:' . curl_error($ch);
	}
	curl_close ($ch);
}

function extractName($name)
{
  // Common/expected prefixes.
  $prefix_list = array(
    'mr',
    'mrs',
    'miss',
    'ms',
    'dr',
    'doctor',
  );

  // Common/expected suffixes.
  $suffix_list = array(
    'md',
    'phd',
    'jr',
    'sr',
    'III',
  );

  $parts = explode(' ', $name);

  // Grab the first name in the string.
  do
  {
    $first_name = array_shift($parts);
  } while ($first_name && in_array(str_replace('.', '', strtolower($first_name)), $prefix_list));

  // If the first name ends with a comma it is actually the last name. Adjust.
  if (strpos($first_name, ',') === (strlen($first_name) - 1))
  {
    $last_name = substr($first_name, 0, strlen($first_name) - 1);
    $first_name = array_shift($parts);

    // Only want the middle initial so grab the next text in the array.
    $middle_name = array_shift($parts);

    // If the text is a suffix clear the middle name.
    if (in_array(str_replace('.', '', strtolower($middle_name)), $suffix_list))
    {
      $middle_name = '';
    }
  }
  else
  {
    // Retrieve the last name if not the leading value.
    do
    {
      $last_name = array_pop($parts);
    } while ($last_name && in_array(str_replace('.', '', strtolower($last_name)), $suffix_list));

    // Only want the middle initial so grab the next text in the array.
    $middle_name = array_pop($parts);
  }


  return array($first_name, $last_name, substr($middle_name, 0, 1));
}

?>