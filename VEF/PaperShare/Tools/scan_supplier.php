 	<?php
include "../global_config.php";
include "../".$strIncDir."sendmail/mail.php";
include "../global_dbconnect.php";

/////////////////////////////////
function DisableSupplier($SupplierName)	//	disable supplier with a given $supplierID 
{
	$strMysqlQuery = "SELECT `email` FROM ".$GLOBALS['strTableUserName']." WHERE `username`='$SupplierName'";
	$SelectSupplierResult=mysql_query($strMysqlQuery);
	$arrSupplierData=mysql_fetch_array($SelectSupplierResult);
	$SupplierEmail = $arrSupplierData['email'];
	
	$strMysqlQuery = "UPDATE ".$GLOBALS['strTableUserName'].
					" SET supplier = 0 WHERE (`username`='$SupplierName')";
	if (mysql_query($strMysqlQuery))
	{
		//	send mail to supplier
		$message=	'Đây là email tự động gửi từ ban quản trị của Nghiencuusinh.org. Hiện bạn đang có yêu cầu đã quá hạn '.
					$DisableSupplierThreshold.
					' ngày. Nghiencuusinh.org hiểu rất có thể bạn đang bận việc cá nhân, do đó tạm thời ngừng chức năng cung cấp của bạn.'.
					'Bất cứ khi nào bạn có thời gian và muốn tiếp tục tham gia cung cấp, bạn có thể vào “Thay đổi thông tin cá nhân”'.
					' trong tài khoản của mình để mở lại chức năng này. Mong sớm nhận được sự giúp đỡ của bạn.';
		if (do_send($SupplierEmail,$SupplierName,'Thong bao tam dung chuc nang cung cap', $message))
		{
			echo "Email to late supplier ".$SupplierName.": done...";
		}
	}
	else die(mysql_error());
}

/////////////////////////////////////////////
function PassRequest($arrRequestData)	//	Pass request
{
	//	Get current supplier data
	$strMysqlQuery = "SELECT * FROM ".$GLOBALS['strTableUserName'].
					" WHERE (`username`='".$arrRequestData['supplier']."')";
	
	$arrSupplierData = mysql_fetch_array(mysql_query($strMysqlQuery)) or die(mysql_error());
	
	
	//	Get current requester data
	$strMysqlQuery = "SELECT * FROM ".$GLOBALS['strTableUserName'].
					" WHERE (`username`='".$arrRequestData['requester']."')";
	
	$arrRequesterData = mysql_fetch_array(mysql_query($strMysqlQuery)) or die(mysql_error());
	
	//	Get the previous supplier and put to array $arrPreviousSuppliers
	parse_str($arrRequestData['previous_suppliers']);
	
	//	Search for new supplier
	$strMysqlQuery = "SELECT `username`,`id` FROM ".$GLOBALS['strTableUserName'].
					" WHERE (`supplier`=1) AND (`user_level`>0) AND (`username`!='".$arrRequesterData['username']."')";		//	search all active supplier
	
	if (isset($arrPreviousSuppliers))
	foreach ($arrPreviousSuppliers as $PreviousSupplier)
	{
		$strMysqlQuery .= "AND (`username`!='".$PreviousSupplier."') ";	//	all previous supplier
	}
	if ($GLOBALS['cross_field_request']==false)
	{
		 $strMyQuery .= "AND (`field`='".$arrRequestData['field']."')";
	}
	$strMysqlQuery .= "ORDER BY last_assigned_request ASC, request_handle_number ASC, request_pending_number ASC";
	
	$SelectSupplierResult = mysql_query($strMysqlQuery) or die(mysql_error());
	
	if ($arrNewSupplierData = mysql_fetch_array($SelectSupplierResult))	// found new supplier
	{
		//	update request's data
		$today = date('Y-m-d');
		$strPreviousSuppliers = $arrRequestData['previous_suppliers'].'$arrPreviousSuppliers[]='.$arrSupplierData['username']."&";
		$strMysqlQuery = "UPDATE ".$GLOBALS['strTableRequestName'].
						" SET `supplier`='".$arrNewSupplierData['username'].
						"', `status`=`status`+1, `previous_suppliers`='$strPreviousSuppliers',`date_assigned`='$today' ".
						" WHERE (`id`='".$arrRequestData['id']."')";
		
		mysql_query($strMysqlQuery) or die(mysql_error());
		
		//	update previous supplier's data
		$strMysqlQuery = "UPDATE ".$GLOBALS['strTableUserName'].
						" SET `request_pending_number`=request_pending_number -1".
						" WHERE (`username`='".$arrRequestData['supplier']."')";
		mysql_query($strMysqlQuery) or die(mysql_error());
		
		//	update current suplier's data
		$last_assigned_request = date('YmdHis');
		$strMysqlQuery = "UPDATE ".$GLOBALS['strTableUserName'].
						 " SET `request_pending_number` = `request_pending_number` +1, `last_assigned_request` = '$last_assigned_request' ".
						 " WHERE  (user_level='1') AND username = '".$arrNewSupplierData['username']."'";
		mysql_query($strMysqlQuery) or die(mysql_error());
		
		//	Email requester about the delay
		echo"Email to requester ".$arrRequesterData['username']." done...";
	}
	else
	{
		$strMysqlQuery = "UPDATE $strTableRequestName SET status = -2 WHERE id=".$arrRequestData['id'];
		
		mysql_query($strMysqlQuery) or die(mysql_error());		
	}
}

///////////////	Main	//////////////////// 
//Get current date
$today = date('Y-m-d');

//	Get all requests which are config.php/$GLOBALS['DisableSupplierThreshold'] days late
$strMysqlQuery = "SELECT * FROM ".$GLOBALS['strTableRequestName'].
				" WHERE DATEDIFF('$today', `date_assigned`)>=".$GLOBALS['DisableSupplierThreshold'].
				" GROUP BY `supplier`";

$SelectLateRequestResult = mysql_query($strMysqlQuery) or die(mysql_error());
//	Get list of all late suppliers and disable them
if (mysql_num_rows($SelectLateRequestResult)>0)
{
	while ($arrLateRequestData=mysql_fetch_array($SelectLateRequestResult))
	{
		$arrLateSuppliers[]=$arrLateRequestData['supplier'];
		DisableSupplier($arrLateRequestData['supplier']);
	}
	//	Redistribute waiting papers
	foreach ($arrLateSuppliers as $LateSupplier)
	{
		$strMysqlQuery = "SELECT * FROM ".$GLOBALS['strTableRequestName'].
						" WHERE (`supplier`= '$LateSupplier') AND (status>-1)";
		$SelectRequestResult = mysql_query($strMysqlQuery) or die(mysql_error());		//	Get list of requests for current LateSupplier
		
		while ($arrRequestData = mysql_fetch_array($SelectRequestResult))
		{	
			echo ("<br />______________<br />Passing Request ".$arrRequestData['id']."<br />");
			PassRequest($arrRequestData);
		}
	}
}
else
{
	echo "Khong co yeu cau muon ".$GLOBALS['DisableSupplierThreshold']." ngay.<br />\r\n";
}

//	Remind suppliers with requests $GLOBALS['WarnSupplierThreshold'] days late
$today = date('Y-m-d');

$strMysqlQuery = 	"SELECT *, COUNT(*) AS LateRequestNumber FROM ".$GLOBALS['strTableRequestName'].
				" WHERE (DATEDIFF('$today', `date_assigned`)=".$GLOBALS['WarnSupplierThreshold'].
				") AND (`status`>=0)".
				" GROUP BY `supplier`";
$SelectRequestResult = mysql_query($strMysqlQuery) or die(mysql_error());

if (mysql_num_rows($SelectRequestResult) == 0)
{
	echo "Khong co yeu cau muon ".$GLOBALS['WarnSupplierThreshold']." ngay. <br />\r\n";
}
else
{
	while ($arrRequestData = mysql_fetch_array($SelectRequestResult))
	{
		$strMysqlQuery = "SELECT * FROM ".$GLOBALS['strTableUserName'].
					" WHERE `username`='".$arrRequestData['supplier']."'";
		$SelectSupplierResult = mysql_query($strMysqlQuery) or die(mysql_error());

		// email to supplier
		while ($arrSupplierData=mysql_fetch_array($SelectSupplierResult))
		{
			$email = $arrSupplierData['email'];
			$username = $arrSupplierData['username'];
			$message = 'Đây là email tự động gửi từ ban quản trị của Nghiencuusinh.org. Hiện thời bạn có yêu cầu '.
						$arrSupplierData['LateRequestNumber'].' đã quá '.$WarnSupplierThreshold.' ngày.'."\r\n".
						'Rất mong bạn nhanh chóng đăng nhập vào trang web Nghiencuusinh.org để xử lý các yêu cầu đã quá hạn. Cám ơn bạn.	';
			if (do_send($email,$username,'Ban dang co yeu cau da qua han tai nghiencuusinh.org',$message))
			{
				echo"Email to supplier ".$arrSupplierData['username'].": done...<br />\r\n";
			}
			else
			{
				echo"Email to supplier ".$arrSupplierData['username'].": failed...<br />\r\n";
			}
		}
	}
		
}
?>