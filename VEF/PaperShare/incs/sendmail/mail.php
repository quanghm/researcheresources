<?php
require("class.phpmailer.php");
require("class.smtp.php");
function do_send($ToAddress,$ToUser,$subject,$message)
{
	$mail = new PHPMailer();
	ini_set("SMTP","smtp.nghiencuusinh.org");
	ini_set("sendmail_from","admin@nghiencuusinh.org");
	$mail->IsSMTP();                                   // send via SMTP
	//$mail->Host     = "smtp.princeton.edu"; // SMTP servers
	$mail->SMTPAuth = true;     // turn on SMTP authentication
	$mail->Username = "nghiencuusinh.org_admin";  // SMTP username
	$mail->Password = "traodoitailieu"; // SMTP password
	
	$mail->From     = "admin@nghiencuusinh.org";
	$mail->FromName = "Admin";
	$mail->AddAddress($ToAddress,$ToUser); 
	$mail->AddReplyTo("admin@nghiencuusinh.org","Admin");
	
	$mail->WordWrap = 50;                              // set word wrap
	$mail->IsHTML(true);                               // send as HTML
	
	$mail->Subject  =  $subject;
	$mail->Body     =  $message;
	//$mail->AltBody  =  "This is the text-only body";
	
	if(!$mail->Send())
	{
		return false;
	   /* echo "Message was not sent <p>";
	   echo "Mailer Error: " . $mail->ErrorInfo;
	   exit; */
	}
	return true;
}
?>