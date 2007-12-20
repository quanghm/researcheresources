<?php
include "chk_login.inc";
if ((logged_in())&& (!isset($strConn)))
{
	include "config.php";
	include "dbconnect.php";
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="../../inetpub/wwwroot/paper_share/Templates/paper_share.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Untitled Document</title>
<!-- InstanceEndEditable -->
<?php echo'<link href="Theme/Default/style.css" rel="stylesheet" type="text/css" />'; ?>
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
</head>
<body>
<table width="999" border="0" align="center">
  <tr align="center">
    <td colspan="2">
	<?php include "menu.php"; ?>
    </td>
  </tr>
  <tr >
    <td width="70%" height="700" valign="top">
	<!-- InstanceBeginEditable name="body" -->
<script language="javascript">
function DataVerify(form)
{
	if (form.txtFAQ.value.length =0)
	{
		alert("Bạn chưa nhập câu hỏi!");
		form.txtFAQ.focus();
		return false;
	}
	return true;
}
</script>
<?php
if (!isset($_GET['quest']))
	{	
		$_GET['quest']="";
	}
if ($_GET['quest']!=='send')
{
	include"faq_process.php";
}
else
{
	if (strlen($_POST['txtFAQ'])==0)
	{
		$_SESSION['ErrMesFeedback']="Bạn cần nhập câu hỏi trước khi gửi.";
		die("<script languague='javascript'>window.location='faq.php'</script>");
	}
	$strInsertQuery = "INSERT INTO $strTableUserName (username, question, answer) VALUES ('".$_SESSION["username"]."', '".$_POST['txtFAQ']."', 'wait')";
	mysql_query($strInsertQuery) or die(mysql_error());
	echo "<center>Cảm ơn ".$_SESSION["username"]." đã gửi câu hỏi, BQT sẽ trả lời ngay sau khi nhận được câu hỏi này!</center>";
	include "dbclose.php";
}
?>
<?php
include "config.php";
include "dbconnect.php";
$strMysqlQuery="SELECT * FROM ".$strTableFAQ." where answer!='wait'";
$arrFAQlist = mysql_query($strMysqlQuery) or die(mysql_error());
$ArticleIndex=1;
echo "<table>";
while ($arrFAQData = mysql_fetch_array($arrFAQlist))
{
echo "<tr";		
	echo ">
			<td >".$ArticleIndex++."</td>\n";		
	echo "      <td >".$arrFAQData['question']."</td>\n";					
	echo "  </tr>";
}
echo "  </tr></table>\n";
?>
<!-- InstanceEndEditable --></td>
<td width="30%" align="left" valign="top" bgcolor="#CCCC66"><?php
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
    <td colspan="2" valign="top" align="center"><!-- Google CSE Search Box Begins  -->
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
