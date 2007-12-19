<?php
include "config.php";
include "dbconnect.php";
//	Get supplier list
$strMysqlQuery = 	"SELECT * FROM $strTableUserName WHERE (supplier = 1)";
$result = mysql_query($strMysqlQuery) or die(mysql_error());

/*$strMysqlQuery = "UPDATE $strTableRequestName SET status=-1 WHERE (field = 'Physics') AND (status>-1) AND (date_request<'2007-12-18')";
echo "$strMysqlQuery</br>";
mysql_query($strMysqlQuery) or die(mysql_error());*/

while ($arrSupplierData=mysql_fetch_array($result))
{
	$strMysqlQuery =	"SELECT * FROM $strTableRequestName WHERE (supplier='" .
						$arrSupplierData['username']."') AND (status >-1)";
	//echo $strMysqlQuery.'<br/>';
	$result1 = mysql_query($strMysqlQuery) or die(mysql_error());
	$pending_request = mysql_num_rows($result1);
	
	$strMysqlQuery ="SELECT * FROM $strTableRequestName " .
					"WHERE (supplier='".$arrSupplierData['username']."')" .
					" AND (status=-1)";
	$result1 = mysql_query($strMysqlQuery) or die(mysql_error());
	$handled_request = mysql_num_rows($result1);
	
	$strMysqlQuery =	"UPDATE $strTableUserName " .
						"SET request_pending_number = " .$pending_request.
						", request_handle_number = " .$handled_request.
						" WHERE username='".$arrSupplierData['username']."'";
	echo $strMysqlQuery."<br/>";					
	mysql_query($strMysqlQuery) or die(mysql_error());
}
include "dbclose.php"
?>