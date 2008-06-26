<?php
include "global_config.php";
include $strIncDir."sendmail/mail.php";
include "global_dbconnect.php";

/////////////////////////////////
function DisableSupplier($SupplierID)	//	disable supplier with a given $supplierID 
{
	$strMysqlQuery = "UPDATE ".$GLOBALS['strTableUserName'].
					" SET supplier = 0 WHERE id='$SupplierID'";
	mysql_query($strMysqlQuery) or die(mysql_error());
}

/////////////////////////////////////////////
function PassRequest($arrRequestData)	//	Pass request
{
	//	Get current supplier data
	$strMysqlQuery = "SELECT * FROM ".$GLOBALS['strTableUserName'].
					" WHERE (`id`='".$arrRequestData['supplierID']."')";
	$arrSupplierData = mysql_fetch_array(mysql_query($strMysqlQuery)) or die(mysql_error());
	
	//	Get current requester data
	$strMysqlQuery = "SELECT * FROM ".$GLOBALS['strTableUserName'].
					" WHERE (`username`='".$arrRequestData['requester']."')";
	$arrRequesterData = mysql_fetch_array(mysql_query($strMysqlQuery)) or die(mysql_error());
	
	//	Get the previous supplier and put to array $arrPreviousSuppliers
	parse_str($arrRequestData['previous_suppliers']);
	
	//	Search for new supplier
	$strMysqlQuery = "SELECT `username`,`id` FROM ".$GLOBALS['strTableUserName'].
					" WHERE (`supplier`=1) AND (`user_level`>0) ";		//	search all active supplier
	foreach ($arrPreviousSuppliers as $PreviousSupplier)
	{
		$strMysqlQuery .= "AND (`username`!='".$PreviousSupplier."') ";	//	all previous supplier
	}
	if ($GLOBALS['cross_field_request']===false)
	{
		$strMyQuery .= "AND (`field`='".$arrRequestData['field']."'";
	}
	$SelectSupplierResult = mysql_query($strMysqlQuery) or die(mysql_error());
	
	if ($arrNewSupplierData = mysql_fetch_array($SelectSupplierResult))	// found new supplier
	{
		//	update request's data
		$strPreviousSuppliers = $arrRequestData.'$arrPreviousSuppliers[]=&'.$arrSupplierData['username'];
		$strMysqlQuery = "UPDATE ".$GLOBALS['strTableRequestName'].
						" SET `supplier`='".$arrNewSupplierData['username'].
						"', `SuppplierID`='".$arrNewSupplierData['id']."', `status`=status+1, `previous_suppliers`=$strPreviousSuppliers".
						" WHERE (`id`='".$arrRequestData['id']."')";
		mysql_query($strMysqlQuery) or die(mysql_error());
		
		//	update previous supplier's data
		$strMysqlQuery = "UPDATE ".$GLOBALS['strTableUserName'].
						" SET `request_pending_number`=request_pending_number -1".
						" WHERE (`id`='".$arrRequesterData['id']."')";
		mysql_query($strMysqlQuery) or die(mysql_error());
		
		//	update current suplier's data
		$last_assigned_request = date('YmdHis');
		$strMysqlQuery = "UPDATE $strTableUserName ".
						 "SET request_pending_number = request_pending_number +1, last_assigned_request = $last_assigned_request ".
						 "WHERE  (user_level='1') AND username = '".$arrSupplierData['username']."'";
		mysql_query($strMysqlQuery) or die(mysql_error());
		
		//	email requester about the delay
			/////	Email Requester about delay
		$emlTo = $arrRequesterData['email'];
		$Subject	= "Yeu cau duoc chuyen";
	
		$message = "<html>
		<head>
		<title>Yêu cầu được chuyển</title>
		</head>
		<body>
		Đây là email tự động gửi từ ban quản trị của $strWebsiteName.<br/>
		Email này nhằm thông báo cho bạn biết có sự chậm trễ trong việc tìm bài báo ".$arrRequestData['title']." bởi ".$arrRequestData['author']." mà bạn đề nghị tại $strWebsiteName. Hiện tại bài báo đang được người cung cấp tiếp theo xử lý.
		Xin hãy đăng nhập vào trang web <a href=\"http://$strWebsiteName\">$strWebsiteName </a> để biết thêm chi tiết.
		</body>
		</html>";
		if (do_send($emlTo, $arrRequestData['requester'],$Subject, $message))
		{
			echo" Send email to ".$arrRequesterData['username']." at".$arrRequesterData['email']." : SUCCESSFUL.<br>\n";
		}
		else
		{
			echo(" Send email to ".$arrRequesterData['username'].": FAILED.<br>\n");
		}
	}
	else
	{
		$strMysqlQuery = "UPDATE $strTableRequestName SET status = -2 WHERE id=".$_POST['frmHandlingRequestID'];
		mysql_query($strMysqlQuery) or die(mysql_error());	
		
		////	inform requesters about failure
		$emlTo = $arrRequesterData['email'];
		$Subject = "Khong tim duoc bai bao cua ban";
		$message = "<html>
		<head>
		<title>Yêu cầu thất bại</title>
		</head>
		<body>
		Đây là email tự động gửi từ ban quản trị của $strWebsiteName.<br/>
		Chúng tôi không tìm được một trong những bài báo theo yêu cầu của bạn tại $strWebsiteName .<br />
		Xin hãy đăng nhập vào trang web <a href=\"http://$strWebsiteName\">$strWebsiteName </a> để biết thêm chi tiết.
		</body>
		</html>";
		if (do_send($emlTo, $arrRequesterData['username'], $Subject, $message))
		{
			echo "email to ".$arrRequesterData['username'].": SUCCESSFUL";
		}
		else
		{
			echo "email to ".$arrRequesterData['username'].": FAILED";
		}
	}
}
///////////////	Main	//////////////////// 
//Get current date
$today = date('Y-m-d');

//	Get all requests which are config.php/$GLOBALS['DisableSupplierThreshold'] days late
$strMysqlQuery = "SELECT `SupplierID` FROM ".$GLOBALS['strTableRequestName'].
				" WHERE DATEDIFF('$today', `date_assigned`)>=".$GLOBALS['DisableSupplierThreshold'];

$SelectLateRequestResult = mysql_query($strMysqlQuery) or die(mysql_error());
//	Get list of all late suppliers and disable them
while ($arrLateRequestData=mysql_fetch_array($SelectLateRequestResult))
{
	$arrLateSupplierID[]=$arrLateRequestData['SupplierID'];
	DisableSupplier($arrLateRequestData['SupplierID']);
}
//	Redistribute waiting papers
foreach ($arrLateSupplierID as $LateSupplierID)
{
	$strMysqlQuery = "SELECT * FROM ".$GLOBALS['strTableRequestName'].
					" WHERE (`SupplierID`= $LateSupplierID) AND (status>-1)";
	$SelectRequestResult = mysql_query($strMysqlQuery) or die(mysql_error());		//	Get list of request for current LateSupplier

	while ($arrRequestData=mysql_fetch_array($SelectRequestResult))
	{
		PassRequest($arrRequestData);
	}
}
?>