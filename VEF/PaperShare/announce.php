<?php
/*
 * Created on Nov 5, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
include "config.php";
if (!isset($_POST['start'])){$_POST['start']=0;}
include "dbconnect.php";
$strMysqlQuery='SELECT * FROM '.$strTableAnnouncement.' ORDER BY id DESC LIMIT '.$_POST['start'].',5';
$arrAnnouncementList = mysql_query($strMysqlQuery) or die(mysql_error());

while ($arrAnnouncementData = mysql_fetch_array($arrAnnouncementList))
{		 
	$today=date('Y-m-d');
	echo"<br/><strong>".$arrAnnouncementData['date']."</strong>";
	if (compareDate($today,$arrAnnouncementData['date'])<=5)
	{		
		echo "&nbsp;<img src=\"images/new.gif\" border=0>"; 
	}
	echo "<br />\r\n" .
			$arrAnnouncementData['content']."<br />\r\n" .
					"____________________________<br /><br />\r\n";
}
?>
<?php
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