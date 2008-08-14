<?php

	include("config.php");
	include("dbconnect.php");
	
	////////////////////////////////////////////////////
	/////   Function Check Username's existance    /////
	////////////////////////////////////////////////////
	for ($i=1; $i<10; $i++)
	{
		$today = date("Y-m-d");
		$username = "testacc$i";
		$encoded_password = crypt('testing');
		$email = "testacc$i@yahoo.com";
		$field = "Mathematics";
		$pending_number = 30-$i;
		$strInsertQuery = "INSERT INTO $strTableUserName (username, password, email, field, supplier,join_date,request_handle_number,request_pending_number) VALUES ('$username', '$encoded_password', '$email', '$field',1,'$today',".$i.",".$pending_number.")";
		echo $strInsertQuery;
		mysql_query($strInsertQuery) or die(mysql_error());
	
	}
	include "dbclose.php";
	echo "<script language=\"javascript\">	window.location = \"index.php\"; </script>"; 
?>