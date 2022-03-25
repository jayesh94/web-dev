<?php
require_once('PHPMailerAutoload.php');

include 'mail-config.php';

$mail = new PHPMailer;

//https://mautic.splinth.com/mautic/custom-api/mod2/mail/mail.php?email=jayesh@splinth.com&name=jayesh
	
echo $_SERVER['DOCUMENT_ROOT'] ;
echo !extension_loaded('openssl')?"Not Available":"Available<br>";
$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = "ssl://smtp.dreamhost.com"; 
$mail->SMTPAuth = true;                               // Enable SMTP authentication and for sending emails from Gmail SMTP
$mail->SMTPDebug  = 0;       // enables SMTP debug information (for testing) https://github.com/PHPMailer/PHPMailer/wiki/SMTP-Debugging
$mail->Port = 465;                           // Enable encryption, 'ssl' also
$mail->Username = $internalUserName;                 // SMTP username
$mail->Password = $internalPassword;                           // SMTP password
//$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted. Keep commented for Gmail SMTP

$host = "ssl://smtp.dreamhost.com";
$port = "465";
$checkconn = fsockopen($host, $port, $errno, $errstr, 5);
	if(!$checkconn){
		echo "($errno) $errstr <br> ";
	} else {
		echo 'ok<br>';
	}

$mail->From = $from; // Should be specified for dream host emails.
$mail->FromName = $fromName;
//$mail->addAddress('joe@example.net', 'Joe User');     // Add a recipient

//$mail->addReplyTo('rt@example.com', 'Information');
//$mail->addCC('cc@example.com');
//$mail->addBCC('bcc@example.com');
if ($nextStageLoginQuery){
	$mail->addAddress($representativeEmail, $representativeName);               // Name is optional
	$mail->addAddress('escalations@creditlinefinance.com');               // Name is optional
	$mail->addCC('sajal@creditlinefinance.com', 'Sajal');
	$mail->addCC('robin@creditlinefinance.com', 'Robin');
	$mail->addBCC('sl@splinth.com');
} else {
	$mail->addAddress($representativeEmail, $representativeName);               // Name is optional
	$mail->addAddress('escalations@creditlinefinance.com');               // Name is optional
	$mail->addReplyTo('shruti@creditlinefinance.com', 'Shruti');
	$mail->addCC('sajal@creditlinefinance.com', 'Sajal');
	$mail->addCC('robin@creditlinefinance.com', 'Robin');
	$mail->addCC('warren@creditlinefinance.com', 'Warren');
	$mail->addBCC('sl@splinth.com');
}
//$mail->WordWrap = 50;                                 // Set word wrap to 50 characters
//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
$mail->isHTML(true);                                  // Set email format to HTML	

$mail->Subject = $subject;
$mail->Body    = $body;
$mail->AltBody = $altBody;

if(!$mail->send()) {
	echo 'Message could not be sent.<br>';
	echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
	echo 'Message has been sent';
}

?>
