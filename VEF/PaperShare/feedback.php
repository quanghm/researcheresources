<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<title>Gửi đóng góp cho trang web</title>
</head>
<body>
<?php
if (!isset($_GET['action']))
{	
	$_GET['action']="";
}
if ($_GET['action']!=='send')
{
	include"feedback.inc.php";
}
else 
{
	$emailto = 'nguyennamhus@yahoo.com';
	$Headers = "From: ".$strAdminEmail."\r\n";
	if (mail($emailto,'feedback',$_POST['txtContent'],$Headers))
	{
		echo "<center>Email đã được gửi. Cám ơn sự đóng góp của bạn. Đang quay lại trang chủ...<br>\r\n" .
				"Nhấn vào <a href='index.php'>đây</a> nếu bạn không muốn đợi lâu.</center>\r\n";
	}
	else
	{
		echo "<center>Có lỗi xuất hiện, email của bạn chưa được gửi. Cám ơn sự quan tâm của bạn. Đang quay lại trang chủ...<br>\r\n" .
				"Nhấn vào <a href='index.php'>đây</a> nếu bạn không muốn đợi lâu.</center>\r\n";
		
	}
		echo "<meta http-equiv=\"refresh\" content=\"5;url=index.php\">";
}
/*
 * Created on Oct 17, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
?>
</body>
</html>