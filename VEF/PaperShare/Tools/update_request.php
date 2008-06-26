<?php
include "../global_config.php";
include "../global_dbconnect.php";

$strMysqlQuery = "SELECT `id`,`username` FROM ".$GLOBALS['strTableUserName'].
				" WHERE (supplier = 1)";
$SelectSupplierResult = mysql_query($strMysqlQuery) or die(mysql_error());
while ($arrSupplierData = mysql_fetch_array($SelectSupplierResult))
{
	$strMysqlQuery =  "UPDATE ".$GLOBALS['strTableRequestName'].
					" SET `SupplierID`=".$arrSupplierData['id']." ".
					" WHERE (supplier = '".$arrSupplierData['username']."')";
	mysql_query($strMysqlQuery) or die(mysql_error());
}
?>