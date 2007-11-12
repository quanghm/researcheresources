<?php
require_once("email.php");
$emailFrom = $_GET['emailFrom'] ;
$emailTo = $_GET['emailTo'] ;
$message = $_GET['message'] ;
echo "<p> Received <p> $message <p> from $emailFrom\n";
if ( $error=email( $emailTo, "Email Subject : Test", $message, "From: $emailFrom" ) ) {
    echo "<p> Congratulations your email has been sent to $emailTo";
}
else{
    echo $error;
}
      ?>
