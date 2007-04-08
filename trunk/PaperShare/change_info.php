<?php
include "chk_login.inc";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<?php
if (logged_in())
{
	include "config.php";
	include "dbconnect.php";
	$strMysqlQuery = "SELECT * FROM $strTableUserName WHERE (email='".$_POST['frmNewEmail']."') AND (username!='".$_SESSION["username"]."')";
	$result = mysql_query($strMysqlQuery);
	if (mysql_num_rows($result)!==0)
	{
		$_SESSION["ErrMess"]="Email đã được người khác chọn!";
	echo '<script language="javascript"> window.location=\'account.php?type=change\';</script>';
	}
	$strMysqlQuery = "UPDATE $strTableUserName SET username='".$_POST['frmUsername']."', email='".$_POST['frmNewEmail']."', field='".$_POST['frmField']."'";
	if ($_POST['frmNewPassword']!=='')
	{
		$strCryptedPassword = crypt($_POST['frmNewPassword']);
		$strMysqlQuery .=", password='$strCryptedPassword'";
	}
	$strMysqlQuery .=" WHERE username = '".$_SESSION['username']."'";
	//echo $strMysqlQuery;
	mysql_query($strMysqlQuery) or die(mysql_error());
	echo "<center>Thay đổi thông tin thành công. Đang quay về trang cá nhân...</center>";
	echo '<script language="javascript"> setTimeout("window.location='."'account.php'".'",3000);</script>';
}
else
{
	echo "Dump!";
}
?>

</head>

<body>
</body>

</html>
