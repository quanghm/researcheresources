<?php
$email = $_GET['email'] ;
$message = $_GET['message'] ;
echo "<p> Received <p> $message <p> from $email\n";
if ( mail( "kimcuong@gmail.com", "Email Subject : Test", $message, "From: $email" ) ) {
    echo "<p> Congratulations your email has been sent";
}
else{
    echo "<p> Sorry your email can not be sent";
}
      ?>
