<?php
session_start();
include "chk_login.inc";
if (logged_in())
{
	function ChkData()
		{
		$frmResult = ($_POST['txtTitle'] == "")||($_POST['txtAuthor']=="")||($_POST['txtJournal']=="")||($_POST['txtYear']=="")||($_POST['txtIssue'] =="")||($_POST['optField'] =="0");
		return $frmResult; 
		}	
		if (ChkData())
		{
			$_SESSION['ErrMess'] = "Yêu cầu chưa được gửi.";
			echo "<script language=\"javascript\"> history.back()</script>";
		}
		else
		{
		/////////   Connect to database   /////////
		include "config.php";
		include 'dbconnect.php';
		//////////////////////////////////////////
		
		///////// 	assign a supplier       //////
		$strMysqlQuery = "SELECT * FROM $strTableUserName WHERE (field ='".$_POST['optField']."') AND (supplier = 1) ORDER BY request_handle_number";
		$result = mysql_query($strMysqlQuery) or die(mysql_error());
		$row = mysql_fetch_array($result);
		
		/////////	Add request to database //////////
		$today = date("Y-m-d");
		$strMysqlQuery = "INSERT INTO $strTableRequestName (title, author, journal, download_link, issue, year, field, date_request, requester,supplier) VALUES ('".$_POST['txtTitle']."', '".$_POST['txtAuthor']."', '".$_POST['txtJournal']."', '".$_POST['txtLink']."', '".$_POST['txtIssue']."', '".$_POST['txtYear']."', '".$_POST['optField']."', '".$today."', '".$_SESSION['username']."', '".$row['username']."')";
		//echo $strMysqlQuery;
		$result = mysql_query($strMysqlQuery) or die(mysql_error());
		
		/////////	Increase user's number of request /////////
		$strMysqlQuery = "SELECT * FROM $strTableUserName WHERE (username ='".$_SESSION['username']."')";
		//echo $strMysqlQuery;
		$result = mysql_query($strMysqlQuery) or die(mysql_error());
		$arrUserData = mysql_fetch_array($result);
		$arrUserData['request_number']++;
		$strMysqlQuery = "UPDATE $strTableUserName SET request_number = ".$arrUserData['request_number']++." WHERE (username = '".$arrUserData['username']."')";
		//echo $strMysqlQuery;
		mysql_query($strMysqlQuery) or die(mysql_error());
		//////////////////////////////////////////////////
		
		echo "<center>Yêu cầu đã được gửi! Quay lại trang thông tin cá nhân</center><br>";
		/////////// Close connection ///////////
		include "dbclose.php";
		$_SESSION['ErrMess'] = "";
		echo '<script language="javascript">window.location="account.php?type=view";</script>';
		}
}
else 						///// Not logged in ///////
{
	$_SESSION["ErrMess"] = "Bạn cần phải đăng nhập trước khi gửi yêu cầu!";
	
	echo '<script language="javascript"> window.location = "index.php";	</script>';
}
?>
