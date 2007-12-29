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
	// $userfile is where file went on webserver 
	/*if (!$store_article_on_server){
		echo "Không được phép upload!";	
		echo '<script language="javascript"> window.location="account.php?type=request";</script>';	
		exit;
	}*/
	$userfile = $HTTP_POST_FILES['userfile']['tmp_name']; 
	// $userfile_name is original file name 
	$userfile_name = $HTTP_POST_FILES['userfile']['name'];
	// $userfile_size is size in bytes 
	$userfile_size = $HTTP_POST_FILES['userfile']['size']; 
	// $userfile_type is mime type e.g. image/gif 
	$userfile_type = $HTTP_POST_FILES['userfile']['type'];
	$userfile_error = $HTTP_POST_FILES['userfile']['error'];
	if (!preg_match('/\.(pdf|doc|xls)$/i',$userfile_name))
	{
	echo "Loi kieu file upload.";	
	echo '<script language="javascript"> window.location="account.php?type=request";</script>';
	exit;	
	}
	// $userfile_error is any error encountered 	 	
	// userfile_error was introduced at PHP 4.2.0 
	// use this code with newer versions 
	
	if ($userfile_error > 0) { 
	echo 'Problem: '; 
	switch ($userfile_error) 
	{ case 1: 
	echo 'File exceeded upload_max_filesize'; 
	break; 
	case 2: 
	echo 'File exceeded max_file_size'; 
	break; 
	case 3: 
	echo 'File only partially uploaded'; 
	break;
	case 4: 
	echo 'No file uploaded'; 
	break; 
	} 
	exit; 
	} 		
	// put the file where we'd like it 
	$upfile = 'upload/'.$userfile_name; 
	$random_digit=rand(0000,9999);
	$upfile_new=$random_digit.$userfile_name;
	if(file_exists($upfile))
	{
		if(copy($HTTP_POST_FILES['userfile']['tmp_name'], "upload/".$upfile_new)){
		echo 'File uploaded successfully<br /><br />';}
		else {
		echo 'Problem<br /><br />';
		ẽexit;
		}
	}
	else
	{
		$upfile_new=$userfile_name;
		if(copy($HTTP_POST_FILES['userfile']['tmp_name'], $upfile)){
		echo 'File uploaded successfully<br /><br />';}
		else {echo 'Problem<br /><br />';
			ẽexit;
		}
	}
	/*
	if (file_exists($upfile)) 
	{
		rename(is_uploaded_file($random_digit.$userfile),
	}
	else
		// is_uploaded_file and move_uploaded_file 
		if(is_uploaded_file($userfile))
		{
			echo 'File uploaded successfully<br /><br />';
		}
		else
		{
			echo 'Problem: Possible file upload attack. Filename: '.$userfile_name; 
			exit;
		} 
	} 	 
	*/
	// show what was uploaded 
	echo 'Preview of uploaded file contents:<br /><hr />'; 
	echo $upfile_new;
	echo '<br /><hr />';
	/////////// increase number of requests handled AND decrease number of requests pending
	$strMysqlQuery = "UPDATE $strTableUserName SET request_handle_number = request_handle_number + 1, request_pending_number = request_pending_number - 1  WHERE (username = '".$_SESSION['username']."')";
	mysql_query($strMysqlQuery) or die(mysql_error());

	/////*//////  Chage status of request to finished
	$strMysqlQuery = "UPDATE $strTableRequestName SET status = -1, stored_link='http://nghiencuusinh.org/upload/".$upfile_new."' WHERE id=".$_POST['frmHandlingRequestID'];
	mysql_query($strMysqlQuery) or die(mysql_error());	
	
	////send email to requester
	$strMysqlQuery = "SELECT * FROM $strTableUserName WHERE username='".$_POST['frmHandlingRequestName']."'";
	$result = mysql_query($strMysqlQuery) or die(mysql_error());	
	if ($arrRequesterData=mysql_fetch_array($result))
	{
		$strEmailTo=$arrRequesterData['email'];
		$strSubject="Xin chào: ".$arrRequesterData['username'];
		$Headers="From: ".$strAdminEmail."\r\n";
		$Headers .= "MIME-Version: 1.0\r\n"; 
		$Headers .= "content-type: text/html; charset=utf-8\r\n";
		$strDir=dirname($_SERVER['PHP_SELF']);
		$message = "<html>
		<head>
		<title>Xin chào ".$arrRequesterData['username']."</title>
		</head>
		<body>
		Đây là email tự động gửi từ ban quản trị của $strWebsiteName.<br/>
		Bầi báo của bạn đã được xử lý. <a href=\"http://nghiencuusinh.org/upload/".$upfile_new."\">Click vào đây để tải về</a><br/>".
		"Chúng tôi rất mong nhận được sự đóng góp thường xuyên của bạn cho trang web.
		</body>
		</html>";
		do_send($arrRequesterData['email'],$arrRequesterData['username'],$strSubject,$message);
	}	
	///////////	 Return to User's page
	echo '<script language="javascript"> window.location="account.php?type=request";</script>';
}
elseif ($_GET['action']=='passing')	//	pass paper to another user
{	
	///////// Increase user's number of requests
	//$strMysqlQuery = "UPDATE $strTableUserName SET request_number = request_number + 1  WHERE (username = '".$_SESSION['username']."')";

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
			if ($_POST['frmSupplier'] == $arrRequestData['requester']) //	New supplier coincides with the requester
			{
				$_SESSION['ErrMes']="Người cung cấp bạn chọn là người đề nghị bài báo. Xin hãy chọn người cung cấp khác.";
				echo '<form name="frm1" method="POST" action="account.php?type=handle_request">
									<input type="hidden" name="frmRequestID" value="'.$arrRequestData['id'].'"/>
									</form>';
				die("<script language=\"javascript\"> document.frm1.submit();</script>");
			}
			for ($i=0; $i<=$arrRequestData['status']; $i++)	//	The indicated supplier is one of the previous suppliers for the request.
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
			Xin hãy đăng nhập vào trang web <a href=\"".'http://'.$_SERVER['SERVER_NAME'].$strDir."\">$strWebsiteName </a> để biết thêm chi tiết.
			</body>
			</html>";
			echo("<form id='frmSendmail' method='POST' action='incs/sendmail/mail.php'>" .
					"<textarea name='message'>".$message."</textarea>".
					"<input type='hidden' name='subject' value='".$Subject."'/>" .
					"<input type='hidden' name='ToAddress' value='".$arrRequesterData['email']."'/>'" .
					"<input type='hidden' name='ToUser' value='".$arrRequesterData['username']."'/>");
			die("<script language='javascript'>document.getElementById('frmSendmail').submit</script>");
			echo "<center>Đang quay lại trang thông tin cá nhân...</center>";
			die('<meta http-equiv="refresh" content="3;url=account.php?type=request">');
		}
		//assign request to new supplier
		$strPreviousSuppliers = $arrRequestData['previous_suppliers'].'arrPreviousSuppliers[]='.$_SESSION['username'].'&';
		$strMysqlQuery ="UPDATE $strTableRequestName SET previous_suppliers = '".$strPreviousSuppliers."', " .
						"status = status + 1, supplier = '".$arrSupplierData['username']."' " .
						"WHERE id = ".$arrRequestData['id'];
		mysql_query($strMysqlQuery) or die(mysql_error());
	
		/////// update new supplier's data
		$last_assigned_request = date('YmdHis');
		$strMysqlQuery = "UPDATE $strTableUserName SET request_pending_number = request_pending_number +1, last_assigned_request = $last_assigned_request WHERE username = '".$arrSupplierData['username']."'";
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
		$Headers = "From: ".$strAdminEmail."\r\n";
		$Headers .= "MIME-Version: 1.0\r\n"; 
		$Headers .= "content-type: text/html; charset=utf-8\r\n";
	
		$strDir=dirname($_SERVER['PHP_SELF']);
		$message = "<html>
		<head>
		<title>Yêu cầu được chuyển</title>
		</head>
		<body>
		Đây là email tự động gửi từ ban quản trị của $strWebsiteName.<br/>
		Email này nhằm thông báo cho bạn biết có sự chậm trễ trong việc tìm bài báo ".$arrRequestData['title']." bởi ".$arrRequestData['author']." mà bạn đề nghị tại $strWebsiteName. Hiện tại bài báo đang được người cung cấp tiếp theo xử lý.
		Xin hãy đăng nhập vào trang web <a href=\"".'http://'.$_SERVER['SERVER_NAME'].$strDir."\">$strWebsiteName </a> để biết thêm chi tiết.
		</body>
		</html>";
		if (mail($emlTo, $Subject, $message, $Headers))
		{
			echo" Send email to ".$arrRequesterData['username']." at".$arrRequesterData['email']." : DONE.<br>\n";
		}
		else
		{
			echo(" Send email to ".$arrRequesterData['username'].": FAILED.<br>\n");
		}

		////////////////////////////////////////////////////////////////
		echo "<center> Chuyển yêu cầu thành công! Đang quay trở lại trang cá nhân...</center>";
		echo ('<meta http-equiv="refresh" content="3;url=account.php?type=request">');
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
	$Headers = "From: ".$strAdminEmail."\r\n";
	$Headers .= "MIME-Version: 1.0\r\n"; 
	$Headers .= "content-type: text/html; charset=utf-8\r\n";

	$strDir=dirname($_SERVER['PHP_SELF']);
	$message = "<html>
	<head>
	<title>Yêu cầu thất bại</title>
	</head>
	<body>
	Đây là email tự động gửi từ ban quản trị của $strWebsiteName.<br/>
	Chúng tôi không tìm được một trong những bài báo theo yêu cầu của bạn tại $strWebsiteName .<br />
	Xin hãy đăng nhập vào trang web <a href=\"".'http://'.$_SERVER['SERVER_NAME'].$strDir."\">$strWebsiteName </a> để biết thêm chi tiết.
	</body>
	</html>";
	if (mail($emlTo, $Subject, $message, $Headers))
	{
		echo" Send email to ".$arrSupplierData['username'].": DONE.<br>\n";
	}
	else
	{
		echo (" Send email to ".$arrSupplierData['username'].": FAILED.<br>\n");
	}

	///////////	 Return to User's page
	echo '<script language="javascript"> window.location="account.php?type=request";</script>';
}
else
{
	echo 'Chả hiểu là phải làm gì! Đang quay lại trang chủ...';
	echo '<meta http-equiv="refresh" content="3;url=index.php">';
}
include "dbclose.php";
?>
</body>
</html>
