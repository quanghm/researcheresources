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
<?php echo'<link href="Theme/Default/style.css" rel="stylesheet" type="text/css" />'; ?>
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
</head>

<body>
<table width="999" border="0" align="center">
  <tr bgcolor="#CCCC66" align="center">
    <td width="25%" height="40" nowrap="nowrap" ><?php echo "<a href=\"index.php\" class=\"menu\">"?><span class="menu">Trang chủ</span><?php echo"</a>"; ?></td>
    <td width="25%" height="40" >
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
    <td width="25%" ><?php echo "<a href=\"feedback.php\" class=\"menu\">Góp ý</a>"; ?>
	</td>
    <td height="40"> <?php echo "<a href=\"about.php\" class=\"menu\">Về chúng tôi</a>"; ?></td>
  </tr>
  <tr >
    <td width="66%" height="700"valign="top" colspan="3">
	<!-- InstanceBeginEditable name="body" -->
<?php
if (!logged_in())
{
	$_SESSION['ErrMes']="Bạn chưa đăng nhập!";
	echo '<script language="javascript" > setTimeout("window.location=\'index.php\'",3000);</script>';
}
else	//start admin 
{
	include "config.php";
	include $strIncDir."sendmail/mail.php";
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
			//////	Suppliers found, sending mail
			$strDir=dirname($_SERVER['PHP_SELF']);
			$message = "<html>
						<head>
						<title>Bạn có yêu cầu đang chờ</title>
						</head>
						
						<body>
						Đây là email tự động gửi từ ban quản trị của $strWebsiteName.<br/>
						Hiện thời bạn có ".$arrSupplierData['request_pending_number']." yêu cầu cần giải quyết.<br />
						Xin hãy đăng nhập vào trang web <a href=\"".'http://'.$_SERVER['SERVER_NAME'].$strDir."\">$strWebsiteName </a> để xử lý các yêu cầu.
						</body>
						</html>";
			$Subject = "Ban co yeu cau dang cho o website $strWebsiteName";
			/*$Headers = "From: ".$strAdminEmail."\r\n";
			$Headers .= "MIME-Version: 1.0\r\n"; 
			$Headers .= "content-type: text/html; charset=utf-8\r\n";
			if (mail($To, $Subject, $message, $Headers))*/
			
			if (do_send($arrSupplierData['email'],$arrSupplierData['username'],$Subject,$message))
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
	elseif ($_GET['action']=='announce')
	{
		echo "<form method='POST' name='frmSendMail' action='admin.php?action=post_announce'>\r\n";
		echo "<center>Soạn thảo thông báo<br />\r\n";
		echo "Tiêu đề: <input type='text' name='txtSubject' size='58'/><br />\r\n" .
				"<textarea name='txtAnnouncement' rows='10' cols='50'></textarea><br />\r\n" .
				"<input type='radio' name='sendtype' id='rdsendtype' value='0' selected='selected'/>Thông báo trên trang chủ\r\n" .
				"<input type='radio' name='sendtype' id='rdsendtype' value='1'/>Tất cả người dùng\r\n" .
				"<input type='radio' name='sendtype' id='rdsendtype' value='2'/>Tất cả Suppliers\r\n" .
				"<input type='radio' name='sendtype' id='rdsendtype' value='3'/>Tất cả admins<br />\r\n" .
				"<input type='submit' id='btnSubmit' value='Gửi thông báo'/>\r\n" .
				"<input type='reset' id='btnReset' value='Làm lại'/>\r\n" .
				"</center>\r\n";
		echo "</form>";
	}
	elseif ($_GET['action']=='post_announce') 
	{
		/*
		 if ($_SERVER['HTTP_REFERER']!=='http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF'])."/admin.php?action=announce")
		{
			die("Invalid referer");
		}
		*/
		if ($_POST['sendtype']=='0')
		{
			// Get date
			$today = date("Y-m-d");
			// connect to database
			$strMysqlQuery = "INSERT INTO $strTableAnnouncement (id,content,date)
							  VALUES (NULL ,'".$_POST['txtAnnouncement']."','".$today."')";
			mysql_query($strMysqlQuery) or die(mysql_error());
			
			echo "Gửi thông báo thành công.<br/>\r\n";
		}
		else
		{
			// Get appropriate users of given type.
			$strMysqlQuery = "SELECT * FROM $strTableUserName";
			if ($_POST['sendtype']=='3')
			{
				$strMysqlQuery.=" WHERE (admin='1')";
			}
			elseif ($_POST['sendtype']=='2')
			{
				$strMysqlQuery .= " WHERE (supplier='1')";
			}
			
			$result=mysql_query($strMysqlQuery) or die(mysql_error());
			while ($arrUserData=mysql_fetch_array($result))
			{
				echo "Gửi thư cho ".$arrUserData['username'].": ";
				if (!do_send($arrUserData['email'],$arrUserData['username'],$_POST['txtSubject'],$_POST['txtAnnouncement']))
				{
					echo "THẤT BẠI...";
				}
				else
				{
					echo "HOÀN TẤT...";
				}
				echo "<br/>\r\n";
			} 			
		}
		echo"Đang quay lại trang quản trị...<br />\r\n";
		echo'<script language="javascript">setTimeout(\'window.location="admin.php"\',3000);</script>';
	}
	elseif ($_GET['action']=='topsuppliers')
	{
		echo "<center><b>Danh sách nhung supplier nhiet tinh nhat</b></center><br />\r\n";
		$strMysqlQuery="
			select supplier, count(*) as completed from tbl_request 
			where status = -1
			group by supplier 
			order by completed DESC";
		$result = mysql_query($strMysqlQuery) or die(mysql_error());

		if (mysql_num_rows($result)==0)
		{
			echo "Chưa có người cung cap nào!";
		}
		else
		{
			echo "<table align='center'>\r\n" .
					"<tr>\r\n" .
					"<th>Ten nguoi cung cap</th>\r\n" .
					"<th>So bai bao da cung cap</th>\r\n" .
					"</tr>";
			$strTrClass="odd";
			while ($arrUserData=mysql_fetch_array($result))
			{
				echo "<tr class=\"$strTrClass\">\r\n" .
					"\t<td>" .$arrUserData['supplier']."</td>\r\n".
					"\t<td>" .$arrUserData['completed']."</td>\r\n".
					"</tr>\r\n";
				$strTrClass=str_replace($strTrClass,"",'oddeven');
			}
			echo "</table>\r\n";
		}
		
	}
	elseif ($_GET['action']=='users')
	{
		if (!isset($_POST['offset']))
		{
			$_POST['offset']=0;
		}
		if (!isset($_GET['orderBy']))
		{
			$_GET['orderBy']='username';
		}
		if (!isset($_GET['order']))
		{
			$_GET['order']='ASC';
		}

		$strMysqlQuery="SELECT * FROM $strTableUserName";
		if ((isset($_POST['field']))and ($_POST['field']>0))
		{
			$strMysqlQuery=$strMysqlQuery." WHERE (field='".$arrFieldList[$_POST['field']]."')";
		}
		$strMysqlQuery.= (" ORDER BY ".$_GET['orderBy']." ".$_GET['order']);
		
		$result = mysql_query($strMysqlQuery) or die(mysql_error());
		
		echo "<center>Danh sách thành viên</center><br />\r\n" .
			"<form method='POST' name='frmFilter' id='frmFilter' action='admin.php?action=users'>\r\n" .
			"\t<center>" .
			"\t Bắt đầu từ:<input name='offset' type='text' size='5' value='0'/>" .
			"\t Chuyên ngành:<select name='field' onchange='document.getElementById(\"frmFilter\").submit()'>\r\n";
		foreach ($arrFieldList as $key => $value)
		{
			echo "\t\t<option value='$key' ";
			if ($key==$_POST['field'])
			{
				echo "selected='selected' ";
			}
			echo ">$value</option>\r\n";
		}
		echo "\t</select>\r\n" .
			"\t<input type='submit' value='Lọc'>" .
			"\t</center>" .
			"</form>\r\n";
		if (mysql_num_rows($result)==0)
		{
			echo "Chưa có người dùng nào!";
		}
		else
		{
			echo "<table align='center'>\r\n" .
					"<tr>\r\n" .
					"<th>Bí danh</th>\r\n" .
					"<th>Ngày gia nhập</th>\r\n" .
					"<th>Chuyên ngành</th>\r\n" .
					"<th>Số yêu cầu đã hoàn tất</th>\r\n" .
					"<th>Số yêu cầu đang chờ</th>\r\n" .
					"<th>Email</th>\r\n" .
					"<th>Supplier</th>\r\n" .
					"</tr>";
			$strTrClass="odd";
			while ($arrUserData=mysql_fetch_array($result))
			{
				echo "<tr class=\"$strTrClass\">\r\n" .
					"\t<td>" .$arrUserData['username']."</td>\r\n".
					"\t<td>" .$arrUserData['join_date']."</td>\r\n".
					"\t<td>" .$arrUserData['field']."</td>\r\n".
					"\t<td>" .$arrUserData['request_handle_number']."</td>\r\n".
					"\t<td>" .$arrUserData['request_pending_number']."</td>\r\n".
					"\t<td>" .$arrUserData['email']."</td>\r\n".
					"\t<td>" .$arrUserData['supplier']."</td>\r\n".
					"</tr>\r\n";
				$strTrClass=str_replace($strTrClass,"",'oddeven');
			}
			echo "</table>\r\n";
		}
	}
	elseif ($_GET['action']=='requests')
	{
		if (!isset($_POST['offset']))
		{
			$_POST['offset']=0;
		}
		if (!isset($_GET['orderBy']))
		{
			$_GET['orderBy']='id';
		}
		if (!isset($_GET['order']))
		{
			$_GET['order']='DESC';
		}

		$strMysqlQuery="SELECT * FROM $strTableRequestName";
		if ((isset($_POST['field']))and ($_POST['field']>0))
		{
			$strMysqlQuery=$strMysqlQuery." WHERE (field='".$arrFieldList[$_POST['field']]."')";
		}
		$strMysqlQuery.= (" ORDER BY ".$_GET['orderBy']." ".$_GET['order']);
		$strMysqlQuery.= (" LIMIT ".$_POST['offset'].", 30");
		
		$result = mysql_query($strMysqlQuery) or die(mysql_error());
		
		echo "<center>Danh sách yêu cầu</center><br />\r\n" .
			"<form method='POST' name='frmFilter' id='frmFilter' action='admin.php?action=requests'>\r\n" .
			"\t<center>" .
			"\t Bắt đầu từ:<input name='offset' type='text' size='5' value='".$_POST['offset']."'/>" .
			"\t Chuyên ngành:<select name='field' onchange='document.getElementById(\"frmFilter\").submit()'>\r\n";
		foreach ($arrFieldList as $key => $value)
		{
			echo "\t\t<option value='$key' ";
			if ($key==$_POST['field'])
			{
				echo "selected='selected' ";
			}
			echo ">$value</option>\r\n";
		}
		echo "\t</select>\r\n" .
			"\t<input type='submit' value='Lọc'>" .
			"\t</center>" .
			"</form>\r\n";
		if (mysql_num_rows($result)==0)
		{
			echo "Chưa có yêu cầu nào!";
		}
		else
		{
			echo "<table width=\"100%\" align='center'>\r\n" .
					"<tr>\r\n" .
					"<th width=\"30%\">Tên bài báo</th>\r\n" .
					"<th>Ngày yêu cầu</th>\r\n" .
					"<th>Chuyên ngành</th>\r\n" .
					"<th>Người yêu cầu</th>\r\n" .
					"<th>Người cung cấp</th>\r\n" .
					"<th>Trạng thái</th>\r\n" .
					"</tr>";
			$strTrClass="odd";
			while ($arrRequestData=mysql_fetch_array($result))
			{
				echo "<tr class=\"$strTrClass\">\r\n" .
					"\t<td>" .$arrRequestData['title']."</td>\r\n".
					"\t<td>" .$arrRequestData['date_request']."</td>\r\n".
					"\t<td>" .$arrRequestData['field']."</td>\r\n".
					"\t<td>" .$arrRequestData['requester']."</td>\r\n".
					"\t<td>" .$arrRequestData['supplier']."</td>\r\n".
					"\t<td>" ;
				if ( $arrRequestData['status'] == -1)
				{
					echo "Hoàn tất";
				}
				elseif ($arrRequestData['status']==-2)
				{
					echo "Thất bại";
				}
				else
				{
					echo "Đang chờ";
				}
				echo "</td>\r\n".
					"</tr>\r\n";
				$strTrClass=str_replace($strTrClass,"",'oddeven');
			}
			echo "</table>\r\n";
		}
	}
	else
	{
		echo '<a href="admin.php?action=mail">Gửi email nhắc việc cho các suppliers</a><br />'."\n";
		echo '<a href="admin.php?action=announce">Gửi thông báo </a><br />'."\n";
		echo '<a href="admin.php?action=users">Danh sách thành viên </a><br />'."\n";
		echo '<a href="admin.php?action=requests">Danh sách bài báo </a><br />'."\n";
	}
}
	// Make a MySQL Connection
	///////////////////////////////////////////////////////

?>

<!-- InstanceEndEditable -->	</td>
<td width="33%" align="left" valign="top" bgcolor="#CCCC66"><?php
		if (logged_in())
		{
			//////////// Select user from database /////////////
	$strMyQuery = "SELECT * FROM $strTableUserName WHERE username = '".$_SESSION['username']."'";
	$result = mysql_query($strMyQuery) or die(mysql_error());
	$arrUserData = mysql_fetch_array($result);
	////////////////////////////////////////////////////

			echo "Chào mừng ".$_SESSION["username"]."! <button onClick=\"javascript:window.location = 'login.php?action=logout'\">Khắc xuất</button><br><br/>\n";

		echo "Bạn đã gửi ".$arrUserData['request_number']." yêu cầu! <a href=\"account.php?type=submit_request\">Yêu cầu bài báo</a><br>\n";
		if ($arrUserData['supplier']) 
		{
			////////	Get list of requests pending	/////////////
			$strMysqlQuery = "SELECT * FROM $strTableRequestName WHERE (supplier = '".$_SESSION['username']."') AND (status >=0)";
			$result = mysql_query($strMysqlQuery) or die(mysql_error());
			$request_pending = mysql_num_rows($result);
			if ($request_pending>0)
			{	echo "Bạn có ".$request_pending." yêu cầu đang chờ <a href=\"account.php?type=request\">xử lý!</a><br>\n";
			}
			else
			{
				echo "Bạn không có yêu cầu nào đang chờ!<br>\n";
			}
		}
		echo "<br />\r\n <a href=\"account.php?type=change\"> Thay đổi thông tin cá nhân </a><br>";			
		if ($arrUserData['admin']){echo "<a href=\"admin.php\">Đăng nhập trang quản trị</a>";}
			//////// Close connection to database /////////
			include "dbclose.php";
		}
		else
		{	
			echo "<center>Bạn chưa đăng nhập</center>";
			require "login_form.inc.php";

		}
	?></td>
  </tr>
  <tr >
    <td colspan="5" valign="top" align="center"><!-- Google CSE Search Box Begins  -->
<form action="http://www.google.com/cse" id="searchbox_004865859078258633675:18sqvplglto">
  <input type="hidden" name="cx" value="004865859078258633675:18sqvplglto" />
  <input type="text" name="q" size="25" />
  <input type="submit" name="sa" value="Search" />
</form>
<!-- Google CSE Search Box Ends -->
© Copyright 2007 by <?php echo $strWebsiteName?></td>
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
