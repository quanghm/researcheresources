<?php
	// Make a MySQL Connection
	$strConn = mysql_connect($GLOBALS['strDatabaseHost'], $GLOBALS['strAdmin'], $GLOBALS['strAdminPass']) or die(mysql_error());
	mysql_select_db($GLOBALS['strDatabaseName']) or die(mysql_error());
	////////////////////////////////////////////////////////
	
	//////////  		identify user 		////////////
	
?>