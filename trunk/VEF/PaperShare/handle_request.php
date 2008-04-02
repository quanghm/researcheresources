<?php
include "chk_login.inc";
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<?php
/*$needle='http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF'])."/account.php";
$haystack=$_SERVER['HTTP_REFERER'];
if (strstr($haystack,$needle)==FALSE)
{die("invalid referer");}
*/
if (!(logged_in()))
{
echo "<center>Bạn chưa đăng nhập! Đang quay trở lại trang chủ...</center>";
die('<meta http-equiv="refresh" content="3;url=index.php">');
}
include "config.php";
include "dbconnect.php";
include($strIncDir."sendmail/mail.php");

	/////////	Get Request's detail
	$strMysqlQuery = "SELECT * FROM $strTableRequestName WHERE id=".$_POST['frmHandlingRequestID'];
	$result = mysql_query($strMysqlQuery) or die(mysql_error());
	$arrRequestData=mysql_fetch_array($result) or die(mysql_error());

	/////	Get Requester's detail
	$strMysqlQuery = "SELECT * FROM $strTableUserName WHERE (username = '".$arrRequestData['requester']."')";
	$result = mysql_query($strMysqlQuery) or die(mysql_error());
	$arrRequesterData = mysql_fetch_array($result);

if (!isset($_GET['action']))
{
	$_GET['action']="";
}
if ($_GET['action']=='finishing')	//	Successfully found paper and send
{
	/////////// increase number of requests handled
	$strMysqlQuery = "UPDATE $strTableUserName 
					  SET request_handle_number = request_handle_number + 1 
					  WHERE (username = '".$_SESSION['username']."')";
	mysql_query($strMysqlQuery) or die(mysql_error());
	
	//	decrease request pending number
	$strMysqlQuery = "UPDATE $strTableUserName
					  SET request_pending_number = request_pending_number - 1
					  WHERE (username = '".$arrRequestData['supplier']."')";
	mysql_query($strMysqlQuery) or die (mysql_error());

	/////*//////  Chage status of request to finished
	$strMysqlQuery = "UPDATE $strTableRequestName 
					  SET status = -1, supplier='".$_SESSION['username']."' 
					  WHERE id=".$_POST['frmHandlingRequestID'];
	mysql_query($strMysqlQuery) or die(mysql_error());	
	
	///////////	 Return to User's page
	echo '<script language="javascript"> window.location="account.php?type=request";</script>';
}
elseif ($_GET['action']=='passing')	//	pass paper to another user
{	
	///////// Assign a new supplier ////////////
		////////	Get the previous supplier and put to array $arrPreviousSuppliers
		parse_str($arrRequestData['previous_suppliers']);
		
		///////		Get list of available suppliers
		if ((isset($_POST['frmSupplier']))&&($_POST['frmSupplier']!==""))	////	New supplier indicated
		{
			if ($_POST['frmSupplier']==$_SESSION['username'])
			{
				$_SESSION['ErrMes']="Bạn không thể chuyển bài báo cho chính bạn.";
				echo '<form name="frm1" method="POST" action="account.php?type=handle_request">
									<input type="hidden" name="frmRequestID" value="'.$arrRequestData['id'].'"/>
									</form>';
				die("<script language=\"javascript\"> document.frm1.submit();</script>");
			}
			
		////	Test the availability of the chosen supplier
			
			//	New supplier coincides with the requester
			if ($_POST['frmSupplier'] == $arrRequestData['requester']) 
			{
				$_SESSION['ErrMes']="Người cung cấp bạn chọn là người đề nghị bài báo. Xin hãy chọn người cung cấp khác.";
				echo '<form name="frm1" method="POST" action="account.php?type=handle_request">
									<input type="hidden" name="frmRequestID" value="'.$arrRequestData['id'].'"/>
									</form>';
				die("<script language=\"javascript\"> document.frm1.submit();</script>");
			}
			
			//	The indicated supplier is one of the previous suppliers for the request.
			for ($i=0; $i<=$arrRequestData['status']; $i++)	
			{
				if ($_POST['frmSupplier']==$arrPreviousSuppliers[$i])
				{
					$_SESSION['ErrMes'] = $arrPreviousSuppliers[$i]."đã không tìm được bài báo này. Xin hãy chọn người cung cấp khác.";
					echo('<form name="frm1" method="POST" action="account.php?type=handle_request">
										<input type="hidden" name="frmRequestID" value="'.$arrRequestData['id'].'"/>
										</form>');
					die("<script language=\"javascript\"> document.frm1.submit();</script>");
				}
			}

			$strMysqlQuery = "SELECT * FROM $strTableUserName WHERE username='".$_POST['frmSupplier']."'";
			$result = mysql_query($strMysqlQuery) or die(mysql_error());
			$arrSupplierData = mysql_fetch_array($result);
			
			if ($arrSupplierData === false)  // No such supplier
			{
				$_SESSION['ErrMes'] = "Người cung cấp bạn chọn không tồn tại. Xin hãy chọn người cung cấp khác.";
				echo('<form name="frm1" method="POST" action="account.php?type=handle_request">
										<input type="hidden" name="frmRequestID" value="'.$arrRequestData['id'].'"/>
										</form>');
				die("<script language=\"javascript\"> document.frm1.submit();</script>");
			}
			
			//	check new requester's field
			if (($cross_field_request==false)and($arrSupplierData['field']!==$arrRequestData['field']))
			{
				$_SESSION['ErrMes'] = "Người cung cấp bạn chọn không theo chuyên ngành của bài báo. Xin hãy chọn người cung cấp khác.";
				echo('<form name="frm1" method="POST" action="account.php?type=handle_request">
										<input type="hidden" name="frmRequestID" value="'.$arrRequestData['id'].'"/>
										</form>');
				die("<script language=\"javascript\"> document.frm1.submit();</script>");
			}
		}
		else	//// No new supplier indicated
		{
			$strMysqlQuery = "SELECT * FROM $strTableUserName WHERE ";
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
		}
				
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
			echo "<center>Đang quay lại trang cá nhân...</center>";
			die('<meta http-equiv="refresh" content="3;url=account.php?type=request">');
		}
		
	//assign request to new supplier
		//update list of previous suppliers
		$strPreviousSuppliers = $arrRequestData['previous_suppliers'].'arrPreviousSuppliers[]='.$arrRequestData['supplier'].'&';
		
		$strMysqlQuery ="UPDATE $strTableRequestName ". 
						"SET previous_suppliers = '".$strPreviousSuppliers."', " .
						"status = status + 1, supplier = '".$arrSupplierData['username']."' " .
						"WHERE id = ".$arrRequestData['id'];
		mysql_query($strMysqlQuery) or die(mysql_error());
	
		/////// update new supplier's data
		$last_assigned_request = date('YmdHis');
		$strMysqlQuery = "UPDATE $strTableUserName ".
						 "SET request_pending_number = request_pending_number +1, last_assigned_request = $last_assigned_request ".
						 "WHERE  (user_level='1') AND username = '".$arrSupplierData['username']."'";
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

		////////////////////////////////////////////////////////////////
		echo "<center> Chuyển yêu cầu thành công! Đang quay trở lại trang cá nhân...</center>";
		echo ('<script language ="javascript">setTimeout("history.go(-2)",2000);</script>');
}
elseif ($_GET['action']=='failing')
{
	//////////	Change request's status to failed
	$strMysqlQuery = "UPDATE $strTableRequestName SET status = -2 WHERE id=".$_POST['frmHandlingRequestID'];
	mysql_query($strMysqlQuery) or die(mysql_error());	
	
	///////// Decrease request_pending_number
	$last_assigned_request = str_replace("-","",$arrRequestData['date_request']).'235959';
	$strMysqlQuery ="UPDATE $strTableUserName " .
					"SET request_pending_number = request_pending_number - 1,last_assigned_request =$last_assigned_request " .
					"WHERE (username = '".$arrRequestData['supplier']."')";
	mysql_query($strMysqlQuery) or die(mysql_error());
	
	///////////  Inform requester about failure of request
		///////  Get requester's email
	$emlTo = $arrRequesterData['email'];
	$Subject	= "Khong tim duoc bai bao cua ban";
	
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
	if (do_send($emlTo,$arrRequesterData['username'], $Subject, $message))
	{
		echo" Send email to ".$arrRequesterData['username'].": DONE.<br>\n";
	}
	else
	{
		echo (" Send email to ".$arrRequesterData['username'].": FAILED.<br>\n");
	}

	///////////	 Return to User's page
	echo '<script language="javascript"> history.go(-2);</script>';
}
else
{
	echo 'Chả hiểu là phải làm gì! Đang quay lại trang chủ...';
	echo '<meta http-equiv="refresh" content="3;url=index.php">';
}
?>
</body>
</html>
