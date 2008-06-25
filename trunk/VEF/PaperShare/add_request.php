<?php
include_once("config.php");
include_once("dbconnect.php");
$today = date("Y-m-d");
for ($index=1;$index<=5;$index++)
{
	$strMysqlQuery = "INSERT INTO $strTableRequestName ".
					"(`title`, `author`, `journal`, `year`, `issue`, `pages`, `field`, `download_link`, 
					`date_request`, `date_assigned`, `requester`, `supplier`, `status`, `previous_suppliers`, `stored_link`) ".
					"VALUES ". 
					"('test$index','testAuthor','Journal','2008','$index','0--21','Mathematics','http://nghiencuusinh.org',
					'$today','$today','testacc0','testacc$index','0','','')";
	mysql_query($strMysqlQuery) or die(mysql_error());					
	$strMysqlQuery = "INSERT INTO $strTableRequestName ".
					"(`title`, `author`, `journal`, `year`, `issue`, `pages`, `field`, `download_link`, 
					`date_request`, `date_assigned`, `requester`, `supplier`, `status`, `previous_suppliers`, `stored_link`) ".
					"VALUES ".
					"('test$index','testAuthor','Journal','2008','$index','0--21','Mathematics','http://nghiencuusinh.org',
					'$today',DATE_SUB('$today',INTERVAL 4 DAY),'testacc0','testacc$index','0','','')";
	mysql_query($strMysqlQuery) or die(mysql_error());
	$strMysqlQuery = "INSERT INTO $strTableRequestName ".
					"(`title`, `author`, `journal`, `year`, `issue`, `pages`, `field`, `download_link`, 
					`date_request`, `date_assigned`, `requester`, `supplier`, `status`, `previous_suppliers`, `stored_link`) ".
					"VALUES ".
					"('test$index','testAuthor','Journal','2008','$index','0--21','Mathematics','http://nghiencuusinh.org',
					'$today',DATE_SUB('$today',INTERVAL 2 DAY),'testacc0','testacc$index','0','','')";
	mysql_query($strMysqlQuery) or die(mysql_error());
}
include_once("dbclose.php");
?>