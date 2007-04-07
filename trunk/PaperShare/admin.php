<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>
<?php
	include("config.php");
	// Make a MySQL Connection
	$strConn = mysql_connect($strDatabaseHost, $strAdmin, $strAdminPass) or die(mysql_error());
	mysql_select_db($strDatabaseName) or die(mysql_error());
	////////////////////////////////////////////////////////
	if (mysql_error() == 0)
	{
		echo "success";
	}
	mysql_close($strConn);
?>
</body>
</html>
