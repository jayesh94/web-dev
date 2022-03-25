<?php

// https://mautic.splinth.com/mautic/custom-api/custom.php

//$url = "https://graph.facebook.com/v8.0/314300759836161/events?access_token=EAAD1s4AdZCJgBACGcgCRcZCjn5eZB5h3RNZC8EOWL1hQZAlL0jHMglwtsTBzb2UPCBV2Dtwq4hvqUlg59NUPCbJkzWSGDkEk22Nnc5hf7rinQHXb0HCWUlLZAHhw6aEjmZAExo6SoY1EpANcynbu5JLRwUIOT9E1AZB4H2UsPA0Y0xWCKqcWuECZB";

$date = new DateTime(); 
echo $date->format('Y-m-d_H:i:s.u');

/*
$contact_dm = "October 22, 2020, 5:07:50 pm IST";
$date = date_create($contact_dm);
$contact_dm = date_format($date,"Y-m-d H:i:s");
echo "<br> $contact_dm <br>";
echo strtotime($contact_dm);

echo "<br>";
print_r(extractName("neha Ganesh doiphode"));
echo "<br>";
echo extractName("neha Ganesh doiphode")[0];
echo "<br>";
echo extractName("neha Ganesh doiphode")[1];
echo "<br> 2 <br> ";
print_r(extractName("DR. neha 2mid Ganesh doiphode"));
echo "<br>";
echo extractName("neha 2mid Ganesh doiphode")[0];
echo "<br>";
echo extractName("neha 2mid Ganesh doiphode")[1];
echo "<br>";

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
*/


$fb_access_token = "EAAD1s4AdZCJgBACGcgCRcZCjn5eZB5h3RNZC8EOWL1hQZAlL0jHMglwtsTBzb2UPCBV2Dtwq4hvqUlg59NUPCbJkzWSGDkEk22Nnc5hf7rinQHXb0HCWUlLZAHhw6aEjmZAExo6SoY1EpANcynbu5JLRwUIOT9E1AZB4H2UsPA0Y0xWCKqcWuECZB";

$offline_event_set_id = 314300759836161;
$upload_tag = "";

$email = "asodekar.atul@rediffmail.com";
$phone = "+919860309670";
$first_name = "Atul";
$last_name = "Asodekar";
$country = "India";
$order_date_time = 1603243035;
$event_name = "Lead";
$currency = "INR";
$lead_value = 2700;

$data=array();
$data["match_keys"]["email"]=hash("sha256",$email);
$data["match_keys"]["phone"]=hash("sha256",$phone);
$data["match_keys"]["fn"]=hash("sha256",$first_name);
$data["match_keys"]["ln"]=hash("sha256",$last_name);
$data["match_keys"]["country"]=hash("sha256","India");
//$data["event_time"] = strtotime($order_date);
$data["event_time"] = $order_date_time;
$data["event_name"] = "Lead";
$data["currency"] = "INR";
$data["value"] = $lead_value;


// LEAD DATA
$data_json = json_encode(array($data));
$fields = array();
$fields['access_token'] = $fb_access_token;
$fields['upload_tag'] = uniqid(); // You should set a tag here (feel free to adjust)
$fields['data'] = $data_json;


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


/*
use Mautic\EmailBundle\Model\EmailModel;
use Mautic\EmailBundle\Model\SendEmailToUser;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Controller\LeadAccessTrait;
use Mautic\CoreBundle\Factory

//	/**
//     * @var LeadModel
//     */
//    $leadModel;
//
//    /**
//     * @var EmailModel
//     */
//     $emailModel;
//	
//	/**
//     * @var SendEmailToUser
//     */
//     $sendEmailToUser;
//	
//	/** @var \Mautic\EmailBundle\Model\EmailModel $model */
//$model            = $this->getModel('email');
//$this->entityClass      = 'Mautic\EmailBundle\Entity\Email';
//
//$id = 6;
//$entity = $this->model->getEntity($id);
//
//	if (null === $entity || !$entity->isPublished()) {
//		return $this->notFound();
//	}
//	
//	$result = $this->model->sendEmail(
//			$entity,
//			$leadFields,
//			[
//				'source'            => ['api', 0],
//				'tokens'            => $cleanTokens,
//				'assetAttachments'  => $assetsIds,
//				'return_errors'     => true,
//			]
//		);
//	$result = $this->model->sendEmailToUser(
//        $entity,
//        null,
//        $lead = null,
//        $tokens = [],
//        $assetAttachments = [],
//        $saveStat = false,
//        $to = ['jayesh@splinth.com'],
//        $cc = [],
//        $bcc = []
//		);
	
	
	/*
	$result = $this->model->sendEmailToUser(
        Email $email,
        $users,
        array $lead = null,
        array $tokens = [],
        array $assetAttachments = [],
        $saveStat = false,
        array $to = [],
        array $cc = [],
        array $bcc = []
    )
	*/

/*

include 'config.php';

$da = '2020-09-21T18:45:30+05:30';

$fud = '2020-09-21 20:00:20';

echo $active_lead_limit;

$date_current = new DateTime();
$cdt = $date_current->format('Y-m-d H:i:s');
$cdW = $date_current->format('l');
$ct = $date_current->format('H:i');
echo "$cdt <br>";
echo "$cdW <br>";
echo "$ct <br>";

if (trim($cdW) != 'Sunday'){
	echo "today is $cdW";
} else {
	echo "not today";
}

//if (!(('16:00' < $ct) && ($ct < '16:25'))){
//	echo 'no';
//} else {
//	echo 'yes';
//}

if ((('11:00' < $ct) && ($ct < '23:00'))){
	echo 'yes';
	include 'config.php';
} else {
	echo 'no';
}

*/

/*
IF CT - DA > 1hr 
&
CT - DA < 2hr
*/
//if (gl_ct_2hrs_1hrs($da, 'ct', 'one')) {
//	echo "true";
//} else {
//	echo "false";
//}

//IF CT - DA > 2hr
//if (gl_ct_2hrs_1hrs($da, 'ct', 'two')) {
//	echo "true";
//} else {
//	echo "false";
//}

// IF FUD - CT > 1hr
//if (gl_ct_2hrs_1hrs($fud, 'main', 'one')) {
//	echo "true";
//} else {
//	echo "false";
//}

//IF FUD > CT
//if (is_fud_gt_ct($fud)){
//	echo "true";
//} else {
//	echo "false";
//}

/*
//IF today 6PM - CT < 1 hr
$date_current = new DateTime();
$dc = $date_current->format('Y-m-d');
$today_18 = $dc." 20:00:00";
if (gl_ct_2hrs_1hrs($today_18, 'main', 'one')) {
	echo "true";
} else {
	echo "false";
}

//IF 6PM > CT
//if (is_fud_gt_ct($today_18)){
//	echo "true";
//} else {
//	echo "false";
//}

/*
Greater = true
Less = false
*/
/*
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
	
	if ($time_main > $time_c){
		return true;
	} else {
		return false;
	}
}


$hasToDoTag = true;
$hasReminderTag = true;
$hasEscalatedTag = true;
$hasPreviousLeadTag = true;

if (($hasToDoTag || $hasReminderTag) && !$hasEscalatedTag && !$hasPreviousLeadTag) {
	if ($hasToDoTag) {
    	echo "to-do <br>";
    }
    if ($hasReminderTag) {
    	echo "reminder <br>";
    }
    if ($hasEscalatedTag) {
    	echo "escalated <br>";
    }
    if ($hasPreviousLeadTag) {
    	echo "previous-lead <br>";
    }
} elseif (($hasToDoTag || $hasReminderTag) && !$hasEscalatedTag) {
	if ($hasToDoTag) {
    	echo "2to-do <br>";
    }
    if ($hasReminderTag) {
    	echo "2reminder <br>";
    }
    if ($hasEscalatedTag) {
    	echo "2escalated <br>";
    }
    if ($hasPreviousLeadTag) {
    	echo "2previous-lead <br>";
    }
}

*/

/*
$servername = "localhost";
	$uname = "qzjtfqubrr";
	$password = "dGA6UfeEtD";
	$dbname = "qzjtfqubrr";
	
	$con = mysqli_connect($servername, $uname, $password, $dbname) ;
	if (mysqli_connect_errno($con) ) {
		echo "Failed to connect to MySQL: " . mysqli_connect_error() ;
	}
	
	$contact_id = 3399;
	$contact_mbu = 'Splinth Technology';
	$df = '2020-09-19 14:17:49';
	// SELECT * FROM `audit_log` WHERE `object_id` = 3399 AND `object` = 'lead' AND `action` = 'udpate' AND `user_name` = 'Splinth Technology' AND `date_added` = '2020-09-19 14:17:49'
	// SELECT * FROM `audit_log` WHERE `object_id` = 3399 AND `user_name` = 'Splinth Technology' AND `date_added` = '2020-09-19 14:17:49'
	$stmt = $con->prepare("SELECT * FROM `audit_log` WHERE `object_id` = ? AND `user_name` = ? AND `date_added` = ?");
	$stmt->bind_param("iss", $contact_id, $contact_mbu, $df);
	$stmt->execute();
	$result = $stmt->get_result();
	$row = $result->fetch_array(MYSQLI_ASSOC);
	$result->close();
	$stmt->close();
	
	$data = json_encode($row, true);
	echo $data;

*/

/*********************

include '/home/199481.cloudwaysapps.com/qzjtfqubrr/public_html/vendor/autoload.php';  

use Mautic\MauticApi;
use Mautic\Auth\ApiAuth;

session_start();

// ApiAuth->newAuth() will accept an array of Auth settings
$settings = array(
    'userName'   => 'admin',             // Create a new user       
    'password'   => 'SPTltd123!@#'              // Make it a secure password
);

// Initiate the auth object specifying to use BasicAuth
$initAuth = new ApiAuth();
$auth = $initAuth->newAuth($settings, 'BasicAuth');

// Nothing else to do ... It's ready to use.
// Just pass the auth object to the API context you are creating.

$api = new MauticApi();
$apiUrl   = "https://mautic.splinth.com/mautic/api/";

$contactApi = $api->newApi('contacts', $auth, $apiUrl);

$id = 3550;
$contact = $contactApi->get($id);
$data = json_encode($contact, true);
echo $data;

********************/


/*
To Add a list of contacts to a segment
*/
/*
$query_content = http_build_query($params);
$fp = fopen($url, 'r', FALSE, // do not use_include_path
stream_context_create([
'http' => [
  'header'  => [ // header array does not need '\r\n'
	'Content-Type: application/json',
	'Authorization: Basic YWRtaW46U1BUbHRkMTIzIUAj',
	'Content-Length: ' . strlen($query_content)
  ],
  'method'  => 'POST',
  'content' => $query_content
]
]));
if ($fp === FALSE) {
return json_encode(['error' => 'Failed to get contents...']);
}
$result = stream_get_contents($fp); // no maxlength/offset
fclose($fp);
return $result;
  
  
$url = "https://mautic.splinth.com/mautic/api/campaigns/17/contact/3483/add";
$fp = fopen($url, 'r', FALSE, // do not use_include_path
stream_context_create([
'http' => [
  'header'  => [ // header array does not need '\r\n'
	'Content-Type: application/json',
	'Authorization: Basic YWRtaW46U1BUbHRkMTIzIUAj',
	'Content-Length: 0'
  ],
  'method'  => 'POST'
]
]));
if ($fp === FALSE) {
return json_encode(['error' => 'Failed to get contents...']);
}
$result = stream_get_contents($fp); // no maxlength/offset
fclose($fp);
echo $result;
*/
/*
To Add a contact to a campaign
*/
/*
$url = "https://mautic.splinth.com/mautic/api/campaigns/17/contact/3483/add";
$fp = fopen($url, 'r', FALSE, // do not use_include_path
stream_context_create([
'http' => [
  'header'  => [ // header array does not need '\r\n'
	'Content-Type: application/json',
	'Authorization: Basic YWRtaW46U1BUbHRkMTIzIUAj',
	'Content-Length: 0'
  ],
  'method'  => 'POST'
]
]));
if ($fp === FALSE) {
return json_encode(['error' => 'Failed to get contents...']);
}
$result = stream_get_contents($fp); // no maxlength/offset
fclose($fp);
echo $result;
*/

/*
	To edit any contact.
*/
/*	
	$url = "https://mautic.splinth.com/mautic/api/contacts/3597/edit";
	$headers = array("Content-Type: application/json", "Authorization: Basic YWRtaW46U1BUbHRkMTIzIUAj");
	$data = "{\"date_last_modified\":\"2020-09-18Ttest\",
				\"email\":\"9082204145@sl-nomail.com\"}";
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	if(curl_exec($curl) === false)
	{
		$error = 'Curl error: ' . curl_error($curl);
		//file_put_contents($log_file_data, $error . "\n", FILE_APPEND);
		exit($error);
	}
	else
	{
		$error = 'Operation completed without any errors';
		//file_put_contents($log_file_data, $error . "\n", FILE_APPEND);
		exit($error);
	}
	curl_close($curl);
*/

/*
$ch = curl_init();
$url = "https://mautic.splinth.com/mautic/api/contacts/3576";
$request_headers = array("Authorization: Basic YWRtaW46U1BUbHRkMTIzIUAj");

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPGET, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
$response = curl_exec($ch);
curl_close ($ch);
echo $response;
$data = json_decode($response, true);
*/

/*
$data = "{'field_name': 'field_value'}";
$url = "http://webservice.url";
$headers = array('Content-Type: application/json');
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
$response = curl_exec($curl);
curl_close($curl);
*/
		
?>