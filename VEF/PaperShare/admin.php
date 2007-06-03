<?php
	include "chk_login.inc";
	if (!isset($_GET['action']))
	{
		$_GET['action']='';
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/paper_share.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Trang chu</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
<?php echo'<link href="Theme/Default/style.css" rel="stylesheet" type="text/css" />'; ?>
</head>

<body>
<table width="999" border="0" align="center">
  <tr bgcolor="#CCCC66" align="center">
    <td width="33%" height="40" nowrap="nowrap" ><?php echo "<a href=\"index.php\" class=\"menu\">"?><span class="menu">Trang chủ</span><?php echo"</a>"; ?></td>
    <td width="33%" height="40" >
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
    <td height="40" colspan="2" > <?php echo "<a href=\"about.php\" class=\"menu\">Về chúng tôi</a>"; ?></td>
  </tr>
  <tr >
    <td width="70%" height="700"colspan="3" valign="top">
<!-- InstanceBeginEditable name="body" -->
<?php
if (!logged_in())
{
	$_SESSION['ErrMess']="Bạn chưa đăng nhập!";
	echo '<script language="javascript" > setTimeout("window.location=\'index.php\'",3000);</script>';
}
else	//start admin 
{
	include "config.php";
	include "dbconnect.php";
	$strMyQuery = "SELECT * FROM $strTableUserName WHERE username = '".$_SESSION['username']."'";
	$result = mysql_query($strMyQuery) or die(mysql_error());
	$arrUserData = mysql_fetch_array($result);
	if (!$arrUserData['admin']) // user is not an admin
	{
		echo "Bạn không phải người quản trị	!";
		echo '<script language="javascript" > setTimeout("window.location=\'account.php\'",3000);</script>';
	}
	elseif ($_GET['action']=='mail')	// Send email to suppliers
	{	
		$today = date("Y-m-d");
		$strMyQuery = "SELECT * FROM $strTableAdmin WHERE lastupdate >= '$today' ORDER BY lastupdate DESC";
		$result = mysql_query($strMyQuery) or die (mysql_error());
		if (mysql_num_rows($result)>0)		// updated
		{
			$arrLastUpdate = mysql_fetch_array($result);
			echo "Email đã được gửi bởi ".$arrLastUpdate['username']." vào ngày ".$arrLastUpdate['lastupdate'];

		}
		else
		{	$strMyQuery = "INSERT INTO $strTableAdmin (lastupdate, username) VALUES ('$today', '".$_SESSION['username']."')";
			mysql_query($strMyQuery) or die(mysql_error());
			////	Get List of suppliers that have pending requests
			$strMyQuery = "SELECT * FROM $strTableUserName WHERE (supplier =1) AND (request_pending_number>0)";
			$result = mysql_query($strMyQuery) or die(mysql_error());
			if (mysql_num_rows($result) == 0)
			{
				echo "Hiện không có yêu cầu nào đang chờ!";
			}
			else while ($arrSupplierData = mysql_fetch_array($result))
			{
			//////	Supplier found, sending maili
				$message = "<html>
	<head>
	<title>Bạn có email đang chờ</title>
	</head>
	
	<body>
	Đây là email tự động gửi từ ban quản trị của $strWebsiteName.
				Hiện thời bạn có ".$arrSupplierData['request_pending_number']." yêu cầu cần giải quyết.
				
				Xin hãy đăng nhập vào trang web <a href=\"".dirname($_SERVER['PHP_SELF'])."\">$strWebsiteName </a> để xử lý các yêu cầu.
	</body>
	</html>";
				$To = $arrSupplierData['email'];
				$Subject = "Bạn có yêu cầu đang chờ ở $arrSupplierData";
				$Headers = "content-type: text/html, charset= utf-8\r\n";
				$Headers = "From: ".$strAdminEmail;
				if (mail($To, $Subject, $message, $Headers))
				{
					echo" Send email to ".$arrSupplierData['username'].": DONE.<br>\n";
				}
				else
				{
					echo (" Send email to ".$arrSupplierData['username'].": FAILED.<br>\n");
				}
			}
		}
	echo "\n".'<br /><button onClick="javascript:window.location=\'admin.php\'">Quay lại trang điều khiển</button>';
	}
	else
	{
		echo '<a href="admin.php?action=mail">Gửi email nhắc việc cho các suppliers</a><br />'."\n";
		echo '<a href="admin.php?action=admins"> Sửa danh sách admins </a><br />'."\n";
		echo '<a href="admin.php?action=users"> Danh sách thành viên </a><br />'."\n";
		echo '<a href="admin.php?action=requests"> Danh sách bài báo </a><br />'."\n";
	}
}
	// Make a MySQL Connection
	///////////////////////////////////////////////////////

?>

<!-- InstanceEndEditable -->	
	</td>
    <td width="30%" align="center" valign="top" bgcolor="#CCCC66"><?php
		if (logged_in())
		{
			//////////// Select user from database /////////////
	$strMyQuery = "SELECT * FROM $strTableUserName WHERE username = '".$_SESSION['username']."'";
	$result = mysql_query($strMyQuery) or die(mysql_error());
	$arrUserData = mysql_fetch_array($result);
	////////////////////////////////////////////////////

			echo "Chào mừng ".$_SESSION["username"]."!<button onClick=\"javascript:window.location = 'login.php?action=logout'\">Khắc xuất</button><br>\n";

		echo "Bạn đã gửi ".$arrUserData['request_number']." yêu cầu! <a href=\"account.php?type=submit_request\">Yêu cầu bài báo</a><br>\n";
		if ($arrUserData['supplier']) 
		{
			////////	Get list of requests pending	/////////////
			$strMysqlQuery = "SELECT * FROM $strTableRequestName WHERE (supplier = '".$_SESSION['username']."') AND (status >=0)";
			$result = mysql_query($strMysqlQuery) or die(mysql_error());
			$request_pending = mysql_num_rows($result);
			if ($request_pending>0)
			{	echo "Hiện tại bạn có ".$request_pending." yêu cầu đang chờ <a href=\"account.php?type=request\">xử lý!</a><br>\n";
			}
			else
			{
				echo "Hiện tại bạn không có yêu cầu nào đang chờ!<br>\n";
			}
		}
		echo "<a href=\"account.php?type=change\"> Thay đổi thông tin cá nhân </a><br>";			
		if ($arrUserData['admin']){echo "<a href=\"admin.php?action=mail\"> Gửi email nhắc việc tới suppliers </a>";}
			//////// Close connection to database /////////
			include "dbclose.php";
		}
		else
		{	
			echo "Bạn chưa đăng nhập";
			require "login_form.inc";

		}
	?></td>
  </tr>
</table>
<center>Beta version! Please send feedback to: <a href="mailto:admin@articleexchange.byethost7.com">admin@articleexchange.byethost7.com</a></center>
</body>
<!-- InstanceEnd --></html>
