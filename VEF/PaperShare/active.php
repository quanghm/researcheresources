<?php

include "chk_login.inc";

if (!(isset($_GET['user_id'])))
	{
		$_GET['user_id'] = '';
	}
if (!(isset($_GET['active'])))
	{
		$_GET['active'] = '';
	}
if (!(isset($_GET['status'])))
	{
		$_GET['status'] = '';
	}

include "config.php";
include "dbconnect.php";	
$strMysqlQuery = "UPDATE $strTableUserName SET user_level = 1 WHERE (username='".$_GET['user_id']."')";
	if (mysql_query($strMysqlQuery))
		{
			echo "Bạn đã kich hoạt thành công! Cảm ơn bạn đã tham gia hoạt động cùng nghiencuusinh.org.";
			//$_SESSION['username']=crypt($_GET['user_id']);
			//$_SESSION['password']=crypt($_GET['active']);
			echo '<meta http-equiv="refresh" content="2; url=account.php"/>';			
		}
	else
		{
			"<br>Chua kich hoat duoc tai khoan: ".die(mysql_error());
		}	
			
?>