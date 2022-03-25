<?php

include '/home/199481.cloudwaysapps.com/qzjtfqubrr/public_html/vendor/autoload.php';  

use Mautic\MauticApi;
use Mautic\Auth\ApiAuth;

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

$userApi  = $api->newApi("users", $auth, $apiUrl);

$id = $contactData['owner']['id'];

$user = $userApi->get($id);

/*
if: $nextStageLoginQuery
from:
info@creditlinefinance.com Creditline Finance

to:
$representativeEmail $representativeName
escalations@creditlinefinance.com

cc:
sajal@creditlinefinance.com
robin@creditlinefinance.com

if: $nextStageLegalValuation || $nextStageProcessQuery
from:
info@creditlinefinance.com Creditline Finance

to:
$representativeEmail $representativeName
escalations@creditlinefinance.com
shruti@creditlinefinance.com

addReplyTo:shruti@creditlinefinance.com

cc:
sajal@creditlinefinance.com
warren@creditlinefinance.com
robin@creditlinefinance.com
*/

$ownerName = $user['user']['firstName']." ".$user['user']['lastName'];
$ownerEmail = $user['user']['email'];

$representativeName = $contactData['fields']['social']['representative_name']['value'];
$representativeEmail = $contactData['fields']['social']['representative_email']['value'];

$from = 'info@creditlinefinance.com';
$fromName = 'Creditline Finance';
$internalUserName = 'info@creditlinefinance.com';
$internalPassword = 'DO NOT KNOW'; 

$externalUserName = "";

$caseName = $contactData['fields']['core']['firstname']['value'];
$caseStage = $contactData['fields']['core']['remarks']['value'];

$caseProfile = $contactData['fields']['social']['twitter']['value'];
$caseValue = $contactData['fields']['social']['skype']['value'];
$caseLoanType = $contactData['fields']['social']['loan_type1']['value'];

$caseSBDate = $contactData['fields']['social']['sb_date']['value'];
$caseLoginDate = $contactData['fields']['social']['login_date']['value'];


$subject = "";
$body = "";
$altBody = "";

if ($nextStageLoginQuery){
	$subject = "Login Reminder - $caseName";
	$body = "Dear Team, greetings,<br/><br/>This case was sent for login on $caseSBDate. Request you to kindly update the login reference number for our records or any pendencies/query on priority basis.<br>".
	"<br><strong>Case Name:</strong> $caseName".
	"<br><strong>Loan Type:</strong> $caseLoanType".
	"<br><strong>Case Value:</strong> $caseValue".
	"<br><strong>Case Profile:</strong> $caseProfile".
	"<br><br>Thanks & Regards,".
	"<br>Team Creditline Finance";
	$altBody = "Login Reminder - $caseName";
}

if ($nextStageLegalValuation){
	$subject = "Please Update on case status - $caseName";
	$body = "Dear Team, greetings,<br/><br/>The following case was logged in on $caseLoginDate.<br>".
	"<br><strong>Case Name:</strong> $caseName".
	"<br><strong>Loan Type:</strong> $caseLoanType".
	"<br><strong>Case Value:</strong> $caseValue".
	"<br><strong>Case Profile:</strong> $caseProfile".
	"<br><br>We have not recieved any status update on the same. Please update on below details on priority basis.<br><br>".
	"<table><tr>".
    "<td>Legal report is cleared?</td>".
    "<td>Yes/No</td>".
	"</tr><tr>".
    "<td>Valuation report is cleared?</td>".
    "<td>Yes/No</td>".
	"</tr></table>".
	"<br><br>Thanks & Regards,".
	"<br>Team Creditline Finance";
	$altBody = "Please Update on case status - $caseName";
}

if ($nextStageProcessQuery){
	$subject = "Please Update on case status - $caseName";
	$body = "Dear Team, greetings,<br/><br/>The following case was logged in on $caseLoginDate.<br>".
	"<br><strong>Case Name:</strong> $caseName".
	"<br><strong>Loan Type:</strong> $caseLoanType".
	"<br><strong>Case Value:</strong> $caseValue".
	"<br><strong>Case Profile:</strong> $caseProfile".
	"<br><br>We have not recieved any status update on the same. Please share the credit queries on priority basis.".
	"<br><br>Thanks & Regards,".
	"<br>Team Creditline Finance";
	$altBody = "Please Update on case status - $caseName";
}


?>