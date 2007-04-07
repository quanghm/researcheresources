<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Đăng ký thành viên</title>
<?php
session_start();	
include "config.php";
include "chk_login.inc";
if (logged_in())
{
	echo "<center> $_SESSION[username]: bạn đã là thành viên! Đang quay trở lại trang cá nhân...</center>";
	die('<meta http-equiv="refresh" content="3; url=account.php">');
}
?>
<script language="javascript">
function DataVerify()
{
	const MinLength = <?php echo constMinLength;?>;
	const MaxLength = <?php echo constMaxLength;?>;
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
</head>

<body>
<table width="800" border="0" align="center" cellpadding="0"  bgcolor="#CCCC66" >
  <tr>
    <th scope="col" width="33%"><a href=<?php echo "\"index.php\""; ?> >Trang chủ </a></td>
    <th scope="col" width="33%"><?php 
			echo "<a href=\"register.php\"> Đăng kí thành viên </a>";
		?></td>
    <th scope="col" width="33%"><a href=<?php echo "\"about.php\""; ?>>Về chúng tôi </a></td>
  </tr>
</table>
<!---------- End of Menu ----------------->
	<?php
	$lines = file('term.txt');
		// Loop tdrough our array, show HTML source as HTML source; and line numbers too.
	$strTermOfUse ='';
	foreach ($lines as $line_num => $line) 
	{
   		//echo htmlspecialchars($line) . "<br />\n";
		$strTermOfUse .= htmlspecialchars($line);
	}
	if (isset($_SESSION["messError"]))
	{	
		echo '<table align="center" width="800" border="0">'."\n";
		echo '	<tr>
    				<td>'."\n";
		echo $_SESSION["messError"];
		echo '</td></tr></table>';	
		$_SESSION["messError"] ='';
	}
	?>
<form method="POST" onSubmit="return DataVerify();" action="register_process.php" onReset="return confirm('Thế nhất định là làm lại à?')" name="frmRegistration">
<table align="center" width="800" border="0">
  <tr>
    <td width="25%">&nbsp;</td>
    <td width="25%">Bí danh </td>
    <td colspan="2"><input type="text" name="frmUsername"></td>
    <td width="25" > <em>(Từ <?php echo constMinLength; ?> đến <?php echo constMaxLength; ?> ký tự) </em></td>
  </tr>
  <tr>
    <td width="25%">&nbsp;</td>
    <td width="25%">Mật khẩu</td>
    <td colspan="2"><input type="password" name="frmPassword"></td>
    <td width="25" ><em>(Từ <?php echo constMinLength; ?> đến <?php echo constMaxLength; ?> ký tự)</em> </td>
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
	  <textarea name="txtTermOfUse" rows="10" cols="60" disabled="disabled"><?php 	echo $strTermOfUse; ?>
    </textarea>
	  </div></td>
    </tr>
  <tr>
    <td width="25%"></td>
    <td colspan="3" align="center">
	<label>
      <input name="frmAgreeToTerm" type="checkbox"> 
      Tớ đồng ý!!!</label>
      	</td>
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
</body>
</html>
