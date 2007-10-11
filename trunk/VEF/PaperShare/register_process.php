<?php
include "chk_login.inc";
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Đang xử lí đăng ký</title>
</head>
<body>
<?php
if ($_SERVER['HTTP_REFERER'].$strDir!=='http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF'])."/register.php")
{die("invalid referer");}
if ($_SESSION["username"] != "")
{
	echo "<center> $_SESSION[username]: bạn đã là thành viên! Đang quay trở lại trang cá nhân...</center";
	echo '<meta http-equiv="refresh" content="3; url=account.php">';
}
else
{
	include("config.php");
	include("dbconnect.php");
	//	Store Submitted Data
	echo "<form name=\"frmSubmittedData\" action=\"register.php\" method=\"post\">\r\n";
	echo "<input type=\"hidden\" name=\"frmUsername\" value=\"".$_POST['frmUsername']."\"/>\r\n";
	echo "<input type=\"hidden\" name=\"frmEmail\" value=\"".$_POST['frmEmail']."\"/>\r\n";
	echo "<input type=\"hidden\" name=\"frmEmailConfirm\" value=\"".$_POST['frmEmailConfirm']."\"/>\r\n";
	echo "<input type=\"hidden\" name=\"frmField\" value=\"".$_POST['frmField']."\"/>\r\n";
	echo "<input type=\"hidden\" name=\"frmSupplier\" value=\"".$_POST['frmSupplier']."\"/>\r\n";
	echo "<input type=\"hidden\" name=\"onFocus\">\r\n";
	echo "</form>\r\n";	
	//	Verify Posted Data
	$strNewUser=trim($_POST['frmUsername']);
		//	Check Username's length
	$CurrentLength = strlen($strNewUser);
	if ($CurrentLength<constMinLength)
	{
		$_SESSION['ErrMes']="Bạn chưa nhập bí danh hoặc bí danh quá ngắn!";
		die('<script language="javascript">
		document.frmSubmittedData.onFocus.value="frmUsername";
		document.frmSubmittedData.submit();
		</script>');
	}
	if ($CurrentLength>constMaxLength)
	{
		$_SESSION['ErrMes']="Bí danh quá dài";
		die('<script language="javascript">
		document.frmSubmittedData.onFocus.value="frmUsername";
		document.frmSubmittedData.submit();
		</script>');
	}
		// Check Username's validity
	for ($index=0;$index<$CurrentLength;$index++)
	{
		if (!strstr($AllowedChars,$strNewUser[$index]))
		{
			$_SESSION['ErrMes']="Bí danh chứa ký tự không cho phép";
			die('<script language="javascript">
		document.frmSubmittedData.onFocus.value="frmUsername";
		document.frmSubmittedData.submit();
		</script>');
		}
	}
	
		//	Check Password's length
	$CurrentLength=strlen($_POST['frmPassword']);
	if ($CurrentLength<constMinLength)
	{
		$_SESSION['ErrMes']="Bạn chưa nhập mật khẩu hoặc mật khẩu quá ngắn!";
		die('<script language="javascript">
		document.frmSubmittedData.onFocus.value="frmPassword";
		document.frmSubmittedData.submit();
		</script>');
	}
	if ($CurrentLength>constMaxLength)
	{
		$_SESSION['ErrMes']="Mật khẩu quá dài";
		die('<script language="javascript">
		document.frmSubmittedData.onFocus.value="frmPassword";
		document.frmSubmittedData.submit();
		</script>');
	}
		//	Check Password's validity
	for ($index=0; $index<$CurrentLength;$index++)
	{
		if (($_POST['frmPassword'][$index]==" ") or (!strstr($AllowedChars,$_POST['frmPassword'][$index])) )
		{
			$_SESSION['ErrMes']="Mật khẩu chứa ký tự không cho phép";
			die('<script language="javascript">
		document.frmSubmittedData.onFocus.value="frmPassword";
		document.frmSubmittedData.submit();
		</script>');						
		}
	}
		//	Check Password's Confirmation
	if ($_POST['frmPassword']!==$_POST['frmPasswordConfirm'])
	{
		$_SESSION['ErrMes']="Xác nhận mật khẩu không khớp";
		die('<script language="javascript">
		document.frmSubmittedData.onFocus.value="frmPassword";
		document.frmSubmittedData.submit();
		</script>');
	}
		//	Check Email's validity
	if ($_POST['frmEmail']!==$_POST['frmEmailConfirm'])
	{
		$_SESSION['ErrMes']="Xác nhận email không khớp";
		die('<script language="javascript">
		document.frmSubmittedData.onFocus.value="frmEmailConfirm";
		document.frmSubmittedData.submit();
		</script>');
	}
		//	Check Agreement to Term
	if ($_POST['frmAgreeToTerm']!=='on')
	{
		$_SESSION['ErrMes']="Bạn phải đồng ý với điều kiện sử dụng website!";
		die('<script language="javascript">
		document.frmSubmittedData.onFocus.value="frmAgreeToTerm";
		document.frmSubmittedData.submit();
		</script>');
	}
		//	Check Field
	if ($_POST['frmField']==0)
	{
		$_SESSION['ErrMes']="Bạn phải chọn một chuyên ngành";
		die('<script language="javascript">
		document.frmSubmittedData.onFocus.value="frmField";
		document.frmSubmittedData.submit();
		</script>');

	}
	
	////////////////////////
	$strMysqlQuery = "SELECT * FROM $strTableUserName WHERE (username = '".$_POST['frmUsername']."') OR (email ='".$_POST['frmEmail']."') ";
	//echo $strMysqlQuery."<br>";
	$result = mysql_query($strMysqlQuery) or die(mysql_error());
	
	////////////////////////////////////////////////////
	/////   Function Check Username's existance    /////
	////////////////////////////////////////////////////
	$row = mysql_fetch_array($result);
	
	if (!($row === false))
	{
		$_SESSION['ErrMes'] = "<center> Bí danh hoặc email đã được sử dụng. Nếu bạn quên mật khẩu xin nhấn vào <a href=\"forgotpassword.php\"> đây</a>.</center>\n";
		echo('<script language="javascript"> 
		document.frmSubmittedData.onFocus.value="frmUsername";
		document.frmSubmittedData.submit();</script>');
	}
	else
	{
		$today = date("Y-m-d");
		$encoded_password = crypt($_POST['frmPassword']);
		$strInsertQuery = "INSERT INTO $strTableUserName (username, password, email, field, supplier,join_date) VALUES ('".$_POST[frmUsername]."', '".$encoded_password."', '".$_POST['frmEmail']."', '".$arrFieldList[$_POST['frmField']]."','".$_POST['frmSupplier']."','$today')";
		mysql_query($strInsertQuery) or die(mysql_error());
		echo "Chào mừng ";
		echo $_SESSION["username"] = $_POST["frmUsername"];
		$_SESSION["password"] = $encoded_password;
		echo "!<br>\n";
		echo "<script language=\"javascript\">	window.location = \"index.php\"; </script>"; 
	}
	include "dbclose.php";
}

?>
</body>
</html>
