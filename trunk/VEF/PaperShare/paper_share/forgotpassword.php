<?php
include "chk_login.inc";
if ((logged_in())&& (!isset($strConn)))
{	
	echo "Bạn đã đăng nhập. Xin vào <a href=\"account.php\">trang cá nhân</a> để thay đổi mật khẩu.";
}
if (!isset($_GET['action']))
{
	$_GET['action']='';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Khôi phục mật khẩu</title>
<script language="javascript">
function chkform(form)
{
	if (form.frmUsername.value =="")
	{
		alert("Bạn phải nhập bí danh");
		return false;
	}
	var strEmail = form.frmEmail.value;
	if ((strEmail.indexOf('@')<0)||(strEmail.indexOf('.',strEmail.indexOf('@'))<0))
	{
		alert("Email không hợp lệ!");
		form.frmEmail.focus();
		return false;
	}
	return true;
}
</script>
</head>
<body>
<?php
if ($_GET['action']=='get')
{
	include "config.php";
	include "dbconnect.php";
	
	$strMysqlQuery = "Select * FROM $strTableUserName WHERE username = '".$_POST['frmUsername']."' AND email='".$_POST['frmEmail']."'";
	$result= mysql_query($strMysqlQuery) or die(mysql_error());
	if ($arrUserData = mysql_fetch_array($result))
	{
		////	get new password
		$strNewPassword="";	
		$chars="abcdefghijklmnopqrstuvwxyz0123456789";
		for ($i=1;$i<10; $i++)
		{
			$strNewPassword .=$chars{mt_rand(0,35)};
		}
		$strEncodedPassword = crypt($strNewPassword);
		////	set user's new password
		$strMysqlQuery = "UPDATE $strTableUserName SET password='".$strEncodedPassword."' WHERE username='".$_POST['frmUsername']."'";
		mysql_query($strMysqlQuery) or die(mysql_error());
		////	email new password to user

			$message = "<html>
	<head>
	<title>Mật khẩu mới tại $strWebsiteName</title>
	</head>
	
	<body>
	Đây là email tự động gửi từ ban quản trị của $strWebsiteName.<br/>
	Mật khẩu mới của bạn là <strong>$strNewPassword</strong>. <br/>" .
	"Bạn có thể đăng nhập vào trang cá nhân của bạn tại <a href=\"".'http://'.$_SERVER['SERVER_NAME'].$strDir."\">$strWebsiteName </a> để thay đổi mật khẩu.
	</body>
	</html>";
				$To = $_POST['frmEmail'];
				$Subject = "Mat khau moi tu $strWebsiteName";
				
	include $strIncDir."sendmail/mail.php";		
	
				//mail($To, $Subject, $message, $Headers);
				if (do_send($_POST['frmEmail'],$_POST['frmUsername'],$Subject,$message))
				{
					echo "<center> Password mới đã được gửi đến email bạn dùng để đăng ký! <br/>\r\n" .
						"Đang quay lại trang chủ, bấm vào <a href='index.php'>đây</a> nếu bạn thấy đợi lâu.</center>\r\n";
					echo "<script language='javascript'>setTimeout('window.location=\"index.php\"',3000)</script>";
				}
	}
	else
	{
		$_SESSION['ErrMes']="Bạn đã nhập sai bí danh hoặc email";
		echo("<script language=\"javascript\">window.location='forgotpassword.php'</script>");
	}
	include "dbclose.php";
}
elseif ($_POST['action']=='varify')
{
	echo "dở hơi";
}
else
{
	echo '<center> Khôi phục mật khẩu </center>';
	if ($_SESSION['ErrMes']!=="")
	{
		echo '<center>'.$_SESSION['ErrMes'].'</center>';
		$_SESSION['ErrMes']='';
	}
	echo'<form name="frmPasswordRecovery" action="forgotpassword.php?action=get" method="post" onsubmit="return chkform(this);">
<table align="center" width="400" border="0">
  <tr>
    <td width="50%">Bí danh</td>
    <td><input type="text" name="frmUsername" size="30" /></td>
  </tr>
  <tr>
    <td width="50%">Email</td>
    <td><input type="text" name="frmEmail" size="30"  /></td>
  </tr>
  <tr>
    <td width="50%"><div align="center">
      <input type="submit" value="Gửi mật khẩu"/>
    </div></td>
    <td><div align="center">
      <input type="reset" value="Làm lại" width="50" />
    </div></td>
  </tr>
</table>
</form>';}
?>
</body>
</html>
