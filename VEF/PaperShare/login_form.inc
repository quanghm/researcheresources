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
		echo "    <td><input type=\"text\" name=\"frmUsername\"></td>\n";
		echo "  </tr>\n";
		echo "  <tr>\n";
		echo "    <td scope=\"row\" align=\"left\">Mật khẩu</td>\n";
		echo "    <td><input type=\"password\" name=\"frmPassword\"></td>";
		echo "  </tr>";
		echo "  <tr>\n";
		echo "	  <td colspan=\"2\" align=\"center\"> <input type=\"submit\" value=\"Vừng ơi mở ra!\"> </td>\n";
		echo "  </tr>";
		echo "</table>";
		echo "</form>";
		echo "<a href=\"register.php\">Đăng ký thành viên...</a>";
?>		