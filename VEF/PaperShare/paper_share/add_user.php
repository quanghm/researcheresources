<?php
/*
 * Created on 30-06-2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
include "config.php";
include "dbconnect.php";
$encoded_password = crypt("testing");
for ($i = 1; $i<=5; $i++)
{
	$today = date("Y-m-d");
	$strMysqlQuery = "INSERT INTO $strTableUserName (username, password, email, field, supplier,join_date) VALUES ('testacc".$i."', '$encoded_password', 'test".$i."@yahoo.com', 'Mathematics', '1','$today')";
	mysql_query($strMysqlQuery);
}
include "dbclose.php";
echo "done";
?>