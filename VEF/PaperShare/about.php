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
<title>Untitled Document</title>
<!-- InstanceEndEditable -->
<?php echo'<link href="Theme/Default/style.css" rel="stylesheet" type="text/css" />'; ?>
<!-- InstanceBeginEditable name="head" -->
<?php echo'<link href="Theme/Default/style.css" rel="stylesheet" type="text/css" />'; ?>
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
    <td width="70%" height="700" valign="top">
	<!-- InstanceBeginEditable name="body" -->
<p align="justify">Website “article exchange” do một nhóm nghiên cứu sinh Việt  Nam tại Mỹ ở nhiều lĩnh vực khác nhau lập nên. Mục đích của website là đáp ứng  nhu cầu ngày càng lớn của cộng đồng khoa học trong nước đối với các bài báo  khoa học, đồng thời thông qua đó tạo điều kiện quen biết, học hỏi và hợp tác  lâu dài giữa cộng đồng khoa học trong nước và nước ngoài. Website hoạt động như  một cầu nối giữa một bên là những người có nhu cầu và một bên là những người có  khả năng cung cấp các bài báo khoa học.   Những lĩnh vực  mà website có thể  cung cấp bao gồm: Toán học, Vật Lý, Hóa học, Sinh học, Công nghệ thông  tin,....</p>
<p align="justify"><strong>Quyền lợi và trách nhiệm của người  dùng:</strong></p>
<div align="justify">
  <ol start="1" type="1">
    <li><strong>Quyền lợi: </strong></li>
      <ul type="disc">
        <li>Tất cả mọi người có nhu cầu đối với bài báo khoa        học đều có thể sử dụng website. Sau khi lập một tài khoản cho mình, người        dùng có thể yêu cầu bài báo mình cần thông qua mẫu form có sẵn.  </li>
        <li>Bài báo sẽ được cung cấp thông qua địa chỉ email        của người dùng 1 ngày sau khi được yêu cầu. </li>
        <li>Người dùng có quyền đóng góp ý kiến xây dựng        website thông qua hòm thư góp ý.</li>
        </ul>
      <li><strong>Trách nhiệm:</strong></li>
      <ul>
        <li>Người dùng có trách nhiệm giới thiệu, quảng bá  website cho những ai có nhu cầu.</li>
            <li>Thông tin yêu cầu bài báo trên mẫu form phải hợp  lệ, trong đó quan trọng nhất là đường link trực tiếp đến bài báo. Đường link  này là nhằm tiết kiệm thời gian và công sức cho người cung cấp.</li>
            <li>Đối với những bài báo được cung cấp bởi website,  người dùng có trách nhiệm chia sẻ với người khác khi được quản trị yêu cầu.</li>
        </ul>
    </ol>
  </div>
<p align="justify"><strong>Quyền lợi  và trách nhiệm của người cung cấp:</strong></p>
<div align="justify">
  <ol>
    <li><strong>Quyền lợi:</strong></li>
      <ul>
        <li>Người cung cấp là những người có khả năng cung  cấp bài báo khoa học thông qua tài khoản của trường hoặc viện nghiên cứu. Người  cung cấp tham gia website hoàn toàn tự nguyện và có quyền dừng tham gia bất cứ  lúc nào mà không cần giải thích, sau khi đã thông báo với quản trị mạng.</li>
        <li>Người cung cấp có quyền loại bỏ những yêu cầu  không hợp lệ hoặc nằm ngoài khả năng cung cấp của mình, sau khi đã giải thích  rõ ràng với người dùng.</li>
        <li>Website sẽ cố gắng điều chỉnh để thời gian mà  mỗi người cung cấp bỏ ra mỗi ngày chỉ dao dộng trong khoảng 10-15 phút.</li>
        <li>Người cung cấp vẫn có thể yêu cầu bài báo mình  cần như một người dùng.</li>
      </ul>
      <li><strong>Trách nhiệm:</strong></li>
      <ul>
        <li>Người cung cấp có trách nhiệm đáp ứng yêu cầu  của người dùng đúng thời gian quy định: 1 ngày sau ngày yêu cầu.</li>
        <li>Website tự động phân bổ các yêu cầu một cách  công bằng cho tất cả những người cung cấp. Người cung cấp có trách nhiệm tuân  thủ sự phân công của website.</li>
      </ul>
    </ol>
</div><p align="justify"><em>Để website  hoạt động có hiệu quả, chúng tôi tha thiết kêu gọi những ai có khả năng làm  người cung cấp đối với một trong các lĩnh vực nêu trên, bất kể đang ở Anh,  Pháp, Mỹ, Đức...tình nguyện <a href="register.php">đăng ký</a> tham gia website. Thời gian và công sức mà  các bạn bỏ ra sẽ là vô cùng quý báu. Cảm ơn các bạn.</em></p>	
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
		if ($arrUserData['supplier']==0)
		{
			echo "<a href=\"account.php?type=active_supplier\"> Tham gia cung cấp bài báo</a><br>";
		}
		else
		{
			echo "<a href=\"account.php?type=cancel_supplier\"> Tạm ngưng cung cấp bài báo</a><br>";
		}
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
