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

// Nothing else to do ... It's ready to use.
// Just pass the auth object to the API context you are creating.

$api = new MauticApi();
$apiUrl   = "https://mautic.splinth.com/mautic/api/";

$contactApi = $api->newApi('contacts', $auth, $apiUrl);
$userApi  = $api->newApi("users", $auth, $apiUrl);

$id = 1;

$user = $userApi->get($id);

echo json_encode($user);

$ownerName = $user['user']['firstName']." ".$user['user']['lastName'];
$ownerEmail = $user['user']['email'];

echo $ownerEmail;
echo $ownerName;

/**
{"user":{"isPublished":true,"dateAdded":null,"dateModified":"2020-02-10T21:05:35+05:30","createdBy":null,"createdByUser":null,"modifiedBy":1,"modifiedByUser":"Splinth Technology","id":1,"username":"admin","firstName":"Splinth","lastName":"Technology","email":"hello@splinth.com","position":null,"role":{"createdByUser":null,"modifiedByUser":null,"id":1,"name":"Administrator","description":"Full system access","isAdmin":true,"rawPermissions":null},"timezone":null,"locale":null,"lastLogin":"2020-11-13T09:56:52+05:30","lastActive":"2020-11-13T10:34:04+05:30","onlineStatus":"idle","signature":"Best regards, |FROM_NAME|"}}
**/

?>