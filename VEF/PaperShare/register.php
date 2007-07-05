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
function DataVerify()
{
	MinLength = <?php echo constMinLength;?>;
	MaxLength = <?php echo constMaxLength;?>;
	//////// check username ///////////////
	if (frmRegistration.frmUsername.value == "")
	{
		alert("Bạn phải nhập bí danh!");
		frmRegistration.frmUsername.focus();
		return false;
	}
	
	///////////////////////////////////////
	
	///////// check password /////////////
	if (frmRegistration.frmPassword.value.length < MinLength)
	{
		alert("Mật khẩu quá ngắn!");
		frmRegistration.frmPassword.focus();
		return false;
	}
		if (frmRegistration.frmPassword.value.length > MaxLength)
	{
		alert("Mật khẩu quá dài");
		frmRegistration.frmPassword.focus();
		return false;
	}

	if (frmRegistration.frmPassword.value != frmRegistration.frmPasswordConfirm.value)
	{
		alert("Xác nhận mật khẩu không khớp!");
		frmRegistration.frmPassword.focus();
		return false;
	}
	//////////////////////////////////////*/
	
	////////// Check email ////////////////
	var strEmail = frmRegistration.frmEmail.value;
	if ((strEmail.indexOf('@')<0)||(strEmail.indexOf('.',strEmail.indexOf('@'))<0))
	{
		alert("Email không hợp lệ!");
		frmRegistration.frmEmail.focus();
		return false;
	}
	if (frmRegistration.frmEmail.value != frmRegistration.frmEmailConfirm.value)
	{
		alert(" Xác nhận email không khớp!")
		frmRegistration.frmEmail.focus();
		return false;
	}
	//////////////////////////////////////
	
	////////	Check Field		/////////
	if (frmRegistration.frmField.value=="0")
	{
		alert("Bạn phải chọn một chuyên ngành!");
		frmRegistration.frmField.focus();
		return false;
	}
	///////////////////////////////////////
	if (frmRegistration.frmAgreeToTerm.checked == false)
	{
		alert("Bạn phải đồng ý với điều kiện sử dụng website!");
		return false;
	}
	return true;}
</script>
<link href="Theme/Default/style.css" rel="stylesheet" type="text/css">
</head>

<body>
<table width="999" border="0" align="center" bgcolor="#FFFFFF" >
  <tr bgcolor="#CCCC66">
    <td width="33%" height="40" align="center"><?php echo "<a href=\"index.php\" class=\"menu\">"?><span class="menu">Trang chủ</span><?php echo"</a>"; ?></td>
    <td width="33%" height="40" align="center">      <?php 
	echo "<a href=\"register.php\" class=\"menu\">Đăng ký thành viên</a>";
	?>	
    </td>
    <td height="40" align="center"> <?php echo "<a href=\"about.php\" class=\"menu\">Về chúng tôi</a>"; ?></td>
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
<form method="POST" onSubmit="return DataVerify();" action="register_process.php" onReset="return confirm('Thế nhất định là làm lại à?')" name="frmRegistration">
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
      <option selected="selected" value="0">Choose a Field of Study...</option>
	  <?php
	  	for ($index=0;$index<$NumberOfField;$index++)
		{
		echo "	  <option value=\"$arrFieldList[$index]\">$arrFieldList[$index]</option>\n";
		}

	  ?>
	</select></td>
    <td width="25" >&nbsp;</td>
  </tr>
  <tr>
    <td width="25%">&nbsp;</td>
    <td width="25%">Bạn có muốn làm người cung cấp</td>
    <td width="12%"><label>
      <input name="frmSupplier" type="radio" value="1">
    Có</label></td>
    <td width="13%"><label>
      <input name="frmSupplier" type="radio" value="0" checked="checked">
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
</form></td>
  </tr>
</table>
	
</body>
</html>
