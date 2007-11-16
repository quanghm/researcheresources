<?php
		if (!isset($_SESSION["ErrMes"]))	{$_SESSION["ErrMes"] = "";	}
		if ($_SESSION["ErrMes"] !== "")
		{
			echo "<center><span class=\"error\">".$_SESSION["ErrMes"]."</span>";
			$_SESSION["ErrMes"] = "";
		}
		echo "<form action=\"login.php?action=login\" method=\"POST\">";
		echo "<table width=\"90%\" border=\"0\" align=\"center\">\n";
		echo "  <tr>\n";
		echo "	  <td scope=\"row\" align=\"left\">Bí danh</td>\n";
		echo "    <td><input type=\"text\" name=\"frmUsername\" size=\"15\"></td>\n";
		echo "  </tr>\n";
		echo "  <tr>\n";
		echo "    <td scope=\"row\" align=\"left\">Mật khẩu</td>\n";
		echo "    <td><input type=\"password\" name=\"frmPassword\" size=\"15\"></td>";
		echo "  </tr>";
		echo "  <tr>\n";
		echo "	  <td colspan=\"2\" align=\"center\"><br />\r\n<input type=\"submit\" value=\"Vừng ơi mở ra!\"> </td>\n";
		echo "  </tr>";
		echo "</table>";
		echo "</form>";
		echo "Quên mật khẩu? <a href=\"forgotpassword.php\">Tạo mật khẩu mới</a>.<br/>\r\n";
		echo "Chưa có tài khoản? <a href=\"register.php\">Đăng ký thành viên...</a>";
?>		