<?php
include "chk_login.inc";
if (!isset($_GET['action']))
{
	$_GET['action']='hura';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>
<table width="400" border="0" align="center">
  <tr>
    <td align="center"><?php
/////////////////////////////
////	 logging out	 ////
/////////////////////////////
$strErrMessage = "Bí danh hoặc mật khẩu không chính xác!<br>Nếu bạn quên mật khẩu, vào <a href=\"forgotpassword.php\">đây</a> để lấy lại";
if ($_GET["action"]=="logout")
{
	if (isset($_SESSION["username"]))
	{
		include "config.php";
		include "dbconnect.php";
		unset($_SESSION["username"]);
		unset($_SESSION["password"]);
		echo "<div align=\"center\"> Logged out successfully!<br> Directing back to Homepage</div>";
		mysql_close($strConn);
	}
	echo '<script language="javascript"> setTimeout("window.location='."'index.php'".'",3000);</script>';
}
///////////////////////////////////
///////     logging in      ///////
///////////////////////////////////
elseif ($_GET["action"] == "login")
{
	include "config.php";
	include "dbconnect.php";
	
	////////////////////////////////////////////////////
	//////////// Select user from database /////////////
	$strMyQuery = "SELECT * FROM $strTableUserName WHERE username = '$_POST[frmUsername]'";
	$result = mysql_query($strMyQuery) or die(mysql_error());
	////////////////////////////////////////////////////
	/////////// Determine if user exists  //////////////
	if (mysql_num_rows($result)!==1)
	{
		$_SESSION["ErrMes"] = $strErrMessage;
		echo "<script language=\"javascript\">";
		echo "history.back();";
		echo "</script>";
	}
	else
	{	
		$row = mysql_fetch_array($result);
		if ($row['user_level']==1)
		{
			$strPasswordOnServer = $row["password"];
			if (crypt($_POST["frmPassword"],$strPasswordOnServer) == $strPasswordOnServer)
			{
				$_SESSION["username"] = $_POST["frmUsername"];
				$_SESSION["password"] = $strPasswordOnServer;
				echo "logged in successfully";
				echo "<script language=\"javascript\">";
				echo "window.location = \"index.php\";";
				echo "</script>";
			}
			else
			{
				$_SESSION["ErrMes"] = $strErrMessage;
				echo "<script language=\"javascript\">";
				echo "history.back();";
				echo "</script>";
			}
		}
		elseif ($row['user_level']==0)
		{
				$_SESSION["ErrMes"] = "Bạn phải checkmail kích hoạt trước khi đăng nhập!";
				echo "<script language=\"javascript\">";
				echo "history.back();";
				echo "</script>";
		}
	}
}
else
////////////////////////////////////////////
///////     Display loggin form     ////////
////////////////////////////////////////////
{
	include "login_form.inc.php";
	/*
	if ($_SESSION["ErrMes"]!=="")
	{
		echo $_SESSION["ErrMes"];
		$_SESSION["ErrMes"]="";
	}
	echo "<form method=\"POST\" action=\"login.php?action=login\">";
	echo "<table width=\"300\">\n";
	echo "<tr>\n";
	echo "<td width=\"30%\"> Bí danh </td> <td><input type=\"text\" name=\"frmUsername\"></td> ";
	echo "</tr> \n <tr>\n";
	echo "<td width=\"30%\"> Mật khẩu </td> <td><input type=\"password\" name=\"frmPassword\"></td> ";		
	echo "</tr>";
	echo "<tr> <td></td><td><input type=\"submit\" value=\"Vừng ơi mở ra\"></td></tr>";
	echo "</table>";
	echo "</form>";
	echo "<a href=\"register.php\">Đăng ký tài khoản</a> \n";
	*/
}
?>
</td>
  </tr>
</table>
<p>&nbsp;</p>
</body>
</html>
