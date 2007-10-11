<?php
include "chk_login.inc";
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
<form name="frmRequestData" action="account.php?type=submit_request" method="post">
<?php
foreach ($_POST as $key => $value)
{
	if ($key!=="btnSubmit")
	{
		echo '<input type="hidden" name="'.$key.'" value="'.$value.'"/>'."\r\n";
	}
}
//die("end");
?>
<input type="hidden" value="" name="onFocus"/>
</form>
<?php
if (logged_in())
{
	//function ChkData()
	//	check link
	$pattern='/(http:\/\/)+([a-z0-9_.]).+([.a-z])+(\/)*([a-z])/';
	if (preg_match($pattern,$_POST['txtLink'])==0)
	{
		$_SESSION['ErrMes']="Đường dẫn không hợp lệ";
		die("<script language='javascript'>
		document.frmRequestData.onFocus.value=\"txtLink\";
		frmRequestData.submit();
		</script>");
	}
	//	check year
	$pattern = "/([0-9]*[0-9])/";
	if (preg_match($pattern,$_POST['txtYear'])==0)
	{
		$_SESSION['ErrMes']="Năm xuất bản chỉ được chứa chữ số";
		die("<script language='javascript'>
		document.frmRequestData.onFocus.value=\"txtYear\";
		frmRequestData.submit()</script>");
	}
	//	check page range
	$pattern = "/([0-9]*[0-9,\-])/";
	if (preg_match($pattern,$_POST['txtPages'])==0)
	{
		$_SESSION['ErrMes']="Số trang chỉ được chứa chữ số và dấu gạch ngang \"-\"";
		die("<script language='javascript'>
		document.frmRequestData.onFocus.value=\"txtPages\";
		frmRequestData.submit()</script>");
	}

	if (($_POST['txtTitle'] == "")||($_POST['txtAuthor']=="")||($_POST['txtJournal']=="")||($_POST['txtYear']=="")||($_POST['txtIssue'] =="")||($_POST['optField'] =="0"))
	{
		$_SESSION['ErrMes'] = "Yêu cầu chưa được gửi! Bạn phải điền TẤT CẢ các thông tin!";
		die("<script language=\"javascript\">frmRequestData.submit()</script>");
	}

	/////////   Connect to database   /////////
	include "config.php";
	include 'dbconnect.php';
	//////////////////////////////////////////
	///////// 	assign a supplier       //////
	$strMysqlQuery = "SELECT * FROM $strTableUserName WHERE ";
	if ($cross_field_request==false)
	{
		$strMysqlQuery.="(field ='".$arrFieldList[$_POST['optField']]."') AND ";
	}
	$strMysqlQuery.="(supplier = 1) AND (username != '".$_SESSION['username']."') ORDER BY request_pending_number ASC, request_handle_number ASC";
	$result = mysql_query($strMysqlQuery);
	$row = mysql_fetch_array($result);		
	if ($row===false)
	{
		$_SESSION['ErrMes'] = "Hiện chưa có suppliers nào! Yêu cầu của bạn chưa được gửi!";
		die('<script language="javascript">window.location="account.php?type=submit_request"</script>');
	}
	
	////////	Add request to database //////////
	$today = date("Y-m-d");
	$strMysqlQuery = "INSERT INTO $strTableRequestName (title, author, journal, download_link, issue, year, pages, field, date_request, requester,supplier,previous_suppliers,stored_link) VALUES ('".$_POST['txtTitle']."', '".$_POST['txtAuthor']."', '".$_POST['txtJournal']."', '".$_POST['txtLink']."', '".$_POST['txtIssue']."', '".$_POST['txtYear']."', '".$_POST['txtPages']."', '".$arrFieldList[$_POST['optField']]."', '".$today."', '".$_SESSION['username']."', '".$row['username']."','','')";

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
	$_SESSION['ErrMes'] = "";
	echo '<meta http-equiv="refresh" content="3; url=account.php"/>';
	//}
}
else 						///// Not logged in ///////
{
	$_SESSION["ErrMes"] = "Bạn cần phải đăng nhập trước khi gửi yêu cầu!";
	echo '<script language="javascript"> window.location = "index.php";	</script>';
}
?>

</body>
</html>