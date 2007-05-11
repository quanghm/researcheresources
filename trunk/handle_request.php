<?php
include "chk_login.inc";
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php
include "config.php";
include "dbconnect.php";


if (!(logged_in()))
{
echo "<center>Bạn chưa đăng nhập! Đang quay trở lại trang chủ...</center>";
die('<meta http-equiv="refresh" content="3;url=index.php">');
}

if (!isset($_GET['action']))
{
	$_GET['action']="";
}
if ($_GET['action']=='finishing')
{
	/////////// increase number of requests handled AND decrease number of requests pending
	$strMysqlQuery = "UPDATE $strTableUserName SET request_handle_number = request_handle_number + 1, request_pending_number = request_pending_number - 1  WHERE (username = '".$_SESSION['username']."')";
	mysql_query($strMysqlQuery) or die(mysql_error());

	/////*//////  Chage status of request to finished
	$strMysqlQuery = "UPDATE $strTableRequestName SET status = -1 WHERE id=".$_POST['frmHandlingRequestID'];
	mysql_query($strMysqlQuery) or die(mysql_error());	
	
	///////////	 Return to User's page
	echo '<script language="javascript"> window.location="account.php?type=request";</script>';}
elseif ($_GET['action']=='passing')
{	
	/////////	Get Request's detail
	$strMysqlQuery = "SELECT * FROM $strTableRequestName WHERE id=".$_POST['frmHandlingRequestID'];
	$result = mysql_query($strMysqlQuery) or die(mysql_error());
	$arrRequestData=mysql_fetch_array($result);
	
	///////// Increase user's number of requests
	//$strMysqlQuery = "UPDATE $strTableUserName SET request_number = request_number + 1  WHERE (username = '".$_SESSION['username']."')";
	
	///////// Decrease request_pending_number
	$strMysqlQuery = "UPDATE $strTableUserName SET request_pending_number = request_pending_number - 1  WHERE (username = '".$_SESSION['username']."')";
	mysql_query($strMysqlQuery);
	///////// Assign a new supplier ////////////
		$strPassSupplier ='';
		////////	Get the previous supplier	//////
		////////	put to array $arrPreviousSuppliers
		parse_str($arrRequestData['previous_suppliers']);
		
		///////		Get list of supplier in same field
		$strMysqlQuery = "SELECT * FROM $strTableUserName WHERE (field = '".$arrRequestData['field']."') AND (supplier = 1) AND (username != '".$arrRequestData['requester']."') AND (username != '".$_SESSION['username']."') ";
		
		for ($i=0; $i<$arrRequestData['status']; $i++)
		{
			$strMysqlQuery .= "AND (username !='".$arrPreviousSuppliers[$i]."') ";
		}
		$strMysqlQuery .= "ORDER BY request_pending_number ASC, request_handle_number ASC";
		//echo $strMysqlQuery.'<br>';
		$result = mysql_query($strMysqlQuery) or die(mysql_error());
		$arrSupplierList = mysql_fetch_array($result);
		if ($arrSupplierList === false)			//No supplier found
		{
			echo "<center>Passing Failed! No available suppliers!</center>";
			///// 	Change request's status to Failed
			$strMysqlQuery = "UPDATE $strTableRequestName SET status = -2 WHERE id=".$_POST['frmHandlingRequestID'];
			mysql_query($strMysqlQuery) or die(mysql_error());	
			////////////////////////////////////////////////
			echo "<center>Đang quay lại trang thông tin cá nhân...</center>";
			die('<meta http-equiv="refresh" content="3;url=account.php?type=request">');
		}
		else		//passing request to new supplier
		{
			$strPreviousSuppliers = $arrRequestData['previous_suppliers'].'arrPreviousSuppliers[]='.$_SESSION['username'].'&';
			//echo $strPreviousSuppliers."<br>";
			$strMysqlQuery = "UPDATE $strTableRequestName SET previous_suppliers = '".$strPreviousSuppliers."', status = status + 1, supplier = '".$arrSupplierList['username']."' WHERE id = ".$arrRequestData['id'];
			mysql_query($strMysqlQuery) or die(mysql_error());
			//echo $strMysqlQuery;
		}
		echo "<center> Chuyển yêu cầu thành công! Đang quay trở lại trang trước...</center>";
		echo ('<meta http-equiv="refresh" content="3;url=account.php?type=request">');}
elseif ($_GET['action']=='failing')
{
	//////////	Change request's status to failed
	$strMysqlQuery = "UPDATE $strTableRequestName SET status = -2 WHERE id=".$_POST['frmHandlingRequestID'];
	mysql_query($strMysqlQuery) or die(mysql_error());	
	
	///////// Decrease request_pending_number
	$strMysqlQuery = "UPDATE $strTableUserName SET request_pending_number = request_pending_number - 1  WHERE (username = '".$_SESSION['username']."')";

	///////////	 Return to User's page
	echo '<script language="javascript"> window.location="account.php?type=request";</script>';}
else
{
	echo 'Chả hiểu là phải làm gì! Đang quay lại trang chủ...';
	echo '<meta http-equiv="refresh" content="3;url=index.php">';
}
include "dbclose.php";
?></head>
<body>
</body>
</html>
