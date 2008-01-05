<?php
function draw_table($type,$arrField,$sortBy,$startFrom,$urlRedirect,$strCondition)
{
	global $strTableUserName, $strTableRequestName;
	
	$strMysqlQuery = "SELECT * FROM ";
	
	if ($type=='User')
	{
		$strMysqlQuery.=("$strTableUserName " );
	}
	else
	{
		$strMysqlQuery.= "$strTableRequestName ";
	}
	if (isset($strCondition) and ($strCondition!== ''))
	{
		$strMysqlQuery .= $strCondition;
	}
	$strMysqlQuery .= "ORDER BY $sortBy";
	$strMysqlQuery .= " LIMIT $startFrom, 30";
	
	$result= mysql_query($strMysqlQuery) or die(mysql_error());

	if (mysql_num_rows($result)==0)
	{
		echo "Không tìm được hồ sơ nào";
	}
	else
	{
		echo "<table width='100%'>\r\n" .
			"	<tr height='40'>\r\n";
		foreach ($arrField as $value)
		{
			echo "		<th>$value</th>\r\n";
		}
		echo "	</tr>\r\n";
		
		$strTrClass = "odd";
		while ($arrRecordData = mysql_fetch_array($result))
		{
			$strTrClass=str_replace($strTrClass,'',"oddeven");
			echo "	<tr class='$strTrClass' height='25pt' onclick='document.getElementById(\"record".$arrRecordData['ID']."\").submit()'>\r\n";
			foreach ($arrField as $key => $value)
			{
				echo "		<td>".$arrRecordData[$key]."</td>\r\n";
			}
			echo "		<form id='record".$arrRecordData['ID']."' method='post' action='$urlRedirect'>\r\n" .
			 		 "			<input type='hidden' name='frm".$type."ID' value='".$arrRecordData['id']."'>\r\n" .
			 		 "		</form>\r\n" .
			 		 "	</tr>\r\n";
		}
		echo "</table>\r\n";
	}
}
?>
