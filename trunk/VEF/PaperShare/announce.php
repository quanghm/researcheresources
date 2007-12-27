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
	echo"<br/><strong>".$arrAnnouncementData['date']."</strong><br />\r\n" .
			$arrAnnouncementData['content']."<br />\r\n" .
					"____________________________<br /><br />\r\n";
}
?>
