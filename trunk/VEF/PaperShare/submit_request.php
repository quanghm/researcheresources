<?php
include "chk_login.inc";
if (logged_in())
{
	function ChkData()
		{
		$frmResult = (($_POST['txtTitle'] == "")||($_POST['txtAuthor']=="")||($_POST['txtJournal']=="")||($_POST['txtYear']=="")||($_POST['txtIssue'] =="")||($_POST['optField'] =="0"));
		return $frmResult; 
		}	
		if (ChkData())
		{
			$_SESSION['ErrMess'] = "!!!Yêu cầu chưa được gửi!!!";
			echo "<script language=\"javascript\"> history.back()</script>";
		}
		else
		{
		/////////   Connect to database   /////////
		include "config.php";
		include 'dbconnect.php';
		//////////////////////////////////////////
		
		///////// 	assign a supplier       //////
		$strMysqlQuery = "SELECT * FROM $strTableUserName WHERE (field ='".$_POST['optField']."') AND (supplier = 1) AND (username != '".$_SESSION['username']."') ORDER BY request_pending_number ASC, request_handle_number ASC";
//		echo $strMysqlQuery;
		$result = mysql_query($strMysqlQuery) or die(mysql_error());
		$row = mysql_fetch_array($result);
		if ($row===false)
		{
			$strMysqlQuery = "SELECT * FROM $strTableUserName WHERE (supplier = 1) AND (username != '".$_SESSION['username']."') ORDER BY request_pending_number ASC, request_handle_number ASC";
			$result = mysql_query($strMysqlQuery);
			$row = mysql_fetch_array($result);
			if ($row===false)
			{
				$_SESSION['ErrMess'] = "Hiện chưa có suppliers nào! Yêu cầu của bạn chưa được gửi!";
				die('<script language="javascript">history.back()</script>');
			}
		}
		////////	Add request to database //////////
		$today = date("Y-m-d");
		$strMysqlQuery = "INSERT INTO $strTableRequestName (title, author, journal, download_link, issue, year, pages, field, date_request, requester,supplier,previous_suppliers,stored_link) VALUES ('".$_POST['txtTitle']."', '".$_POST['txtAuthor']."', '".$_POST['txtJournal']."', '".$_POST['txtLink']."', '".$_POST['txtIssue']."', '".$_POST['txtYear']."', '".$_POST['txtPages']."', '".$_POST['optField']."', '".$today."', '".$_SESSION['username']."', '".$row['username']."','','')";

		 $result = mysql_query($strMysqlQuery) or die(mysql_error());
		
		/////////	Increase number of requests pending for supplier /////////
		$strMysqlQuery = "UPDATE $strTableUserName SET request_pending_number = request_pending_number + 1 WHERE username = '".$row['username']."'";
		mysql_query($strMysqlQuery) or die(mysql_error());
		///////////////////////////////////////////////////////
		
		/////////	Increase user's number of request /////////
		$strMysqlQuery = "SELECT * FROM $strTableUserName WHERE (username ='".$_SESSION['username']."')";
		//echo $strMysqlQuery;
		$result = mysql_query($strMysqlQuery) or die(mysql_error());
		$arrUserData = mysql_fetch_array($result);
		$arrUserData['request_number']++;
		$strMysqlQuery = "UPDATE $strTableUserName SET request_number = ".$arrUserData['request_number']++." WHERE (username = '".$arrUserData['username']."')";
		//echo $strMysqlQuery;
		mysql_query($strMysqlQuery) or die(mysql_error());
	
		///////////////////////////////////////////////
		echo "<center>Yêu cầu đã được gửi! Đang quay lại trang thông tin cá nhân...</center><br>";
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
