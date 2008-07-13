 	<?php
include "../global_config.php";
include "../".$strIncDir."sendmail/mail.php";
include "../global_dbconnect.php";

/////////////////////////////////
function DisableSupplier($SupplierName)	//	disable supplier with a given $supplierID 
{
	$strMysqlQuery = "UPDATE ".$GLOBALS['strTableUserName'].
					" SET supplier = 0 WHERE (`username`='$SupplierName')";
	echo $strMysqlQuery."<br />";
	mysql_query($strMysqlQuery) or die(mysql_error());
}

/////////////////////////////////////////////
function PassRequest($arrRequestData)	//	Pass request
{
	//	Get current supplier data
	$strMysqlQuery = "SELECT * FROM ".$GLOBALS['strTableUserName'].
					" WHERE (`username`='".$arrRequestData['supplier']."')";
	echo $strMysqlQuery."<br />";
	$arrSupplierData = mysql_fetch_array(mysql_query($strMysqlQuery)) or die(mysql_error());
	
	
	//	Get current requester data
	$strMysqlQuery = "SELECT * FROM ".$GLOBALS['strTableUserName'].
					" WHERE (`username`='".$arrRequestData['requester']."')";
	echo $strMysqlQuery."<br />";
	$arrRequesterData = mysql_fetch_array(mysql_query($strMysqlQuery)) or die(mysql_error());
	
	//	Get the previous supplier and put to array $arrPreviousSuppliers
	parse_str($arrRequestData['previous_suppliers']);
	
	//	Search for new supplier
	$strMysqlQuery = "SELECT `username`,`id` FROM ".$GLOBALS['strTableUserName'].
					" WHERE (`supplier`=1) AND (`user_level`>0) AND (`username`!='".$arrRequesterData['username']."')";		//	search all active supplier
	
	if (isset($arrPreviousSuppliers))
	foreach ($arrPreviousSuppliers as $PreviousSupplier)
	{
		$strMysqlQuery .= "AND (`username`!='".$PreviousSupplier."') ";	//	all previous supplier
	}
	if ($GLOBALS['cross_field_request']==false)
	{
		 $strMyQuery .= "AND (`field`='".$arrRequestData['field']."')";
	}
	$strMysqlQuery .= "ORDER BY last_assigned_request ASC, request_handle_number ASC, request_pending_number ASC";
	echo $strMysqlQuery."<br />";
	$SelectSupplierResult = mysql_query($strMysqlQuery) or die(mysql_error());
	
	if ($arrNewSupplierData = mysql_fetch_array($SelectSupplierResult))	// found new supplier
	{
		//	update request's data
		$today = date('Y-m-d');
		$strPreviousSuppliers = $arrRequestData['previous_suppliers'].'$arrPreviousSuppliers[]='.$arrSupplierData['username']."&";
		$strMysqlQuery = "UPDATE ".$GLOBALS['strTableRequestName'].
						" SET `supplier`='".$arrNewSupplierData['username'].
						"', `status`=status+1, `previous_suppliers`='$strPreviousSuppliers',`date_assigned`='$today' ".
						" WHERE (`id`='".$arrRequestData['id']."')";
		echo $strMysqlQuery."<br />";
		mysql_query($strMysqlQuery) or die(mysql_error());
		
		//	update previous supplier's data
		$strMysqlQuery = "UPDATE ".$GLOBALS['strTableUserName'].
						" SET `request_pending_number`=request_pending_number -1".
						" WHERE (`username`='".$arrRequestData['supplier']."')";
		echo $strMysqlQuery."<br />";
		mysql_query($strMysqlQuery) or die(mysql_error());
		
		//	update current suplier's data
		$last_assigned_request = date('YmdHis');
		$strMysqlQuery = "UPDATE ".$GLOBALS['strTableUserName'].
						 " SET `request_pending_number` = `request_pending_number` +1, `last_assigned_request` = '$last_assigned_request' ".
						 " WHERE  (user_level='1') AND username = '".$arrNewSupplierData['username']."'";
		echo $strMysqlQuery;
		mysql_query($strMysqlQuery) or die(mysql_error());
	}
	else
	{
		$strMysqlQuery = "UPDATE $strTableRequestName SET status = -2 WHERE id=".$arrRequestData['id'];
		echo $strMysqlQuery."<br />";
		mysql_query($strMysqlQuery) or die(mysql_error());		
	}
}

///////////////	Main	//////////////////// 
//Get current date
$today = date('Y-m-d');

//	Get all requests which are config.php/$GLOBALS['DisableSupplierThreshold'] days late
$strMysqlQuery = "SELECT `supplier` FROM ".$GLOBALS['strTableRequestName'].
				" WHERE DATEDIFF('$today', `date_assigned`)>=".$GLOBALS['DisableSupplierThreshold'];
echo $strMysqlQuery."<br />";
$SelectLateRequestResult = mysql_query($strMysqlQuery) or die(mysql_error());
//	Get list of all late suppliers and disable them
if (mysql_num_rows($SelectLateRequestResult)>0)
{
	while ($arrLateRequestData=mysql_fetch_array($SelectLateRequestResult))
	{
		$arrLateSuppliers[]=$arrLateRequestData['supplier'];
		DisableSupplier($arrLateRequestData['supplier']);
	}
	//	Redistribute waiting papers
	foreach ($arrLateSuppliers as $LateSupplier)
	{
		$strMysqlQuery = "SELECT * FROM ".$GLOBALS['strTableRequestName'].
						" WHERE (`supplier`= '$LateSupplier') AND (status>-1)";
		echo $strMysqlQuery."<br />";
		$SelectRequestResult = mysql_query($strMysqlQuery) or die(mysql_error());		//	Get list of requests for current LateSupplier
		
		while ($arrRequestData = mysql_fetch_array($SelectRequestResult))
		{	
			echo ("<br />______________<br />Passing Request ".$arrRequestData['id']."<br />");
			PassRequest($arrRequestData);
		}
	}
}
else
{
	echo "Khong co yeu cau muon ".$GLOBALS['DisableSupplierThreshold']." ngay.";
}
?>