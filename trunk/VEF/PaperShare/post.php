<?php
include "chk_login.inc";
include "config.php";
include $strIncDir."sendmail/mail.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/paper_share.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<!-- InstanceBeginEditable name="doctitle" -->
<title>Untitled Document</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
<?php echo'<link href="Theme/Default/style.css" rel="stylesheet" type="text/css" />'; ?>
</head>

<body>
<?php
	if (strlen($_POST['freeRTE_content'])<15)
	{
		$_SESSION['ErrMesFeedback']="Để tránh spam, chúng tôi yêu cầu góp ý phải chứa ít nhất 15 ký tự.";
		die("<script languague='javascript'>window.location='feedback.php'</script>");
	}

	if (isset($_POST['senderEmail'])&&($_POST['senderEmail']!==''))
	{
		$subject = $_POST['senderEmail'];
	}
	else
	{
		$subject="Annonymous feedback";
	}
	if (do_send($strAdminEmail,"Admin",$subject,$_POST['freeRTE_content']))
	//if (mail($emailto,'feedback',$_POST['txtContent'],$Headers))
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
?></body>
</html>
