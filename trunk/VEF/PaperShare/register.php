<?php
include "chk_login.inc";
?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Đăng ký thành viên</title>
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
	if (form.frmUsername.value.length < MinLength)
	{
		alert("Bí danh quá ngắn!");
		form.frmUsername.focus();
		return false;
	}
	if (form.frmUsername.value.length > MaxLength)
	{
		alert("Bí danh quá dài!");
		form.frmUsername.focus();
		return false;
	}
	///////////////////////////////////////
	
	///////// check password /////////////
	if (form.frmPassword.value.length < MinLength)
	{
		alert("Mật khẩu quá ngắn!");
		form.frmPassword.focus();
		return false;
	}
		if (form.frmPassword.value.length > MaxLength)
	{
		alert("Mật khẩu quá dài");
		form.frmPassword.focus();
		return false;
	}

	if (form.frmPassword.value != form.frmPasswordConfirm.value)
	{
		alert("Xác nhận mật khẩu không khớp!");
		form.frmPassword.focus();
		return false;
	}
	//////////////////////////////////////*/
	
	////////// Check email ////////////////
	var strEmail = form.frmEmail.value;
	if ((strEmail.indexOf('@')<0)||(strEmail.indexOf('.',strEmail.indexOf('@'))<0))
	{
		alert("Email không hợp lệ!");
		form.frmEmail.focus();
		return false;
	}
	if (form.frmEmail.value != form.frmEmailConfirm.value)
	{
		alert(" Xác nhận email không khớp!")
		form.frmEmail.focus();
		return false;
	}
	//////////////////////////////////////
	
	////////	Check Field		/////////
	if (form.frmField.value=="0")
	{
		alert("Bạn phải chọn một chuyên ngành!");
		form.frmField.focus();
		return false;
	}
	///////////////////////////////////////
	if (form.frmAgreeToTerm.checked == false)
	{
		alert("Bạn phải đồng ý với điều kiện sử dụng website!");
		return false;
	}
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
	<div align="center" class="error">
      <?php
	$lines = file('term.txt');
		// Loop tdrough our array, show HTML source as HTML source; and line numbers too.
	$strTermOfUse ='';
	foreach ($lines as $line_num => $line) 
	{
   		//echo htmlspecialchars($line) . "<br />\n";
		$strTermOfUse .= htmlspecialchars($line);
	}
	if (isset($_SESSION['ErrMes']))
	{	
		echo $_SESSION['ErrMes'];
		$_SESSION['ErrMes'] ='';
	}
	?>
    </div>
<form method="POST" onSubmit="return DataVerify(this);" action="register_process.php" onReset="return confirm('Thế nhất định là làm lại à?')" name="frmRegistration">
<table align="center" width="800" border="0">
  <tr>
    <td width="25%">&nbsp;</td>
    <td width="25%">Bí danh </td>
    <td colspan="2"><input type="text" name="frmUsername"></td>
    <td width="25%" > <em>(Từ <?php echo constMinLength; ?> đến <?php echo constMaxLength; ?> ký tự) </em></td>
  </tr>
  <tr>
    <td width="25%">&nbsp;</td>
    <td width="25%">Mật khẩu</td>
    <td colspan="2"><input type="password" name="frmPassword"></td>
    <td width="25%" ><em>(Từ <?php echo constMinLength; ?> đến <?php echo constMaxLength; ?> ký tự)</em> </td>
  </tr>
  <tr>
    <td width="25%">&nbsp;</td>
    <td width="25%">Xác nhận mật khẩu</td>
    <td colspan="2"><input type="password" name="frmPasswordConfirm"></td>
    <td width="25" >&nbsp;</td>
  </tr>
  <tr>
    <td width="25%">&nbsp;</td>
    <td width="25%">Email</td>
    <td colspan="2"><input type="text" name="frmEmail"></td>
    <td width="25" >&nbsp;</td>
  </tr>
  <tr>
    <td width="25%">&nbsp;</td>
    <td width="25%">Xác nhận lại email</td>
    <td colspan="2"><input type="text" name="frmEmailConfirm"></td>
    <td width="25" >&nbsp;</td>
  </tr>
  <tr>
    <td width="25%">&nbsp;</td>
    <td width="25%">Chuyên ngành</td>
    <td colspan="2"><select name="frmField">
	  <?php
	  	for ($index=0;$index<$NumberOfField;$index++)
		{
		echo "	  <option value=\"$index\">$arrFieldList[$index]</option>\n";
		}

	  ?>
	</select></td>
    <td width="25" >&nbsp;</td>
  </tr>
  <tr>
    <td width="25%">&nbsp;</td>
    <td width="25%">Bạn có khả năng làm người cung cấp bài báo?</td>
    <td width="12%"><label>
      <input name="frmSupplier" type="radio" value="1" onChange="alert('Người cung cấp sẽ giúp các user khác lấy tài liệu khi được yêu cầu.\r\nNếu bạn chỉ đơn thuần cần tài liệu, bạn nên chọn KHÔNG.')" <?php if ($_POST['frmSupplier']==1) {echo "checked=\"checked\"";}?>>
    Có</label></td>
    <td width="13%"><label>
      <input name="frmSupplier" type="radio" value="0" <?php if ($_POST['frmSupplier']==0) {echo "checked=\"checked\"";}?>>
    Không</label></td>
    <td width="25">&nbsp;</td>
  </tr>
  <tr>
	<td colspan="5"><div align="center">
	  <textarea name="txtTermOfUse" rows="10" cols="60" readonly="readonly"><?php 	echo $strTermOfUse; ?>
    </textarea>
	  </div></td>
    </tr>
  <tr>
    <td width="25%"></td>
    <td colspan="3" align="center">
	<label>
      <input name="frmAgreeToTerm" type="checkbox"> 
      Tớ đồng ý!!!</label>   	  </td>
    <td width="25" >&nbsp;</td>
  </tr>
  <tr>
    <td width="25%">&nbsp;</td>
    <td width="25%"align="center"><button type="submit" name="btnSubmit">Cho tớ tham gia với</button></td>
    <td colspan="2" align="center"><button type="reset" name="reset">Ooops! Làm lại nào!</button></td>
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
			echo "document.frmRegistration.$key.value=\"$value\";\r\n";
		}
	}
	echo "document.frmRegistration.".$_POST['onFocus'].".focus();\r\n";
	echo "</script>\r\n";
}
?>
</body>
</html>
