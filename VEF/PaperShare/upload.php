<?php
	// $userfile is where file went on webserver 
	$userfile = $HTTP_POST_FILES['userfile']['tmp_name']; 
	// $userfile_name is original file name 
	$userfile_name = $HTTP_POST_FILES['userfile']['name'];
	// $userfile_size is size in bytes 
	$userfile_size = $HTTP_POST_FILES['userfile']['size']; 
	// $userfile_type is mime type e.g. image/gif 
	$userfile_type = $HTTP_POST_FILES['userfile']['type']; 
	// $userfile_error is any error encountered 
	$userfile_error = $HTTP_POST_FILES['userfile']['error']; 
	
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
	
	// is_uploaded_file and move_uploaded_file 
	if (is_uploaded_file($userfile)) 
	{ 
	if (!move_uploaded_file($userfile, $upfile)) 
	{ 
	echo 'Problem: Could not move file to destination directory'; 
	exit; 
	} 
	} else { 
	echo 'Problem: Possible file upload attack. Filename: '.$userfile_name; 
	exit; 
	} 
	echo 'File uploaded successfully<br /><br />'; 
	
	// show what was uploaded 
	echo 'Preview of uploaded file contents:<br /><hr />'; 
	echo $userfile_name;
	echo '<br /><hr />';
	/////////// increase number of requests handled AND decrease number of requests pending
	$strMysqlQuery = "UPDATE $strTableUserName SET request_handle_number = request_handle_number + 1, request_pending_number = request_pending_number - 1  WHERE (username = '".$_SESSION['username']."')";
	mysql_query($strMysqlQuery) or die(mysql_error());

	/////*//////  Chage status of request to finished
	$strMysqlQuery = "UPDATE $strTableRequestName SET status = -1, stored_link='http://nghiencuusinh.org/upload/".$userfile_name."' WHERE id=".$_POST['frmHandlingRequestID'];
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
		Ðây là email t? d?ng g?i t? ban qu?n tr? c?a $strWebsiteName.<br/>
		B?i báo c?a b?n dã du?c x? lý. <a href=\"http://nghiencuusinh.org/upload/".$userfile_name."\">Click vào dây d? t?i v?</a><br/>".
		"Chúng tôi r?t mong nh?n du?c s? dóng góp thu?ng xuyên c?a b?n cho trang web.
		</body>
		</html>";
		do_send($arrRequesterData['email'],$arrRequesterData['username'],$strSubject,$message);
	}	
	///////////	 Return to User's page
	echo '<script language="javascript"> window.location="account.php?type=request";</script>';
?>
