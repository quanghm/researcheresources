<?php
include "chk_login.inc";
if ((logged_in())&& (!isset($strConn)))
{
	include "config.php";
	include "dbconnect.php";
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title>Chúng tôi nói về chúng tôi</title>



<?php echo'<link href="Theme/Default/style.css" rel="stylesheet" type="text/css" />'; ?>
</head>

<body>
<table width="999" border="0" align="center">
  <tr bgcolor="#CCCC66" align="center">
    <td width="33%" height="40" nowrap="nowrap" ><?php echo "<a href=\"index.php\" class=\"menu\">"?><span class="menu">Trang chủ</span><?php echo"</a>"; ?></td>
    <td width="33%" height="40" >
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
    <td height="40"> <?php echo "<a href=\"about.php\" class=\"menu\">Về chúng tôi</a>"; ?></td>
  </tr>
  <tr>
    <td height="500"colspan="3" valign="top">
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
</div><p align="justify"><em>Để website  hoạt động có hiệu quả, chúng tôi tha thiết kêu gọi những ai có khả năng làm  người cung cấp đối với một trong các lĩnh vực nêu trên, bất kể đang ở Anh,  Pháp, Mỹ, Đức...tình nguyện <a href="register.php">đăng ký</a> tham gia website. Thời gian và công sức mà  các bạn bỏ ra sẽ là vô cùng quý báu. Cảm ơn các bạn.</em></p>	</td>
  </tr>
</table>
</body>
</html>
