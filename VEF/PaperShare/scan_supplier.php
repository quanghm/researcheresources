<?php
include "config.php";
include $strIncDir."sendmail/mail.php";
include "dbconnect.php";

/////////////////////////////////
function compareDate($i_sFirstDate, $i_sSecondDate)
{
	//Break the Date strings into seperate components
	$arrFirstDate = explode ("-", $i_sFirstDate);
	$arrSecondDate = explode ("-", $i_sSecondDate);
	
	$intFirstYear = $arrFirstDate[0];
	$intFirstMonth = $arrFirstDate[2];
	$intFirstDay = $arrFirstDate[1];
	
	$intSecondYear = $arrSecondDate[0];
	$intSecondMonth = $arrSecondDate[2];
	$intSecondDay = $arrSecondDate[1];
	
	// Calculate the diference of the two dates and return the number of days.
	$intDate1 = gregoriantojd($intFirstDay, $intFirstMonth, $intFirstYear);
	$intDate2 = gregoriantojd($intSecondDay, $intSecondMonth, $intSecondYear);
	
	return $intDate1 - $intDate2;
}//end Compare Date
/////////////////////////////////
function PassRequest($arrRequestData)
{
	include "config.php";	
	//	Get Requester's Data
	$strMysqlQuery =	"SELECT * FROM $strTableUserName ".
						"WHERE (username = '".$arrRequestData['requester']."'";
	$SelectRequesterResult = mysql_query($strMysqlQuery);
	$arrRequesterData = mysql_fetch_array($SelectRequesterResult);
	
	//	Get list of previous suppliers
	parse_str($arrRequestData['previous_suppliers']);
	
	///////		Get list of available suppliers
	$strMysqlQuery = "SELECT * FROM $strTableUserName WHERE (user_level>=1) AND ";
	if ($cross_field_request==false)
	{
		$strMysqlQuery.="(field = '".$arrRequestData['field']."') AND ";
	}
	$strMysqlQuery .= "(supplier = 1) AND (username != '".$arrRequestData['requester']."') AND (username != '".$_SESSION['username']."') ";
	
	for ($i=0; $i<$arrRequestData['status']; $i++)
	{
		$strMysqlQuery .= "AND (username !='".$arrPreviousSuppliers[$i]."') ";
	}
	$strMysqlQuery .= "ORDER BY last_assigned_request ASC, request_handle_number ASC, request_pending_number ASC";
	$result = mysql_query($strMysqlQuery) or die(mysql_error());
	$arrSupplierData = mysql_fetch_array($result);
	
	///////////////////////
	if ($arrSupplierData === false)			//No supplier found
	{
		echo "<center>Chuyển yêu cầu không thành công. Không tìm được người cung cấp.</center>";
		///// 	Change request's status to Failed
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
		Xin hãy đăng nhập vào trang web <a href=\"$strWebsiteName\">$strWebsiteName </a> để biết thêm chi tiết.
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
	else
	{
		//assign request to new supplier
		//update list of previous suppliers
		$strPreviousSuppliers = $arrRequestData['previous_suppliers'].'arrPreviousSuppliers[]='.$arrRequestData['supplier'].'&';
		
		//	Get date
		$today = date('Y-m-d');
		
		//	Update Request's Data
		$strMysqlQuery ="UPDATE $strTableRequestName ". 
						"SET previous_suppliers = '".$strPreviousSuppliers."', date_assigned = $today, " .
						"status = status + 1, supplier = '".$arrSupplierData['username']."' " .
						"WHERE id = ".$arrRequestData['id'];
		mysql_query($strMysqlQuery) or die(mysql_error());
	
		/////// update new supplier's data
		$last_assigned_request = date('YmdHis');
		$strMysqlQuery = "UPDATE $strTableUserName ".
						 "SET request_pending_number = request_pending_number +1, last_assigned_request = $last_assigned_request ".
						 "WHERE  (user_level>=1) AND username = '".$arrSupplierData['username']."'";
		mysql_query($strMysqlQuery) or die(mysql_error());
		
		// Update previous supplier's data
		$last_assigned_request = str_replace("-","",$arrRequestData['date_request']).'235959';
		$strMysqlQuery=	"UPDATE $strTableUserName " .
						"SET request_pending_number = request_pending_number -1, last_assigned_request=$last_assigned_request " .
						"WHERE (username='".$arrRequestData['supplier']."')";
		mysql_query($strMysqlQuery) or die(mysql_error());
		
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
		Xin hãy đăng nhập vào trang web <a href=\"$strWebsiteName\">$strWebsiteName </a> để biết thêm chi tiết.
		</body>
		</html>";
		if (do_send($emlTo, $arrRequestData['requester'],$Subject, $message))
		{
			echo" Send email to ".$arrRequesterData['username']." at".$arrRequesterData['email']." : DONE.<br>\n";
		}
		else
		{
			echo(" Send email to ".$arrRequesterData['username'].": FAILED.<br>\n");
		}	

	}
}	
function DisableSupplier($arrSupplierData)
{
	include "config.php";
	//	Get list of pending requests for the supplier
	$strMysqlQuery =	"SELECT * FROM $strTableRequestName ".
						"WHERE (supplier='".$arrSupplierData['username']."')";
	echo $strMysqlQuery."<br/>";
	$SelectRequestResult = mysql_query($strMysqlQuery) or die(mysql_error());
	
	//	Pass all pending requests
	while ($arrRequestData = mysql_fetch_array($SelectRequestResult))
	{
		PassRequest($arrRequestData);
	}
	
	//	Disable supplier
	$strMysqlQuery = 	"UPDATE $strTableUserName ".
						"SET supplier =0 WHERE (username = '".$arrSupplierData['username']."')";
	echo $strMysqlQuery;
	mysql_query($strMysqlQuery) or die(mysql_error());
}
//Get current date
$today = date('Y-m-d');

//	Scan Supplier
$strMysqlQuery =	"SELECT * FROM $strTableUserName ". 
				 	"WHERE	(supplier=1)";
echo $strMysqlQuery."<br/>";
$SelectSupplierResult = mysql_query($strMysqlQuery) or die(mysql_error());

while ($arrSupplierData = mysql_fetch_array($SelectSupplierResult))		//Scan pending request for current supplier
{
	$strMysqlQuery=	"SELECT * FROM $strTableRequestName ".
					"WHERE	(supplier ='".$arrSupplierData['username']."') AND (status>-1) ".
					"ORDER BY date_assigned ASC";
	echo $strMysqlQuery." ";
	$SelectRequestResult = mysql_query($strMysqlQuery) or die(mysql_error());
	echo mysql_num_rows($SelectRequestResult)."<br/>";
	if (mysql_num_rows($SelectRequestResult)>0)
	{
		$arrOldestRequestData = mysql_fetch_array($SelectRequestResult) or die(mysql_error());
		echo $DateDifference = compareDate($today,$arrOldestRequestData['date_assigned']);

		if ($DateDifference>=$DisableSupplierThreshold-2)
		{
			DisableSupplier($arrSupplierData);
		}
		if ($DateDifference>=$WarnSupplierThreshold)
		{
			include "warn_supplier.php";
		}
		if ($DateDifference>=1)
		{
			include "remind_supplier.php";
		}
	}
}
?>