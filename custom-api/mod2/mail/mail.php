<?php
require 'PHPMailerAutoload.php';

$email_id = $_REQUEST["email"];
$name = $_REQUEST["name"];
$mail = new PHPMailer;

//https://mautic.splinth.com/mautic/custom-api/mod2/mail/mail.php?email=jayesh@splinth.com&name=jayesh


if (isset($name, $email_id)) {
	echo $_SERVER['DOCUMENT_ROOT'] ;
	echo !extension_loaded('openssl')?"Not Available":"Available<br>";
	$mail->isSMTP();                                      // Set mailer to use SMTP
	//$mail->Host = 'smtp1.example.com;smtp2.example.com';  // Specify main and backup SMTP servers
	$mail->Host = "ssl://smtp.dreamhost.com"; 
	$mail->SMTPAuth = true;                               // Enable SMTP authentication and for sending emails from Gmail SMTP
	$mail->SMTPDebug  = 2;       // enables SMTP debug information (for testing)
	$mail->Port = 465;                           // Enable encryption, 'ssl' also
	$mail->Username = 'noreply@sasteloans.com';                 // SMTP username
	$mail->Password = 'pass';                           // SMTP password
	//$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted. Keep commented for Gmail SMTP
	
	$host = "ssl://smtp.dreamhost.com";
	$port = "465";
	$checkconn = fsockopen($host, $port, $errno, $errstr, 5);
		if(!$checkconn){
			echo "($errno) $errstr <br> ";
		} else {
			echo 'ok<br>';
		}
		

	$mail->From = 'noreply@sasteloans.com'; // Should be specified for dream host emails.
	$mail->FromName = 'Saste Loans Escalations';
	//$mail->addAddress('joe@example.net', 'Joe User');     // Add a recipient
	$mail->addAddress($email_id);               // Name is optional
	//$mail->addReplyTo('rt@example.com', 'Information');
	//$mail->addCC('cc@example.com');
	//$mail->addBCC('bcc@example.com');
	
	//$mail->WordWrap = 50;                                 // Set word wrap to 50 characters
	//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
	//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
	$mail->isHTML(true);                                  // Set email format to HTML	
	
	$mail->Subject = "Hello $name";
	$mail->Body    = "This is the HTML message body <b>in bold!</b><br>
						Click <a href=>here</a> to verify update of your password.";
	$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
	
	if(!$mail->send()) {
		echo 'Message could not be sent.<br>';
		echo 'Mailer Error: ' . $mail->ErrorInfo;
	} else {
		//$db->updatePassword($email_id);
		echo 'Message has been sent';
	}
}