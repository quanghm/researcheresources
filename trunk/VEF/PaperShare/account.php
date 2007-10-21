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
<title>Hồ sơ cá nhân</title>
<!-- InstanceEndEditable -->
<?php echo'<link href="Theme/Default/style.css" rel="stylesheet" type="text/css" />'; ?>
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
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
	if (logged_in())
	{
		//////////// Select user from database /////////////
		$strMyQuery = "SELECT * FROM $strTableUserName WHERE username = '".$_SESSION['username']."'";
		$result = mysql_query($strMyQuery) or die(mysql_error());
		$arrUserData = mysql_fetch_array($result);
		////////////////////////////////////////////////////
	
		///////////  	Set up user's menu      ////////////
		echo "<table width=\"100%\" cellpadding=\"1\" align=\"center\" cellspacing=\"1\">\n";
		echo "  <tr align=\"center\" bgcolor=\"#CCCCCC\">\n";
		echo "    <td height=\"30\" bgcolor=\"#CCCCCC\"";
		if ($arrUserData['supplier']) {echo 'width="33%"';}
		else {echo 'width ="50%"';}
		echo "><a href=\"account.php\" class=\"submenu\" >Thông tin chung</a> </th>\n";
		echo "    <td bgcolor=\"#CCCCCC\"";
		if ($arrUserData['supplier']) {echo 'width="33%"';}
		else {echo 'width ="50%"';}
		echo "><a href=\"account.php?type=articles\"class=\"submenu\">Bài báo đã yêu cầu</a> </td>\n";
		if ($arrUserData['supplier'])
		{
			echo "	  <td bgcolor=\"#CCCCCC\"><a href=\"account.php?type=request\"class=\"submenu\">Các yêu cầu gửi tới bạn</a> </td>\n";
		}
		echo "  </tr>\n</table>\n";
		/////////////////////////////////////////////////////
		
		//////	Default is view general information 	//////
		if (!(isset($_GET['type'])))
		{
			$_GET['type'] = '';
		}
		
		//////  Print out requested user's information 	//////
		if ($_GET['type'] == 'articles')    //// If view the articles user requested
		{
			if (!isset($_GET['sortby']))
			{
				$_GET['sortby']= "date_request";
			}
			if (!isset($_GET['order']))
			{
				$_GET['order']="DESC";
			}
			$strMyQuery = "SELECT * FROM $strTableRequestName WHERE requester = '".$_SESSION['username']."' ORDER BY ".$_GET['sortby']." ".$_GET['order'];
			$result = mysql_query($strMyQuery) or die(mysql_error());
			if (mysql_num_rows($result) == 0)
			{	
				echo "Bạn chưa yêu cầu bài báo nào!";
			}
			else
			{
				echo "<table width=\"100%\" border=\"0\" cellpadding=\"1\" cellspacing=\"1\">";
				echo "	<tr >\n";
				echo "		<th scope=\"col\">STT</th>\n";
				echo "      <th scope=\"col\" onclick=\"window.location='account.php?type=articles&sortby=title&order=".str_replace($_GET['order'],"",'ASCDESC');
				echo "';\">Tiêu đề</th>\n";
				echo "      <th scope=\"col\" onclick=\"window.location='account.php?type=articles&sortby=author&order=".str_replace($_GET['order'],"",'ASCDESC');
				echo "';\">Tác giả</th>\n";
				echo "      <th scope=\"col\" onclick=\"window.location='account.php?type=articles&sortby=journal&order=".str_replace($_GET['order'],"",'ASCDESC');
				echo "';\">Tạp chí</th>\n";
				echo "      <th scope=\"col\" onclick=\"window.location='account.php?type=articles&sortby=year&order=".str_replace($_GET['order'],"",'ASCDESC');
				echo "';\">Năm</th>\n";
				echo "      <th scope=\"col\" onclick=\"window.location='account.php?type=articles&sortby=date_request&order=".str_replace($_GET['order'],"",'ASCDESC');
				echo "';\">Ngày yêu cầu</th>\n";
				echo "      <th scope=\"col\" onclick=\"window.location='account.php?type=articles&sortby=status&order=".str_replace($_GET['order'],"",'ASCDESC');
				echo "';\">Tình trạng</th>\n";
				echo "  </tr>";
				$ArticleIndex = 1;
				$row = 0;
				while ($arrArticleList = mysql_fetch_array($result))
				{
					
					echo "	<tr ";
					if (($row % 2) == 1)
					{
						echo 'class="odd"';						
					}
					else
					{
						echo 'class="even"';
					}
					echo ">
								<td >".$ArticleIndex++."</td>\n";
					echo "      <td >".$arrArticleList['title']."</td>\n";
					echo "      <td >".$arrArticleList['author']."</td>\n";
					echo "      <td >".$arrArticleList['journal']."</td>\n";
					echo "      <td >".$arrArticleList['year']."</td>\n";
					echo "      <td >".$arrArticleList['date_request']."</td>\n";
					if ($arrArticleList['status'] >= 0)				// Status is pending
					{
						echo "      <td >Đang chờ</td>\n";
					}
					elseif ($arrArticleList['status'] == -2)		// Status is Failed
					{
						echo "      <td >Thất bại</td>\n";
					}
					elseif ($arrArticleList['status'] == -1)		// Status is finished
					{
						echo "      <td >Hoàn tất</td>\n";
	
					}
					elseif ($store_article_on_server)
					{
						echo "      <td ><a href=\"".$arrArticleList['download_link']."\">Ready</a></td>\n";				
					}
					echo "  </tr>\n";
					$row++;
				}
				echo "</table>";
			}
		}
		elseif ($_GET['type'] == 'request')   /////// If View the requests pending
		{			
			if (!isset($_GET['sortby']))
			{
				$_GET['sortby']= "date_request";
			}
			if (!isset($_GET['order']))
			{
				$_GET['order']="DESC";
			}
			$strMysqlQuery = "SELECT * FROM $strTableRequestName WHERE (supplier = '".$_SESSION['username']."') AND (status>=0) ORDER BY ".$_GET['sortby']." ".$_GET['order'];
			$result = mysql_query($strMysqlQuery) or die(mysql_error());

			if (mysql_num_rows($result) == 0)
			{	
				echo "Hiện không có yêu cầu nào được gửi tới bạn!";
			}
			else
			{
				echo "<table width=\"100%\" cellpadding=\"1\" cellspacing=\"1\">";
				echo "	<tr>\n";
				echo "		<th scope=\"col\">STT</th>\n";
				echo "      <th scope=\"col\" onclick=\"window.location='account.php?type=request&sortby=title&order=".str_replace($_GET['order'],"",'ASCDESC');
				echo "';\">Tiêu đề</th>\n";
				echo "      <th scope=\"col\" onclick=\"window.location='account.php?type=request&sortby=author&order=".str_replace($_GET['order'],"",'ASCDESC');
				echo "';\">Tác giả</th>\n";
				echo "      <th scope=\"col\" onclick=\"window.location='account.php?type=request&sortby=journal&order=".str_replace($_GET['order'],"",'ASCDESC');
				echo "';\">Tạp chí</th>\n";
				echo "      <th scope=\"col\" onclick=\"window.location='account.php?type=request&sortby=year&order=".str_replace($_GET['order'],"",'ASCDESC');
				echo "';\">Năm</th>\n";
				echo "      <th scope=\"col\" onclick=\"window.location='account.php?type=request&sortby=date_request&order=".str_replace($_GET['order'],"",'ASCDESC');
				echo "';\">Ngày yêu cầu</th>\n";
				echo "      <th scope=\"col\" onclick=\"window.location='account.php?type=request&sortby=status&order=".str_replace($_GET['order'],"",'ASCDESC');
				echo "';\">Trạng thái</th>\n";
				echo "  </tr>";
				$ArticleIndex = 1;
				$row=1;
				while ($arrArticleList = mysql_fetch_array($result))
				{
					//$row++;
					echo "	<tr ";
					if ($row++%2)
					{
						echo 'class="even"';
					}
					else
					{
						echo 'class="odd"';
					}
					echo " onclick=\"document.frm$ArticleIndex.submit();\"";
					echo ">
								<td >".$ArticleIndex."</td>\n";
					echo "      <td >".$arrArticleList['title']."</td>\n";
					echo "      <td >".$arrArticleList['author']."</td>\n";
					echo "      <td >".$arrArticleList['journal']."</td>\n";
					echo "      <td >".$arrArticleList['year']."</td>\n";
					echo "      <td >".$arrArticleList['date_request']."</td>\n";
					echo "      <td align=\"center\"><form name=\"frm".$ArticleIndex++."\" method=\"POST\" action=\"account.php?type=handle_request\">
									<input type=\"hidden\" name=\"frmRequestID\" value=\"".$arrArticleList['id']."\"/>
									<input type=\"submit\" name=\"frmSubmiHandle\" value=\" Chi tiết \"/>
									</form></td>\n";

				/*	if ($arrArticleList['status'] == 'pending')
					{
						echo "      <td ><form method=\"POST\" action=\"account.php?type=handle_request\">
										<input type=\"hidden\" name=\"frmRequestID\" value=\"".$arrArticleList['ID']."\"/>
										<input type=\"submit\" name=\"frmSubmiHandle\" value=\" Giết \"/></form></td>\n";
	
					}
					else
					{
						echo "      <td >".$arrArticleList['status']."</td>\n";				
					}*/
					echo "  </tr>\n";
				}
				echo "</table>";
			}
		}
		elseif ($_GET['type'] == 'submit_request')   ///// If submit a request
		{	
			echo '<script language="javascript">
		function ChkForm()
		{
			if (document.frmRequest.txtTitle.value == "")
			{
				alert("Bạn chưa điền tên bài báo");
				return false;
			}
			if (document.frmRequest.txtAuthor.value == "")
			{
				alert("Bạn chưa điền tên tác giả");
				return false;
			}
			if (document.frmRequest.txtJournal.value =="")
			{
				alert("Bạn chưa điền tên tạp chí");
				return false;
			}
			if (document.frmRequest.txtIssue.value == "")
			{
				alert("Bạn chưa điền số tạp chí");
				return false;
			}
			if (document.frmRequest.txtYear.value == "")
			{
				alert("Bạn chưa điền năm xuất bản");
				return false;
			}
			if (document.frmRequest.txtLink.value == "")
			{
				alert("Bạn chưa điền link");
				return false;
			}
			if (document.frmRequest.optField.value == "0")
			{
				alert("Bạn phải chọn chuyên ngành");
				return false;
			}
			return true;
		}
		</script>';				///// javascript function to deal with request data
			echo "<center> Yêu cầu bài báo<br>\n";
			echo "Bạn cần điền vào <strong>tất cả</strong> các thông tin "."<a onclick=\"javascript:window.open('help.php','wnd_help','height=600,width=500')\"><img height=\"20\" src=\"Theme/Default/Images/questionmark.jpg\"></a>\n";
			if (isset($_SESSION['ErrMes']) and($_SESSION['ErrMes']==!""))
			{
				echo "<br><strong><font color=\"#FF0000\">".$_SESSION['ErrMes']."</font></strong>";
				$_SESSION['ErrMes']="";
			}
			echo "</center>\n";
			echo '<form name="frmRequest" onSubmit="return ChkForm();" method="post" action="submit_request.php">
		  <table width="100%" cellpadding="0">
			<tr>
			  <td width="31%"><div align="left">Tên bài báo </div></td>
			  <td>
				<input name="txtTitle" type="text"';
			if (isset($_POST["txtLink"]))
			{
				echo'value="'.$_POST["txtTitle"].'"';
			}
			echo' size="50" />
			  </td>
			</tr>
			<tr>
			  <td ><div align="left">Tác giả </div></td>
			  <td>
				<input name="txtAuthor" type="text" size="50"/>
			  </td>
			</tr>
			<tr>
			  <td><div align="left">Tạp chí </div></td>
			  <td><input name="txtJournal" type="text" size="50" /></td>
			</tr>
			<tr>
			  <td><div align="left">Link</div></td>
			  <td>
				<input name="txtLink" type="text" size="50" /> 
			  </td>
			</tr>
			<tr>
			  <td><div align="left">Số</div></td>
			  <td>
				<input name="txtIssue" type="text" size="15" />
			  </td>
			</tr>
			<tr>
			  <td><div align="left">Năm xuất bản </div></td>
			  <td>
				<input name="txtYear" type="text" size="15" />
			  </td>
			</tr>
			<tr>
			  <td><div align="left">Số trang </div></td>
			  <td><input name="txtPages" type="text" size="15" /></td>
			</tr>
			<tr>
			  <td><div align="left">Chuyên ngành </div></td>
			  <td><select name="optField">
			  <option value="0">Choose a Field of Study...</option>'."\n";
		  	for ($index=1;$index<$NumberOfField;$index++)
				{
				echo "	  <option value=\"$index\">$arrFieldList[$index]</option>\n";
				}
	  
			echo '		</select></td>
			</tr>
			<tr>
			  <td colspan="2">        
				<div align="center">
				  <button name="btnSubmit" type="submit"> Gửi yêu cầu </button>
				  <input name="frmReset" type="reset" value="Làm lại từ đầu" />
				</div>
			  </td>
			</tr>
		  </table>
		</form>'."\r\n";				/////	User's Interface
		echo '<script language="javascript">'."\r\n";
			if (isset($_POST))
			{
				foreach ($_POST as $key =>$value)
				{
					if ($key=="onFocus")
					{
						echo 'document.frmRequest.'.$value.".focus();\r\n";
					}
					elseif ($key!=="optField")
					{
						echo 'document.frmRequest.'.$key.'.value="'.$value.'";'."\n";
					}
					elseif ($key=="optField")
					{
						echo 'document.frmRequest.optField['.$value.'].selected="1";'."\r\n";
					}
				}
			}
			echo '</script>';

		}
		elseif ($_GET['type'] == 'change')			////// Change personal information
		{
		echo'<script language="javascript">
		function DataVerify()
		{
			if (document.frmChangeInfo.frmOldPassword.value=="")
			{
				if ((document.frmChangeInfo.frmNewPassword.value!=="")||(document.frmChangeInfo.frmNewEmail!=="';
		echo $arrUserData['email'].'"))
				{
					alert("Bạn phải nhập mật khẩu cũ");
					return false;
				}
			}
			else	
			{
				if ((document.frmChangeInfo.frmNewPassword.value.length < 6)&& (document.frmChangeInfro.frmNewPassword.value.length >0))
					{
						alert("Mật khẩu mới quá ngắn");
						return false;
					}
				if (document.frmChangeInfo.frmNewPassword.value !== document.frmChangeInfo.frmNewPasswordConfirm.value)
					{
						alert("Xác nhận mật khảu không đúng!");
						return false;
					}
				if (document.frmChangeInfo.frmNewEmail.value !== document.frmChangeInfo.frmNewEmailConfirm.value)
					{
						alert("Xác nhận email không đúng!");
						return false;
					}
				if (document.frmChangeInfo.frmNewField.value==0)
					{
						alert("Bạn phải chọn một chuyên ngành!");
						return false;
					}
			}
			return true;
		}
		</script>';
	
			echo "<center>Thông tin cá nhân";
			echo"</center>";	
			if ($_SESSION['ErrMes']!=='') { echo '<center> <font color="#FF0000">'.$_SESSION['ErrMes']."</font></center>";$_SESSION['ErrMes']='';}
			echo '<form id="frmChangeInfo" method="POST" onSubmit="return DataVerify()" action="change_info.php" name="frmChangeInfo">
			<table align="center" width="100%" border="0">
			  <tr>
				<td width="50%">Bí danh </td>
				<td colspan="2"><input type="text" name="frmUsername" readonly="true" value="'.$arrUserData['username'].'"></td>
				</tr>
			  <tr>
				<td >Mật khẩu cũ * </td>
				<td colspan="2"><input type="password" name="frmOldPassword"></td>
			  </tr>
			  <tr>
				<td >Mật khẩu mới ** </td>
				<td colspan="2"><input type="password" name="frmNewPassword"></td>
				</tr>
			  <tr>
				<td >Xác nhận lại mật khẩu mới ** </td>
				<td colspan="2"><input type="password" name="frmNewPasswordConfirm"></td>
				</tr>
			  <tr>
				<td >Email</td>
				<td colspan="2"><input type="text" name="frmNewEmail" value="'.$arrUserData['email'].'"></td>
				</tr>
			  <tr>
				<td >Xác nhận lại email</td>
				<td colspan="2"><input type="text" name="frmNewEmailConfirm" value="'.$arrUserData['email'].'"></td>
				</tr>
			  <tr>
				<td >Chuyên ngành</td>
				<td colspan="2"><select name="frmNewField">
				  <option value="0">Choose a Field of Study...</option>'."\n";
					for ($index=0;$index<$NumberOfField;$index++)
					{
					echo "	  <option value=\"$arrFieldList[$index]\"";
					if ($arrFieldList[$index]==$arrUserData['field']){echo  'selected="selected"';}
					echo ">$arrFieldList[$index]</option>\n";
					}
			echo'</select></td>
				</tr>
			  <tr>
				<td >Bạn có muốn làm người cung cấp</td>
				<td width="25%"><label>
				  <input name="frmNewSupplier" type="radio" value="1"';
			if ($arrUserData['supplier']) {echo "checked";}	  
			echo'>
				Có</label></td>
				<td width="25%"><label>
				  <input name="frmNewSupplier" type="radio" value="0"';
			if (!($arrUserData['supplier'])) {echo "checked";}	  
			echo'>
				Không</label></td>
				</tr>
			  <tr>
				<td colspan="3" align="left"><i><br>(*) Yêu cầu nếu bạn muốn thay đổi bất cứ thông tin nào. <br>
				(**) Bỏ trống nếu bạn không muốn đổi mật khẩu. </i>
				</td>
			   </tr>
			  <tr>
				<td align="center"><button type="submit" name="btnSubmit">Úm ba la, hô BIẾN</button></td>
				<td colspan="2" align="center"><button type="reset" name="button" onClick="javascript: history.back();">Quay trở lại trang trước </button></td>
			  </tr>
			</table>
			</form>';				
		}
		elseif ($_GET['type']=='handle_request')	//////	Handle request
		{
			//////////// Get request   ///////////////////
			$strMysqlQuery = "SELECT * FROM $strTableRequestName WHERE id = ".$_POST['frmRequestID'];
			$result=mysql_query($strMysqlQuery) or die(mysql_error());
			$arrRequestData = mysql_fetch_array($result);
			//////////////////////////////////////////////
			
			/////////// Get Requester's email ////////////
			$strMysqlQuery = "SELECT * FROM ".$strTableUserName." WHERE username = '".$arrRequestData['requester']."'";
			$result = mysql_query($strMysqlQuery) or die(mysql_error());
			$arrRequesterData=mysql_fetch_array($result);
			//////////////////////////////////////////////
			
			echo "<center> Xử lý yêu cầu </center>\n";
			if (isset($_SESSION['ErrMes'])&&($_SESSION['ErrMes']!==""))
			{
				echo "<center><span class=\"error\">".$_SESSION['ErrMes']."</span></center>";
				$_SESSION['ErrMes']="";
			}
			echo '<table width="100%" cellspace="0">
			  <tr>
				<td width="30%">Người đề nghị </td>
				<td><a href="mailto: '.$arrRequesterData['email'].'" >';
			echo $arrRequestData['requester'];
			echo '</a></td>
			  </tr>
			  <tr>
				<td>Tiêu đề </td>
				<td><a href="'.$arrRequestData['download_link'].'" target="_blank">';
			echo $arrRequestData['title'];
			echo '</a></td>
			  </tr>
			  <tr>
				<td>Tác giả </td>
				<td>'.$arrRequestData['author'].'</td>
			  </tr>
			  <tr>
				<td>Tạp chí </td>
				<td>'.$arrRequestData['journal'].'</td>
			  </tr>
			  <tr>
				<td>Số</td>
				<td>'.$arrRequestData['issue'].'</td>
			  </tr>
			  <tr>
				<td>Năm xuất bản </td>
				<td>'.$arrRequestData['year'].'</td>
			  </tr>
			</table>
			<form method="POST" name="frmFinishRequest" action="handle_request.php?action=finishing"> 
				<input name="frmHandlingRequestID" type="hidden" value="'.$arrRequestData['id'].'"/>
				<a href="javascript: document.frmFinishRequest.submit()">Báo cáo hoàn tất </a></form>';
			if ($arrRequestData['status']<$max_pass)
			{
				echo '<form method="POST" name="frmPassRequest" action="handle_request.php?action=passing">
					<input name="frmHandlingRequestID" type="hidden" value="'.$arrRequestData['id'].'"/>
		<a href="javascript: document.frmPassRequest.submit()">Chuyển yêu cầu cho:</a>
<input type="text" name="frmSupplier"/>
<input type="submit" value="Chuyển"/><br/>
<span style="font-style: italic; font-size:small; color:#CC0033">(Để trống nếu bạn không muốn chỉ định người cung cấp mới)</font></span>				</form>';
			}
			else
			{
				echo '<form method="POST" name="frmPassRequest" action="handle_request.php?action=failing">
					<input name="frmHandlingRequestID" type="hidden" value="'.$arrRequestData['id'].'"/>
					<a href="javascript: document.frmPassRequest.submit()">Báo cáo thất bại </a>
				</form>';
			}
				echo '<a href="account.php?type=request">Quay lại </a>';		
		}
		else					/////////// Default: Display general information
		{
			echo "Bạn đã gửi ".$arrUserData['request_number']." yêu cầu! <a href=\"account.php?type=submit_request\">Yêu cầu bài báo</a><br>\n";
			if ($arrUserData['supplier']) 
			{
				////////	Get list of requests pending	/////////////
				$strMysqlQuery = "SELECT * FROM $strTableRequestName WHERE (supplier = '".$_SESSION['username']."') AND (status = 'pending')";
				$result = mysql_query($strMysqlQuery) or die(mysql_error());
				$request_pending = mysql_num_rows($result);
				if ($request_pending>0)
				{	echo "Hiện tại bạn có ".$request_pending." yêu cầu đang chờ xử lý! Xin nhấn vào 
					<a href=\"account.php?type=request\">đây</a><br>\n";
				}
				else
				{
					echo "Hiện tại bạn không có yêu cầu nào cần xử lý!<br>\n";
				}
			}
			echo "<a href=\"account.php?type=change\"> Thay đổi thông tin cá nhân </a>";			
		}
	}
	else
	{
	echo '<center><span class="error">Bạn chưa đăng nhập!</span></center>';
	}
	?>
	<!-- InstanceEndEditable -->	</td>
    <td width="33%" align="center" valign="top" bgcolor="#CCCC66"><?php
		if (logged_in())
		{
			//////////// Select user from database /////////////
	$strMyQuery = "SELECT * FROM $strTableUserName WHERE username = '".$_SESSION['username']."'";
	$result = mysql_query($strMyQuery) or die(mysql_error());
	$arrUserData = mysql_fetch_array($result);
	////////////////////////////////////////////////////

			echo "Chào mừng ".$_SESSION["username"]."!<button onClick=\"javascript:window.location = 'login.php?action=logout'\">Khắc xuất</button><br>\n";

		echo "Bạn đã gửi ".$arrUserData['request_number']." yêu cầu! <a href=\"account.php?type=submit_request\">Yêu cầu bài báo</a><br>\n";
		if ($arrUserData['supplier']) 
		{
			////////	Get list of requests pending	/////////////
			$strMysqlQuery = "SELECT * FROM $strTableRequestName WHERE (supplier = '".$_SESSION['username']."') AND (status >=0)";
			$result = mysql_query($strMysqlQuery) or die(mysql_error());
			$request_pending = mysql_num_rows($result);
			if ($request_pending>0)
			{	echo "Hiện tại bạn có ".$request_pending." yêu cầu đang chờ <a href=\"account.php?type=request\">xử lý!</a><br>\n";
			}
			else
			{
				echo "Hiện tại bạn không có yêu cầu nào đang chờ!<br>\n";
			}
		}
		echo "<a href=\"account.php?type=change\"> Thay đổi thông tin cá nhân </a><br>";			
		if ($arrUserData['admin']){echo "<a href=\"admin.php?action=mail\"> Gửi email nhắc việc tới suppliers </a>";}
			//////// Close connection to database /////////
			include "dbclose.php";
		}
		else
		{	
			echo "Bạn chưa đăng nhập";
			require "login_form.inc";

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
</body>
<!-- InstanceEnd --></html>