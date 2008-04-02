<?php
if (!isset($strConn))
{
	// Make a MySQL Connection
	$strConn = mysql_connect($strDatabaseHost, $strAdmin, $strAdminPass) or die(mysql_error());
	mysql_select_db($strDatabaseName) or die(mysql_error());
	////////////////////////////////////////////////////////
}	
?>