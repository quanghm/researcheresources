<?php
/*
 * Created on Oct 16, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
echo "<table align='center'>\r\n" .
		"<tr><td align='center'>\r\n";
echo "Gửi thư góp ý cho trang web\r\n";
if (isset($_SESSION['ErrMesFeedback'])and ($_SESSION['ErrMesFeedback']!==''))
{
	echo "<center><span class=\"error\">" .
			$_SESSION['ErrMesFeedback']."</span></center>\r\n";
	$_SESSION['ErrMesFeedback']="";
}
echo	"<form onsubmit='return confirm(\"Gửi góp ý?\");' action='feedback.php?action=send' name='frmFeedback' method='post'>\r\n" .
		"<textarea name='txtContent' rows='20' cols='50'></textarea><br/>\r\n" .
		"<input type='submit' value='Gửi góp ý'> <input type='reset' vaule='Xóa tất cả'>" .
		"</form>";
echo "</td></tr></table>";
?>
