<?php
include "chk_login.inc";
?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Gửi câu hỏi</title>
<?php
include "config.php";
if (logged_in())
{
	echo "<center> $_SESSION[username]: bạn đã là thành viên! Đang quay trở lại trang cá nhân...</center>";
	die('<meta http-equiv="refresh" content="3; url=account.php">');
}
?>
<script language="javascript">
function DataVerify(form)
{
	var MinLength = <?php echo constMinLength;?>;
	var MaxLength = <?php echo constMaxLength;?>;
	//////// check username ///////////////
	if (form.txtFullName.value.length < MinLength)
	{
		alert("Họ tên quá ngắn!");
		form.txtFullName.focus();
		return false;
	}
	if (form.txtFullName.value.length > MaxLength)
	{
		alert("Họ tên quá dài!");
		form.txtFullName.focus();
		return false;
	}
	///////////////////////////////////////
	
	///////// check password /////////////
	if (form.txtAddress.value.length < MinLength)
	{
		alert("Địa chỉ quá ngắn!");
		form.txtAddress.focus();
		return false;
	}
		if (form.txtAddress.value.length > MaxLength)
	{
		alert("Địa chỉ quá dài");
		form.txtAddress.focus();
		return false;
	}
	//////////////////////////////////////*/
	
	////////// Check email ////////////////
	var strEmail = form.txtEmail.value;
	if ((strEmail.indexOf('@')<0)||(strEmail.indexOf('.',strEmail.indexOf('@'))<0))
	{
		alert("Email không hợp lệ!");
		form.txtEmail.focus();
		return false;
	}
	if (form.txtEmail.value != form.txtEmailConfirm.value)
	{
		alert(" Xác nhận email không khớp!")
		form.txtEmail.focus();
		return false;
	}
	//////////////////////////////////////

return true;
}
</script>

<link href="Theme/Default/style.css" rel="stylesheet" type="text/css">
</head>

<body>
<table width="999" border="0" align="center" bgcolor="#FFFFFF" >
  <tr>
    <td height="40" align="center" colspan="3"><?php include "menu.php" ?></td>
  </tr>
  <!---------- End of Menu ----------------->
  <tr bgcolor="#CCCC66">
    <td height="500" colspan="4" nowrap="nowrap" bgcolor="#FFFFFF" >	
<form method="POST" onSubmit="return DataVerify(this);" action="faq_process.php" onReset="return confirm('Thế nhất định là làm lại à?')" name="frmFAQ">
<table align="center" width="800" border="0">
  <tr>
    <td width="25%">&nbsp;</td>
    <td width="25%">H&#7885; t&ecirc;n</td>
    <td colspan="2"><input type="text" name="txtFullName"></td>
    <td width="25%" ></td>
  </tr>
  <tr>
    <td width="25%">&nbsp;</td>
    <td width="25%">&#272;ịa ch&#7881;</td>
    <td colspan="2"><input type="text" name="txtAddress"></td>
    <td width="25%" ></td>
  </tr>
  <tr>
    <td width="25%">&nbsp;</td>
    <td width="25%">Số điện thoại</td>
    <td colspan="2"><input type="text" name="txtSDT"></td>
    <td width="25" >&nbsp;</td>
  </tr>
  <tr>
    <td width="25%">&nbsp;</td>
    <td width="25%">Email</td>
    <td colspan="2"><input type="text" name="txtEmail"></td>
    <td width="25" >&nbsp;</td>
  </tr>
  <tr>
    <td width="25%">&nbsp;</td>
    <td width="25%">Xác nhận lại email</td>
    <td colspan="2"><input type="text" name="txtEmailConfirm"></td>
    <td width="25" >&nbsp;</td>
  </tr>
  <tr>
    <td width="25%">&nbsp;</td>
    <td width="25%">Nội dung y&ecirc;u cầu</td>
    <td width="12%"></td>
    <td width="13%"></td>
    <td width="25">&nbsp;</td>
  </tr>
  <tr>
	<td colspan="5" align="center">
	  <textarea name="txtRequestContent" rows="10" cols="60"></textarea></td>
    </tr>
  <tr>
    <td width="25%">&nbsp;</td>
    <td width="25%"align="center"><button type="submit" name="btnSubmit">Gửi yêu cầu</button></td>
    <td colspan="2" align="center"><button type="reset" name="reset">Oh, t&#7899; làm lại!</button></td>
    <td width="25" >&nbsp;</td>
  </tr>
</table>
</form>
	</td>
  </tr>
</table>
<?php
if (isset($_POST))
{
	echo "<script language=\"javascript\">\r\n";
	foreach ($_POST as $key=>$value)
	{
		if ($key!=='onFocus')
		{
			echo "document.frmFAQ.$key.value=\"$value\";\r\n";
		}
	}
	echo "document.frmFAQ.".$_POST['onFocus'].".focus();\r\n";
	echo "</script>\r\n";
}
?>
</body>
</html>
