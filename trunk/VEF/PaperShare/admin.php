<?php
	include "chk_login.inc";
	if (!isset($_GET['action']))
	{
		$_GET['action']='';
	}
include_once("global_config.php");
include_once("global_dbconnect.php");
function compareDate($i_sFirstDate, $i_sSecondDate)
{
//Break the Date strings into seperate components
$arrFirstDate = explode ("-", $i_sFirstDate);
$arrSecondDate = explode ("-", $i_sSecondDate);

$intFirstYear = $arrFirstDate[0];
$intFirstMonth = $arrFirstDate[2];
$intFirstDay = $arrFirstDate[1];

$intSecondYear = $arrSecondDate[0];
$intSecondMonth = $arrSecondDate[2];
$intSecondDay = $arrSecondDate[1];
// Calculate the diference of the two dates and return the number of days.
$intDate1 = gregoriantojd($intFirstDay, $intFirstMonth, $intFirstYear);
$intDate2 = gregoriantojd($intSecondDay, $intSecondMonth, $intSecondYear);

return $intDate1 - $intDate2;
}//end Compare Date
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
  <tr align="center">
    <td colspan="2">
	<?php include "menu.php"; ?>
    </td>
  </tr>
  <tr >
    <td width="70%" height="700" valign="top">
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
		{	
			///////// Dectect late suppliers///////////
			function DisableSupplier($SupplierName)	//	disable supplier with a given $supplierID 
			{
				$strMysqlQuery = "SELECT `email` FROM ".$GLOBALS['strTableUserName']." WHERE `username`='$SupplierName'";
				$SelectSupplierResult=mysql_query($strMysqlQuery);
				$arrSupplierData=mysql_fetch_array($SelectSupplierResult);
				$SupplierEmail = $arrSupplierData['email'];
				
				$strMysqlQuery = "UPDATE ".$GLOBALS['strTableUserName'].
								" SET supplier = 0, `request_pending_number`=0 WHERE (`username`='$SupplierName')";
				if (mysql_query($strMysqlQuery))
				{
					//	send mail to supplier
					$message=	'Đây là email tự động gửi từ ban quản trị của Nghiencuusinh.org. Hiện bạn đang có yêu cầu đã quá hạn '.
								$DisableSupplierThreshold.
								' ngày. Nghiencuusinh.org hiểu rất có thể bạn đang bận việc cá nhân, do đó tạm thời ngừng chức năng cung cấp của bạn.'.
								'Bất cứ khi nào bạn có thời gian và muốn tiếp tục tham gia cung cấp, bạn có thể vào “Thay đổi thông tin cá nhân”'.
								' trong tài khoản của mình để mở lại chức năng này. Mong sớm nhận được sự giúp đỡ của bạn.';
					if (do_send($SupplierEmail,$SupplierName,'Thong bao tam dung chuc nang cung cap', $message))
					{
						echo "Disable and Email to late supplier ".$SupplierName.": done...";
					}
				}
				else die(mysql_error());
			}
			
			/////////////////////////////////////////////
			function PassRequest($arrRequestData)	//	Pass request
			{
				//	Get current supplier data
				$strMysqlQuery = "SELECT * FROM ".$GLOBALS['strTableUserName'].
								" WHERE (`username`='".$arrRequestData['supplier']."')";
				
				$arrSupplierData = mysql_fetch_array(mysql_query($strMysqlQuery)) or die(mysql_error());
				
				
				//	Get current requester data
				$strMysqlQuery = "SELECT * FROM ".$GLOBALS['strTableUserName'].
								" WHERE (`username`='".$arrRequestData['requester']."')";
				
				$arrRequesterData = mysql_fetch_array(mysql_query($strMysqlQuery)) or die(mysql_error());
				
				//	Get the previous supplier and put to array $arrPreviousSuppliers
				parse_str($arrRequestData['previous_suppliers']);
				
				//	Search for new supplier
				$strMysqlQuery = "SELECT `username`,`id` FROM ".$GLOBALS['strTableUserName'].
								" WHERE (`supplier`=1) AND (`user_level`>0) AND (`username`!='".$arrRequesterData['username']."')";		//	search all active supplier
				
				if (isset($arrPreviousSuppliers))
				foreach ($arrPreviousSuppliers as $PreviousSupplier)
				{
					$strMysqlQuery .= "AND (`username`!='".$PreviousSupplier."') ";	//	all previous supplier
				}
				if ($GLOBALS['cross_field_request']==false)
				{
					 $strMyQuery .= "AND (`field`='".$arrRequestData['field']."')";
				}
				$strMysqlQuery .= "ORDER BY last_assigned_request ASC, request_handle_number ASC, request_pending_number ASC";
				
				$SelectSupplierResult = mysql_query($strMysqlQuery) or die(mysql_error());
				
				if ($arrNewSupplierData = mysql_fetch_array($SelectSupplierResult))	// found new supplier
				{
					//	update request's data
					$today = date('Y-m-d');
					$strPreviousSuppliers = $arrRequestData['previous_suppliers'].'$arrPreviousSuppliers[]='.$arrSupplierData['username']."&";
					$strMysqlQuery = "UPDATE ".$GLOBALS['strTableRequestName'].
									" SET `supplier`='".$arrNewSupplierData['username'].
									"', `status`=`status`+1, `previous_suppliers`='$strPreviousSuppliers',`date_assigned`='$today' ".
									" WHERE (`id`='".$arrRequestData['id']."')";
					
					mysql_query($strMysqlQuery) or die(mysql_error());
					
					//	update previous supplier's data
					$strMysqlQuery = "UPDATE ".$GLOBALS['strTableUserName'].
									" SET `request_pending_number`=request_pending_number -1".
									" WHERE (`username`='".$arrRequestData['supplier']."')";
					mysql_query($strMysqlQuery) or die(mysql_error());
					
					//	update current suplier's data
					$last_assigned_request = date('YmdHis');
					$strMysqlQuery = "UPDATE ".$GLOBALS['strTableUserName'].
									 " SET `request_pending_number` = `request_pending_number` +1, `last_assigned_request` = '$last_assigned_request' ".
									 " WHERE  (user_level='1') AND username = '".$arrNewSupplierData['username']."'";
					mysql_query($strMysqlQuery) or die(mysql_error());
					
					//	Email requester about the delay
					//  echo"Email to requester ".$arrRequesterData['username']." done...";
				}
				else
				{
					$strMysqlQuery = "UPDATE $strTableRequestName SET status = -2 WHERE id=".$arrRequestData['id'];
					
					mysql_query($strMysqlQuery) or die(mysql_error());		
				}
			}
			
			///////////////	Main	//////////////////// 
			//Get current date
			$today = date('Y-m-d');
			
			//	Get all requests which are config.php/$GLOBALS['DisableSupplierThreshold'] days late
			$strMysqlQuery = "SELECT * FROM ".$GLOBALS['strTableRequestName'].
							" WHERE DATEDIFF('$today', `date_assigned`)>=".$GLOBALS['DisableSupplierThreshold'].
							" GROUP BY `supplier`";
			
			$SelectLateRequestResult = mysql_query($strMysqlQuery) or die(mysql_error());
			//	Get list of all late suppliers and disable them
			if (mysql_num_rows($SelectLateRequestResult)>0)
			{
				while ($arrLateRequestData=mysql_fetch_array($SelectLateRequestResult))
				{
					$arrLateSuppliers[]=$arrLateRequestData['supplier'];
					DisableSupplier($arrLateRequestData['supplier']);
				}
				//	Redistribute waiting papers
				foreach ($arrLateSuppliers as $LateSupplier)
				{
					$strMysqlQuery = "SELECT * FROM ".$GLOBALS['strTableRequestName'].
									" WHERE (`supplier`= '$LateSupplier') AND (status>-1)";
					$SelectRequestResult = mysql_query($strMysqlQuery) or die(mysql_error());		//	Get list of requests for current LateSupplier
					
					while ($arrRequestData = mysql_fetch_array($SelectRequestResult))
					{	
						echo ("<br />______________<br />Passing Request ".$arrRequestData['id']."<br />");
						PassRequest($arrRequestData);
					}
				}
			}
			else
			{
				echo "Khong co yeu cau muon ".$GLOBALS['DisableSupplierThreshold']." ngay.<br />\r\n";
			}
			
			//	Remind suppliers with requests $GLOBALS['WarnSupplierThreshold'] days late
			$today = date('Y-m-d');
			
			$strMysqlQuery = 	"SELECT *, COUNT(*) AS LateRequestNumber FROM ".$GLOBALS['strTableRequestName'].
							" WHERE (DATEDIFF('$today', `date_assigned`)=".$GLOBALS['WarnSupplierThreshold'].
							") AND (`status`>=0)".
							" GROUP BY `supplier`";
			$SelectRequestResult = mysql_query($strMysqlQuery) or die(mysql_error());
			
			if (mysql_num_rows($SelectRequestResult) == 0)
			{
				echo "Khong co yeu cau muon ".$GLOBALS['WarnSupplierThreshold']." ngay. <br />\r\n";
			}
			else
			{
				while ($arrRequestData = mysql_fetch_array($SelectRequestResult))
				{
					$strMysqlQuery = "SELECT * FROM ".$GLOBALS['strTableUserName'].
								" WHERE `username`='".$arrRequestData['supplier']."'";
					$SelectSupplierResult = mysql_query($strMysqlQuery) or die(mysql_error());
			
					// email to supplier
					while ($arrSupplierData=mysql_fetch_array($SelectSupplierResult))
					{
						$email = $arrSupplierData['email'];
						$username = $arrSupplierData['username'];
						$message = 'Đây là email tự động gửi từ ban quản trị của Nghiencuusinh.org. Hiện thời bạn có '.
									$arrSupplierData['LateRequestNumber'].' yêu cầu đã quá '.$WarnSupplierThreshold.' ngày.'."\r\n".
									'Rất mong bạn nhanh chóng đăng nhập vào trang web Nghiencuusinh.org để xử lý các yêu cầu đã quá hạn. Cám ơn bạn.	';
						if (do_send($email,$username,'Ban dang co yeu cau da qua han tai nghiencuusinh.org',$message))
						{
							echo"Email to supplier ".$arrSupplierData['username'].": done...<br />\r\n";
						}
						else
						{
							echo"Email to supplier ".$arrSupplierData['username'].": failed...<br />\r\n";
						}
					}
				}
			}
			////	End dealing with late suppliers
			$strMyQuery = "INSERT INTO $strTableAdmin (lastupdate, username) VALUES ('$today', '".$_SESSION['username']."')";
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
				$message = "<html>
							<head>
							<title>Bạn có yêu cầu đang chờ</title>
							</head>
							
							<body>
							Đây là email tự động gửi từ ban quản trị của $strWebsiteName.<br/>
							Hiện thời bạn có ".$arrSupplierData['request_pending_number']." yêu cầu cần giải quyết.<br />
							Xin hãy đăng nhập vào trang web <a href=\"http://$strWebsiteName\">$strWebsiteName </a> để xử lý các yêu cầu.
							</body>
							</html>";
				$Subject = "Ban co yeu cau dang cho o website $strWebsiteName";
				
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
			echo "<center><b>Danh sách những supplier nhiệt tình nhất</b></center><br />\r\n";
			$strMysqlQuery="select supplier, count(*) as completed from tbl_request 
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
						"<th height=30 width=250>Tên người cung cấp</th>\r\n" .
						"<th height=30 width=250>Số bài báo đã cung cấp</th>\r\n" .
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
		
		echo "<div class='title' align='center'>Danh sách thành viên</div><br />\r\n" .
			"<form method='POST' name='frmFilter' id='frmFilter' action='admin.php?action=users'>\r\n" .
			"\t<center>" .
			"\tBắt đầu từ:<input name='offset' type='text' size='5' value='0'/>" .
			"\tChuyên ngành:<select name='field' onchange='document.getElementById(\"frmFilter\").submit()'>\r\n";
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
			echo '<form action="admin.php?action=view_detail&type=user" ID="frmViewDetail" method="post">'."\r\n".
				'	<input type="hidden" name="UserID" ID="UserID"/></form>  '."\r\n";
			echo 	"<script language='javascript'>\r\n".
					"	function submitID(id)\r\n" .
					"	{\r\n" .
					"		document.getElementById('UserID').value=id;\r\n" .
					"		document.getElementById('frmViewDetail').submit();\r\n" .
					"	}\r\n".
					"</script>\r\n";
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
				echo "<tr onclick='javascript:submitID(".$arrUserData['id'].")' class=\"$strTrClass\">\r\n" .
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
		if ((!isset($_POST['offset'])) or (!is_int($_POST['offset'])))
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
		echo "<div class='title' align='center'>Danh sách yêu cầu</div><br />\r\n" .
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
			"\tTrạng thái:<select name='status' onchange='document.getElementById(\"frmFilter\").submit()'>\r\n" .
			"\t\t<option value='0'";
		if ($_POST['status']=='0'){echo "selected='selected'";}
		echo ">Đang chờ</option>\r\n" .
			"\t\t<option value='-1'";
		if ($_POST['status']=='-1'){echo "selected='selected'";}
		echo ">Hoàn tất</option>\r\n" .
			"\t\t<option value='-2'";
		if ($_POST['status']=='-2'){echo "selected='selected'";}
		echo ">Thất bại</option>\r\n" .
			"</select>" .
			"\t<input type='submit' value='Lọc'>" .
			"\t</center>" .
			"</form>\r\n";
		include "make_list.inc.php";
		$arrField= array(
						'title'=>"Tên bài báo",
						'date_request' => "Ngày yêu cầu",
						'field' => "Chuyên ngành",
						'requester' => "Người yêu cầu",
						'supplier' => "Người cung cấp"						
						);
		if ($_POST['status']=='-1')
		{
			$strCondition = " WHERE (status=-1) ";
		}
		elseif ($_POST['status']=='-2')
		{
			$strCondition = " WHERE (status=-2) ";
		}
		else
		{
			$strCondition = " WHERE (status>-1) ";
		}
		if ($_POST['field']>0){$strCondition .= "AND (field='".$arrFieldList[$_POST['field']]."') ";}
		draw_table('Request',$arrField,$_GET['orderBy']." ".$_GET['order'],$_POST['offset'],'account.php?type=handle_request',$strCondition);
	}
	elseif ($_GET['action']=="view_detail") 
	{
		if (!isset($_GET['type']))
		{
			$_GET['type']="";
		}
		
		if ($_GET['type']=="user")
		{
			$strMysqlQuery="SELECT * FROM $strTableUserName WHERE (id='".$_POST['UserID']."')";
			$result = mysql_query($strMysqlQuery);
			$arrUserDetail = mysql_fetch_array($result) or die(mysql_error());
			echo "<div class='title' align='center'> Thong tin chi tiet cua <span style='color:#FF0000'>".$arrUserDetail['username']."</span></div>\r\n";
		}
		else
		{
			die("<script language='javascript'>window.location='admin.php';</script>");
		}
	}
	elseif ($_GET['action']=="all_supplier") 
	{
		$strMysqlQuery="SELECT * FROM $strTableUserName WHERE supplier=1 and field='".$arrUserData['field']."'";
		$result = mysql_query($strMysqlQuery) or die(mysql_error());
		echo "Tổng số supplier: ".$num_row=mysql_num_rows($result);
		echo "<table align='center'>\r\n" .
					"<tr>\r\n" .
					"<th>Bí danh</th>\r\n" .
					"<th>Chuyên ngành</th>\r\n" .
					"<th>Yêu cầu đã hoàn tất</th>\r\n" .
					"<th>Yêu cầu đang chờ</th>\r\n" .
					"<th>Email</th>\r\n" .
					"<th>Stop cung cấp</th>\r\n" .
					"</tr>";
		$strTrClass="odd";
		while ($arrList_User = mysql_fetch_array($result))
		{
		 echo "<tr class=\"$strTrClass\">\r\n" .
					"\t<td>" .$arrList_User['username']."</td>\r\n".
					"\t<td>" .$arrList_User['field']."</td>\r\n".
					"\t<td>" .$arrList_User['request_handle_number']."</td>\r\n".
					"\t<td>" .$arrList_User['request_pending_number']."</td>\r\n".
					"\t<td>" .$arrList_User['email']."</td>\r\n".
					"\t<td>".
					"<form name=\"frm".$arrList_User['ID']."\" method=\"POST\" action=\"admin.php?action=stop_supplier\">".
									"<input type=\"hidden\" name=\"txtId\" value=\"".$arrList_User['ID']."\"/>".
									"<input type=\"submit\" name=\"btnStop\" value=\" Stop \"/>".
									"</form>".
					"</td>\r\n".
					"</tr>\r\n";
					$strTrClass=str_replace($strTrClass,"",'oddeven');
		}		
		echo "</table>\r\n";
		//echo "<div class='title' align='center'> Thong tin chi tiet cua <span style='color:#FF0000'>".$arrList_User['username']."</span></div>\r\n";		
	}
	elseif ($_GET['action']=="stop_supplier") 
	{
		$strMysqlQuery = "UPDATE $strTableUserName " .
						"SET supplier = 0 ".
						"WHERE id = ".$_POST['txtId']."";
		mysql_query($strMysqlQuery) or die(mysql_error());		
		
		//CHUYEN BAI BAO CHO NGUOI CUNG CAP KHAC
		
		//Select this member
		$strSelectQuery="SELECT * FROM $strTableUserName WHERE id = '".$_POST['txtId']."'";
		//echo $strSelectQuery;
		$Select_result=mysql_query($strSelectQuery) or die(mysql_error());
		$arrSelectMember = mysql_fetch_array($Select_result);
		//Update number of pending requests for supplier
		$strUpdateQuery = "UPDATE $strTableUserName " .
						"SET request_pending_number = 0 ".
						"WHERE username = '".$arrSelectMember['username']."'";
		mysql_query($strUpdateQuery) or die(mysql_error());	
		//Select request and requester passing
		$strSelectRequestOfMember = "SELECT * FROM $strTableRequestName WHERE supplier='".$arrSelectMember['username']."' AND status <> '-1'";
		//echo "<br>".$strSelectRequestOfMember."<br>";
		$Select_request_result=mysql_query($strSelectRequestOfMember) or die(mysql_error());
		while ($arrSelectRequestOfMember = mysql_fetch_array($Select_request_result))
		{
			parse_str($arrSelectRequestOfMember['previous_suppliers']);
			//
			$select_desc_number_requester="SELECT * FROM $strTableUserName WHERE (supplier ='1') AND (field = '".$arrSelectRequestOfMember['field']."') AND (username != '".$arrRequestData['requester']."') AND username!='".$arrSelectMember['username']."'";			
			for ($i=0; $i<$arrSelectRequestOfMember['status']; $i++)
			{
				$select_desc_number_requester .= " AND (username !='".$arrPreviousSuppliers[$i]."') ";
			}
			$select_desc_number_requester .=" ORDER BY last_assigned_request ASC, request_handle_number ASC, request_pending_number ASC";
			//echo "<br>".$select_desc_number_requester."<br>";
			$result_desc_number_requester = mysql_query($select_desc_number_requester) or die(mysql_error());
			$arrSupplierData_desc_number_requester = mysql_fetch_array($result_desc_number_requester);
			
			//Update change request for supplier other
			$strPreviousSuppliers = $arrSelectRequestOfMember['previous_suppliers'].'arrPreviousSuppliers[]='.$arrSelectMember['username'].'&';
			$strMysql_update_change_request ="UPDATE $strTableRequestName SET previous_suppliers = '".$strPreviousSuppliers."', " .
						"status = status + 1, supplier = '".$arrSupplierData_desc_number_requester['username']."' " .
						"WHERE id = '".$arrSelectRequestOfMember['id']."'";
			mysql_query($strMysql_update_change_request) or die(mysql_error());
			
			//Update request for new supplier
			$last_assigned_request = date('YmdHis');
			$strMysqlQuery = "UPDATE $strTableUserName SET request_pending_number = request_pending_number +1, last_assigned_request = $last_assigned_request WHERE username = '".$arrSupplierData_desc_number_requester['username']."'";
			mysql_query($strMysqlQuery) or die(mysql_error());
		}
		
		
		//send mail
		
		$strEmailTo=$arrSelectMember['email'];
		$strSubject="Xin chào ".$arrSelectMember['username'];
		$Headers="From: ".$strAdminEmail."\r\n";
		$Headers .= "MIME-Version: 1.0\r\n"; 
		$Headers .= "content-type: text/html; charset=utf-8\r\n";
		$strDir=dirname($_SERVER['PHP_SELF']);
		$message = "<html>
		<head>
		<title>Xin chào ".$arrSelectMember['username']."</title>
		</head>
		<body>
		Đây là email tự động gửi từ ban quản trị của $strWebsiteName.<br/>
		Chức năng cung cấp bài báo của bạn tạm thời ngưng hoạt động, lý do là bạn chưa có thời gian để cung cấp bài báo. Nếu có những thắc mắc gì, hoặc bạn muốn trở lại cung cấp khi có thời gian thì hãy thông báo cho ban quản trị. Cảm ơn bạn!".
		"<br>Chúng tôi rất mong nhận được sự đóng góp thường xuyên của bạn cho trang web.
		</body>
		</html>";
		
		//messages to member
		do_send($strEmailTo,$arrSelectMember['username'],$strSubject,$message);
		
		echo "Supplier này đã dừng cung cấp<br>";
		echo "Đã gửi email thông báo tới member.<br>";					
		
		//F5
		echo '<meta http-equiv="refresh" content="2; url=admin.php?action=all_supplier"/>';
	}
	elseif ($_GET['action']=="message_2day") 
	{
		$strMysqlQuery="SELECT * FROM $strTableRequestName WHERE status>=0";
		$strMysqlQuery_Distinct="SELECT DISTINCT supplier,title,requester,supplier,date_request FROM $strTableRequestName WHERE status>=0";
		$result = mysql_query($strMysqlQuery) or die(mysql_error());
		$result_user = mysql_query($strMysqlQuery_Distinct) or die(mysql_error());
		if ($num_row=mysql_num_rows($result)==0)
		{
			echo "Hiện tại không có yêu cầu nào cần xử lý";
		}
		else
		{
			$today=date('Y-m-d');
			echo "Danh sách bài báo đang có thời gian chờ 2-3 ngày.";
				echo "<table width=100% align='center'>\r\n" .
								"<tr>\r\n" .
								"<th>Tiêu đề</th>\r\n" .
								"<th>Người yêu cầu</th>\r\n" .
								"<th>Người cung cấp</th>\r\n" .
								"<th>Số ngày chờ</th>\r\n" .
								"</tr>";
					$strTrClass="odd";			
			while ($arrList_request = mysql_fetch_array($result))
			{				
				if ((compareDate($today,$arrList_request['date_request'])>=2)&&(compareDate($today,$arrList_request['date_request'])<=3))
				{					
					echo "<tr class=\"$strTrClass\">\r\n" .
								"\t<td>" .$arrList_request['title']."</td>\r\n".
								"\t<td>" .$arrList_request['requester']."</td>\r\n".
								"\t<td>" .$arrList_request['supplier']."</td>\r\n".
								"\t<td>" .compareDate($today,$arrList_request['date_request'])."</td>\r\n".
								"</tr>\r\n";
								$strTrClass=str_replace($strTrClass,"",'oddeven');												
				}				
			}
			echo "</table>\r\n<br>";			
			echo "<br>&nbsp;Danh sách thành viên có bài báo chờ xử lý 2-3 ngày.";
				echo "<table width=100% align='center'>\r\n" .
								"<tr>\r\n" .
								"<th>User Name</th>\r\n" .
								"<th>Yêu cầu đã xử lý</th>\r\n" .
								"<th>Yêu cầu đang chờ</th>\r\n" .
								"<th>Mail to supplier</th>\r\n" .
								"</tr>";
					$strTrClass="odd";
			while ($arrList_request_user = mysql_fetch_array($result_user))
			{
				if ((compareDate($today,$arrList_request_user['date_request'])>=2)&&(compareDate($today,$arrList_request_user['date_request'])<=3))
				{
					$str_select_user="SELECT * FROM $strTableUserName WHERE username='".$arrList_request_user['supplier']."'";
					$result_user_list = mysql_query($str_select_user) or die(mysql_error());
					while ($arrList_user = mysql_fetch_array($result_user_list))
					{				
						echo "<tr class=\"$strTrClass\">\r\n" .
								"\t<td>" .$arrList_user['username']."</td>\r\n".
								"\t<td>" .$arrList_user['request_handle_number']."</td>\r\n".
								"\t<td>" .$arrList_user['request_pending_number']."</td>\r\n".
								"\t<td>".
								"<form name=\"frm".$arrList_user['ID']."\" method=\"POST\" action=\"admin.php?action=mail_2day\">".
									"<input type=\"hidden\" name=\"txtId\" value=\"".$arrList_user['ID']."\"/>".
									"<input type=\"submit\" name=\"btnStop\" value=\" Send mail \"/>".
									"</form>".
								"</td>\r\n".
								"</tr>\r\n";
								$strTrClass=str_replace($strTrClass,"",'oddeven');																				
					}
				}				
			}			
			echo "</table>\r\n";
			echo "&nbsp;<form name=\"frmMailAll\" method=\"POST\" action=\"admin.php?action=mail_all_2day\">".									
									"<input type=\"submit\" name=\"btnStop\" value=\" Mail to all this supplier \"/>".
									"</form>";
		}
	}
	elseif ($_GET['action']=="mail_all_2day")
	{
		$strMysqlQuery_Distinct="SELECT DISTINCT supplier,title,requester,supplier,date_request FROM $strTableRequestName WHERE status>=0";
		$result_user = mysql_query($strMysqlQuery_Distinct) or die(mysql_error());
		if ($num_row=mysql_num_rows($result)==0)
		{
			echo "Hiện tại không có yêu cầu nào cần xử lý";
		}
		else
		{
			$today=date('Y-m-d');
			while ($arrList_request_user = mysql_fetch_array($result_user))
			{
				if ((compareDate($today,$arrList_request_user['date_request'])>=2)&&(compareDate($today,$arrList_request_user['date_request'])<=3))
				{
					$str_select_user="SELECT * FROM $strTableUserName WHERE username='".$arrList_request_user['supplier']."'";
					$result_user_list = mysql_query($str_select_user) or die(mysql_error());
					while ($arrList_user = mysql_fetch_array($result_user_list))
					{				
						echo "<tr class=\"$strTrClass\">\r\n" .
								"\t<td>" .$arrList_user['username']."</td>\r\n".
								"\t<td>" .$arrList_user['request_handle_number']."</td>\r\n".
								"\t<td>" .$arrList_user['request_pending_number']."</td>\r\n".
								"\t<td>".
								"<form name=\"frm".$arrList_user['ID']."\" method=\"POST\" action=\"admin.php?action=mail_2day\">".
									"<input type=\"hidden\" name=\"txtId\" value=\"".$arrList_user['ID']."\"/>".
									"<input type=\"submit\" name=\"btnStop\" value=\" Send mail \"/>".
									"</form>".
								"</td>\r\n".
								"</tr>\r\n";
								$strTrClass=str_replace($strTrClass,"",'oddeven');																				
					}
				}				
			}			
			echo "</table>\r\n";
			echo "&nbsp;<form name=\"frmMailAll\" method=\"POST\" action=\"admin.php?action=mail_all_2day\">".									
									"<input type=\"submit\" name=\"btnStop\" value=\" Mail to all this supplier \"/>".
									"</form>";
		}
	}
	elseif ($_GET['action']=="mail_2day")
	{
		$strSelectQuery="SELECT * FROM $strTableUserName WHERE id = '".$_POST['txtId']."'";
		//echo $strSelectQuery;
		$Select_result=mysql_query($strSelectQuery) or die(mysql_error());
		$arrSelectMember = mysql_fetch_array($Select_result);
		
		$strEmailTo=$arrSelectMember['email'];
		$strSubject="Xin chào ".$arrSelectMember['username'];
		$Headers="From: ".$strAdminEmail."\r\n";
		$Headers .= "MIME-Version: 1.0\r\n"; 
		$Headers .= "content-type: text/html; charset=utf-8\r\n";
		$strDir=dirname($_SERVER['PHP_SELF']);
		$message = "<html>
		<head>
		<title>Xin chào ".$arrSelectMember['username']."</title>
		</head>
		<body>
		Đây là email tự động gửi từ ban quản trị của $strWebsiteName.<br/>
		Bạn đang có yêu cầu cần xử lý gấp. Xin mời truy cập website http://nghiencuusinh.org để xử lý yêu cầu bài báo. Cảm ơn bạn!
		</body>
		</html>";		
		//messages to member
		do_send($strEmailTo,$arrSelectMember['username'],$strSubject,$message);
		
		echo "Đã gửi email tới supplier này<br>";							
		//F5
		echo '<meta http-equiv="refresh" content="2; url=admin.php?action=message_2day"/>';
	}
	elseif ($_GET['action']=="message_4day") 
	{
		$today=date('Y-m-d');
		$strMysqlQuery="SELECT * FROM $strTableRequestName WHERE status>=0";
		$strMysqlQuery_Distinct="SELECT DISTINCT supplier,title,requester,supplier,date_request FROM $strTableRequestName WHERE status>=0";
		$result = mysql_query($strMysqlQuery) or die(mysql_error());
		$result_user = mysql_query($strMysqlQuery_Distinct) or die(mysql_error());
		if ($num_row=mysql_num_rows($result)==0)
		{
			echo "Hiện tại không có yêu cầu nào cần xử lý";
		}
		else
		{									
			echo "Danh sách bài báo sau thông báo 2 ngày(4 ngày kể từ ngày gửi) mà chưa được xử lý.<br>";
				echo "<table width=100% align='center'>\r\n" .
								"<tr>\r\n" .
								"<th>Tiêu đề</th>\r\n" .
								"<th>Người yêu cầu</th>\r\n" .
								"<th>Người cung cấp</th>\r\n" .
								"<th>Số ngày chờ</th>\r\n" .
								"</tr>";
					$strTrClass="odd";			
			while ($arrList_request = mysql_fetch_array($result))
			{				
				if (compareDate($today,$arrList_request['date_request'])>=35)
				{					
					echo "<tr class=\"$strTrClass\">\r\n" .
								"\t<td>" .$arrList_request['title']."</td>\r\n".
								"\t<td>" .$arrList_request['requester']."</td>\r\n".
								"\t<td>" .$arrList_request['supplier']."</td>\r\n".
								"\t<td>" .compareDate($today,$arrList_request['date_request'])."</td>\r\n".
								"</tr>\r\n";
								$strTrClass=str_replace($strTrClass,"",'oddeven');												
				}				
			}
			echo "</table>\r\n<br>";
			echo "<br>&nbsp;Danh sách thành viên trễ xử lý bài báo sau thông báo 2 ngày(4 ngày kể từ ngày gửi).<br>";
				echo "<table width=100% align='center'>\r\n" .
								"<tr>\r\n" .
								"<th>User Name</th>\r\n" .
								"<th>Yêu cầu đã xử lý</th>\r\n" .
								"<th>Yêu cầu đang chờ</th>\r\n" .
								"<th>Stop supplier</th>\r\n" .
								"</tr>";
					$strTrClass="odd";
			while ($arrList_request_user = mysql_fetch_array($result_user))
			{
				if (compareDate($today,$arrList_request_user['date_request'])>=35)
				{
					$str_select_user="SELECT * FROM $strTableUserName WHERE username='".$arrList_request_user['supplier']."'";
					$result_user_list = mysql_query($str_select_user) or die(mysql_error());
					while ($arrList_user = mysql_fetch_array($result_user_list))
					{				
						echo "<tr class=\"$strTrClass\">\r\n" .
								"\t<td>" .$arrList_user['username']."</td>\r\n".
								"\t<td>" .$arrList_user['request_handle_number']."</td>\r\n".
								"\t<td>" .$arrList_user['request_pending_number']."</td>\r\n".
								"\t<td>".
								"<form name=\"frm".$arrList_user['ID']."\" method=\"POST\" action=\"admin.php?action=stop_supplier\">".
									"<input type=\"hidden\" name=\"txtId\" value=\"".$arrList_user['ID']."\"/>".
									"<input type=\"submit\" name=\"btnStop\" value=\" Stop \"/>".
									"</form>".
								"</td>\r\n".
								"</tr>\r\n";
								$strTrClass=str_replace($strTrClass,"",'oddeven');																				
					}
				}				
			}			
			echo "</table>\r\n";
		}
	}
	elseif ($_GET['action']=="all_notsupplier") 
	{
		$strMysqlQuery="SELECT * FROM $strTableUserName WHERE supplier=0 and field='".$arrUserData['field']."'";
		$result = mysql_query($strMysqlQuery) or die(mysql_error());
		echo "Tổng số thành viên không cung cấp: ".$num_row=mysql_num_rows($result);
		echo "<table align='center'>\r\n" .
					"<tr>\r\n" .
					"<th>Bí danh</th>\r\n" .
					"<th>Chuyên ngành</th>\r\n" .
					"<th>Yêu cầu đã hoàn tất</th>\r\n" .
					"<th>Yêu cầu đang chờ</th>\r\n" .
					"<th>Email</th>\r\n" .
					"<th>Stop cung cấp</th>\r\n" .
					"</tr>";
		$strTrClass="odd";
		while ($arrList_User = mysql_fetch_array($result))
		{
		 echo "<tr class=\"$strTrClass\">\r\n" .
					"\t<td>" .$arrList_User['username']."</td>\r\n".
					"\t<td>" .$arrList_User['field']."</td>\r\n".
					"\t<td>" .$arrList_User['request_handle_number']."</td>\r\n".
					"\t<td>" .$arrList_User['request_pending_number']."</td>\r\n".
					"\t<td>" .$arrList_User['email']."</td>\r\n".
					"\t<td>".
					"<form name=\"frm".$arrList_User['ID']."\" method=\"POST\" action=\"admin.php?action=start_supplier\">".
									"<input type=\"hidden\" name=\"txtId\" value=\"".$arrList_User['ID']."\"/>".
									"<input type=\"submit\" name=\"btnStart\" value=\" Start \"/>".
									"</form>".
					"</td>\r\n".
					"</tr>\r\n";
					$strTrClass=str_replace($strTrClass,"",'oddeven');
		}		
		echo "</table>\r\n";
		//echo "<div class='title' align='center'> Thong tin chi tiet cua <span style='color:#FF0000'>".$arrList_User['username']."</span></div>\r\n";		
	}
	elseif ($_GET['action']=="start_supplier") 
	{
		$strMysqlQuery = "UPDATE $strTableUserName " .
						"SET supplier = 1 ".
						"WHERE id = ".$_POST['txtId']."";
		mysql_query($strMysqlQuery) or die(mysql_error());
		$strSelectQuery="SELECT * FROM $strTableUserName WHERE id = '".$_POST['txtId']."'";
		$Select_result=mysql_query($strSelectQuery) or die(mysql_error());
		$arrSelectMember = mysql_fetch_array($Select_result);
		//send mail
		$strEmailTo=$arrSelectMember['email'];
		$strSubject="Xin chào ".$arrSelectMember['username'];
		$Headers="From: ".$strAdminEmail."\r\n";
		$Headers .= "MIME-Version: 1.0\r\n"; 
		$Headers .= "content-type: text/html; charset=utf-8\r\n";
		$strDir=dirname($_SERVER['PHP_SELF']);
		$message = "<html>
			<head>
			<title>Chào mừng ".$_POST["frmUsername"]."</title>
			</head>
			<body>
			Đây là email tự động gửi từ ban quản trị của $strWebsiteName.<br/>
			<br>Cám ơn bạn đã đồng ý tham gia cung cấp tài liệu tại www.nghiencuusinh.org.  
			Là một supplier (người cung cấp tài liệu) xin bạn lưu ý những điều sau :
			<br><br>
			Ý nghĩa: 
			Khi bạn đồng ý tham gia, chắc chắn bạn đã ý thức được chúng ta làm việc này hoàn toàn không vì lợi ích cá nhân. Đơn giản, chúng ta chỉ muốn góp một phần nhỏ vào việc giải quyết tình trạng khan hiếm tài liệu (chủ yếu là các bài báo trên tạp chí quốc tế) dùng trong nghiên cứu, giảng dạy và học tập tại Việt Nam. 
			www.nghiencuusinh.org mặc dù chỉ là một giải pháp tạm thời nhưng hết sức quan trọng. Cho tới khi các trường đại học Việt Nam có thể tự bỏ tiền ra mua báo, www.nghiencuusinh.org chắc chắn sẽ vẫn là một địa chỉ vô cùng hữu ích.
			<br><br>
			Nghĩa vụ: 
			-	Có trách nhiệm với yêu cầu gửi tới bạn : lấy sớm, lấy đúng bài báo, gửi tới đúng người yêu cầu, lưu bài báo lại trong một thời gian nhất định (phòng trường hợp bạn gửi mail nhưng quên không attach) và đặc biệt phải check mail hoặc kiểm tra mục “Yêu cầu gửi đến bạn” hằng ngày tại www.nghiencuusinh.org.
			-	Khuyến khích giúp đỡ các suppliers khác: khi bạn có thời gian hãy kiểm tra thêm mục “Tất cả các bài báo đang chờ” để xử lý giùm các yêu cầu của các supplier khác. Điều này sẽ giúp quá trình xử lý yêu cầu nhanh hơn và giảm tải cho các suppliers khác khi họ bận việc đột xuất.
			-	Có trách nhiệm quảng bá cho www.nghiencuusinh.org. Càng nhiều người tham gia cung cấp tài liệu, cũng như càng nhiều người tham gia gửi yêu cầu, công việc của chúng ta càng hiệu qủa và hữu ích. 
			<br><br>
			Bạn có thể tham khảo chi tiết hơn về quyền lợi và nghĩa vụ của một supplier tại http://www.nghiencuusinh.org/about.php. Cám ơn bạn rất nhiều
			<br/>Chúng tôi rất mong nhận được sự đóng góp thường xuyên của bạn cho trang web.
			</body>
			</html>";
		//messages to member
		do_send($strEmailTo,$arrSelectMember['username'],$strSubject,$message);
		echo "User này đã trở thành supplier<br>";
		echo "Đã gửi email thông báo tới member.<br>";
		echo '<meta http-equiv="refresh" content="2; url=admin.php?action=all_notsupplier"/>';
	}
	else
	{
		echo '<a href="admin.php?action=mail">Gửi email nhắc việc cho các suppliers</a><br />'."\n";
		echo '&nbsp;<a href="admin.php?action=message_2day">Supplier có yêu cầu chờ qúa 2 ngày</a><br />'."\n";
		echo '&nbsp;<a href="admin.php?action=message_4day">Supplier nhận thông báo qúa 2 ngày</a><br />'."\n";
		echo '&nbsp;<a href="admin.php?action=announce">Gửi thông báo </a><br />'."\n";
		echo '&nbsp;<a href="admin.php?action=users">Danh sách thành viên </a><br />'."\n";
		echo '&nbsp;<a href="admin.php?action=all_supplier">Danh sách supplier </a><br />'."\n";
		echo '&nbsp;<a href="admin.php?action=all_notsupplier">Danh sách thành viên không cung cấp</a><br />'."\n";		
		echo '&nbsp;<a href="admin.php?action=requests">Danh sách bài báo </a><br />'."\n";
		echo '&nbsp;<a href="admin.php?action=topsuppliers">Top suppliers</a>';		
	}
}
	// Make a MySQL Connection
	///////////////////////////////////////////////////////

?>

<!-- InstanceEndEditable --></td>
<td width="30%" align="left" valign="top" bgcolor="#CCCC66"><?php
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
				///////Get list of requests pending	/////////////
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
		}/*		
		if ($arrUserData['supplier']==5)
		{
			echo "<a href=\"account.php?type=active_supplier\"> Tham gia cung cấp bài báo</a><br>";
		}
		elseif ($arrUserData['supplier']==1)
		{
			echo "<a href=\"account.php?type=cancel_supplier\"> Tạm ngưng cung cấp bài báo</a><br>";
		}*/
		//echo "<a href=\"account.php?type=receive_paper\"> Nhận bài đã yêu cầu </a><br>";
		echo "<a href=\"account.php?type=change\"> Thay đổi thông tin cá nhân </a><br>";			
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
    <td colspan="2" valign="top" align="center"><!-- Google CSE Search Box Begins  -->
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
