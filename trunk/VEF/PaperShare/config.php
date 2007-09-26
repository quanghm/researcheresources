
<?php

/////// Config database   //////////

$strDatabaseHost = 'localhost';			

$strAdmin = 'vef';

$strAdminPass = 'test123';

$strDatabaseName = 'papershare';

$strTableUserName = 'tbl_user';

$strTableRequestName = 'tbl_request';

$strTableAdmin = 'tbl_update';

////// Config site's options////////
	
	$strWebsiteName="Article Resource";

	$strAdminEmail="admin@localhost";
	////////////////////////////////

	define("constMinLength", 6);
	define("constMaxLength", 15);
		//////// Allowed Characters in usernames
	$AllowedChars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789âăáấắạậặãẫẵàầằảẩẳôơóốớọộợõỗỡòồờỏổởêéếèềẹệẽễẻểíìỉĩịưúửùừủửũữụựýỳỷỹỵÂĂÁẤẮẠẬẶÃẪẴÀẦẰẢẨẲÔƠÓỐỚỌỘỢÕỖỠÒỒỜỎỔỞÊÉẾÈỀẸỆẼỄẺỂÍÌỈĨỊƯÚỬÙỪỦỬŨỮỤỰÝỲỶỸỴ_ ";

	

	///// Array of Fields //////

	$arrFieldList[]='Mathematics';
	
	$arrFieldList[]='Physics';
		
	$arrFieldList[]='Chemistry';

	$arrFieldList[]='Biology';

	$arrFieldList[]='Computer Science';

	$arrFieldList[]='Economics';

	$arrFieldList[]='Civil Engineering';

	$arrFieldList[]='Chemical Engineering';

	$arrFieldList[]='Electrical Engineering';

	$arrFieldList[]='Environmental Engineering';

	$arrFieldList[]='Industrial Engineering';

	$arrFieldList[]='Materials Engineering';

	$arrFieldList[]='Mechanical Engineering';

	$arrFieldList[]='Engineering - Other';

	///

	$NumberOfField=count($arrFieldList);

	// maximum times to pass an order.

	// this is used for status of a request.

	$max_pass = 2;		

	//  array of request status					

	// status = -1 => finished

	// status = -2 => failed

	////////////////////////////////

	$store_article_on_server = false;

	$archive_request = true;

	$suppliers_per_request = 1;

	///////////////////////////

	$cross_field_request = false;

?>