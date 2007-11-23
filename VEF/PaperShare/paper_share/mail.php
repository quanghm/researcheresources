<?php
include 'config.php';
include $strIncDir."sendmail/mail.php";
$emlTo = "qhoang@princeton.edu";
$Subject = "Khong tim duoc bai bao cua ban";
$message = "Test message";
print do_send($emlTo,"Quang Hoang", $Subject, $message);
?>