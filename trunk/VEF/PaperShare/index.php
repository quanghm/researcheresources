<?php
include "chk_login.inc";
if ((logged_in())&& (!isset($strConn)))
{
	include "config.php";
	include "dbconnect.php";
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/paper_share.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->

<title>Nghi&ecirc;n c&#7913;u sinh dot org</title>
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
    <td width="66%" height="700"valign="top">
	<!-- InstanceBeginEditable name="body" -->
<?php include"announce.php";?>
<!-- InstanceEndEditable -->	</td>
<td width="33%" align="left" valign="top" bgcolor="#CCCC66"><?php
		if (logged_in())
		{
			//////////// Select user from database /////////////
	$strMyQuery = "SELECT * FROM $strTableUserName WHERE username = '".$_SESSION['username']."'";
	$result = mysql_query($strMyQuery) or die(mysql_error());
	$arrUserData = mysql_fetch_array($result);
	////////////////////////////////////////////////////

			echo "Ch&agrave;o m&#7915;ng ".$_SESSION["username"]."! <button onClick=\"javascript:window.location = 'login.php?action=logout'\">Kh&#7855;c xu&#7845;t</button><br><br/>\n";

		echo "B&#7841;n &#273;&atilde; g&#7917;i ".$arrUserData['request_number']." Y&ecirc; c&#7847;u! <a href=\"account.php?type=submit_request\">Y&ecirc; c&#7847;u b&agrave;i b&aacute;o</a><br>\n";
		if ($arrUserData['supplier']) 
		{
			////////	Get list of requests pending	/////////////
			$strMysqlQuery = "SELECT * FROM $strTableRequestName WHERE (supplier = '".$_SESSION['username']."') AND (status >=0)";
			$result = mysql_query($strMysqlQuery) or die(mysql_error());
			$request_pending = mysql_num_rows($result);
			if ($request_pending>0)
			{	echo "B&#7841;n c&oacute; ".$request_pending." y&ecirc;u c&#7847;u &#273;ang ch&#7901; <a href=\"account.php?type=request\">x&#7917; l&yacute;!</a><br>\n";
			}
			else
			{
				echo "B&#7841;n kh&ocirc;ng c&oacute; y&ecirc;u c&#7847;u n&agrave;o &#273;ang ch&#7901;!<br>\n";
			}
		}
		echo "<br />\r\n <a href=\"account.php?type=change\"> Thay &#273;&#7893;i th&ocirc;ng tin c&aacute; nh&acirc;n </a><br>";			
		if ($arrUserData['admin']){echo "<a href=\"admin.php\"> &#272;&#259;ng nh&#7853;p trang qu&#7843;n tr&#7883;</a>";}
			//////// Close connection to database /////////
			include "dbclose.php";
		}
		else
		{	
			echo "<center>B&#7841;n ch&#432;a &#273;&#259;ng nh&#7853;p</center>";
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
Â© Copyright 2007 by <?php echo $strWebsiteName?></td>
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
