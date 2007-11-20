<?php
include "chk_login.inc";
if ((logged_in())&& (!isset($strConn)))
{
	include "config.php";
	include $strIncDir."sendmail/mail.php";
	include "dbconnect.php";
}
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
<table width="999" border="0" align="center">
  <tr bgcolor="#CCCC66" align="center">
    <td width="25%" height="40" nowrap="nowrap" ><?php echo "<a href=\"index.php\" class=\"menu\">"?><span class="menu">Trang chủ</span><?php echo"</a>"; ?></td>
    <td width="25%" height="40" >
	<?php 
	if (logged_in())
	{
		echo "<a href=\"account.php\" class=\"menu\">Hồ sơ cá nhân</a>";
	}
	else
	{
		echo "<a href=\"register.php\" class=\"menu\">Đăng ký thành viên</a>";
	}
	?>	</td>
    <td width="25%" ><?php echo "<a href=\"feedback.php\" class=\"menu\">Góp ý</a>"; ?>	</td>
    <td height="40"> <?php echo "<a href=\"about.php\" class=\"menu\">Về chúng tôi</a>"; ?></td>
  </tr>
  <tr >
    <td width="66%" height="700"valign="top" colspan="3">
<!-- InstanceBeginEditable name="body" -->
<?php
/*if (!isset($_GET['action']))
{	
	$_GET['action']="";
}
if ($_GET['action']!=='send')
{*/
	include"feedback.inc.php";
/*}
else 
{
	/*
	 * $needle='http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF'])."/feedback.php";
	$haystack=$_SERVER['HTTP_REFERER'];
	if (strstr($haystack,$needle)==FALSE)
	{
		$_SESSION['ErrMesFeedback']="invalid referer";
		die("<script languague='javascript'>window.location='feedback.php'</script>");
	}
	
	 *****************
	
	if ((strlen($_POST['freeRTE_content'])<5)&&(strlen($_POST['freeRTE_content']!=='Soan thao gop y')))
	{
		$_SESSION['ErrMesFeedback']="Để tránh spam, chúng tôi yêu cầu góp ý phải chứa ít nhất 5 ký tự.";
		die("<script languague='javascript'>window.location='feedback.php'</script>");
	}
	if (do_send($strAdminEmail,"Admin","Annonymous feedback",$_POST['freeRTE_content']))
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
}
/*
 * Created on Oct 17, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
?>
<!-- InstanceEndEditable -->
</td>
    <td width="33%" align="left" valign="top" bgcolor="#CCCC66"><?php
		if (logged_in())
		{
			//////////// Select user from database /////////////
	$strMyQuery = "SELECT * FROM $strTableUserName WHERE username = '".$_SESSION['username']."'";
	$result = mysql_query($strMyQuery) or die(mysql_error());
	$arrUserData = mysql_fetch_array($result);
	////////////////////////////////////////////////////

			echo "Chào mừng ".$_SESSION["username"]."! <button onClick=\"javascript:window.location = 'login.php?action=logout'\">Khắc xuất</button><br><br/>\n";

		echo "Bạn đã gửi ".$arrUserData['request_number']." yêu cầu! <a href=\"account.php?type=submit_request\">Yêu cầu bài báo</a><br>\n";
		if ($arrUserData['supplier']) 
		{
			////////	Get list of requests pending	/////////////
			$strMysqlQuery = "SELECT * FROM $strTableRequestName WHERE (supplier = '".$_SESSION['username']."') AND (status >=0)";
			$result = mysql_query($strMysqlQuery) or die(mysql_error());
			$request_pending = mysql_num_rows($result);
			if ($request_pending>0)
			{	echo "Bạn có ".$request_pending." yêu cầu đang chờ <a href=\"account.php?type=request\">xử lý!</a><br>\n";
			}
			else
			{
				echo "Bạn không có yêu cầu nào đang chờ!<br>\n";
			}
		}
		echo "<br />\r\n <a href=\"account.php?type=change\"> Thay đổi thông tin cá nhân </a><br>";			
		if ($arrUserData['admin']){echo "<a href=\"admin.php\">Đăng nhập trang quản trị</a>";}
			//////// Close connection to database /////////
			include "dbclose.php";
		}
		else
		{	
			echo "<center>Bạn chưa đăng nhập</center>";
			require "login_form.inc.php";

		}
	?></td>
  </tr>
  <tr >
    <td colspan="5" valign="top" align="center"><!-- Google CSE Search Box Begins  -->
<form action="http://www.google.com/cse" id="searchbox_004865859078258633675:18sqvplglto">
  <input type="hidden" name="cx" value="004865859078258633675:18sqvplglto" />
  <input type="text" name="q" size="25" />
  <input type="submit" name="sa" value="Search" />
</form>
<!-- Google CSE Search Box Ends -->
© Copyright 2007 by <?php echo $strWebsiteName?></td>
  </tr>
</table>
<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">
_uacct = "UA-2793588-2";
urchinTracker();
</script>
</body>
<!-- InstanceEnd --></html>
