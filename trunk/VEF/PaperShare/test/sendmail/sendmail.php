<?php
$emailFrom = $_GET['emailFrom'] ;
$emailTo = $_GET['emailTo'] ;
$message = $_GET['message'] ;
echo "<p> Received <p> $message <p> from $emailFrom\n";
if ( mail( $emailTo, "Email Subject : Test", $message, "From: $emailFrom" ) ) {
    echo "<p> Congratulations your email has been sent to $emailTo";
}
else{
    echo "<p> Sorry your email can not be sent";
}
      ?>
