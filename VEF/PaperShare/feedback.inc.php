<?php
function freeRTE_Preload($content) {
	// Strip newline characters.
	$content = str_replace(chr(10), " ", $content);
	$content = str_replace(chr(13), " ", $content);
	// Replace single quotes.
	$content = str_replace(chr(145), chr(39), $content);
	$content = str_replace(chr(146), chr(39), $content);
	// Return the result.
	return $content;
}
// Send the preloaded content to the function.
$content = freeRTE_Preload("Soan thu gop y...");

echo "<div class='title' align='center'> Gửi thư góp ý cho trang web</div></br>\r\n";
if (isset($_SESSION['ErrMesFeedback'])and ($_SESSION['ErrMesFeedback']!==''))
{
	echo "<center><span class=\"error\">" .
			$_SESSION['ErrMesFeedback']."</span></center>\r\n";
	$_SESSION['ErrMesFeedback']="";
}
?>
<table width="60%">
	<tr>
		<td align="center">
			<form onsubmit='return confirm("Gửi góp ý?");' method="post" action="post.php">
			Email của bạn (không bắt buộc): <input type="text" name="senderEmail" >
			<input type="hidden" name="action" value="send"/>
			<!-- Include the Free Rich Text Editor Runtime -->
			<script src="<? echo "incs/rte" ?>/js/richtext.js" type="text/javascript" language="javascript"></script>
			<!-- Include the Free Rich Text Editor Variables Page -->
			<script src="<? echo "incs/rte" ?>/js/config.js.php" type="text/javascript" language="javascript"></script>
			<!-- Initialise the editor -->
			<script>
			initRTE('<?= $content ?>', 'example.css');
			</script>
			<input type="submit" value="Gui gop y">
			</form>
		</td>
	</tr>
</table>
<?php
/*
 * Created on Oct 16, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 *
 * echo "<table align='center'>\r\n" .
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
*/?>
