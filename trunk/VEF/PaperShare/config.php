
<?php

/////// Config database   //////////

$strDatabaseHost = 'localhost';			

$strAdmin = 'root';

$strAdminPass = '';

$strDatabaseName = 'papershare';

$strTableUserName = 'tbl_user';

$strTableRequestName = 'tbl_request';

$strTableAdmin = 'tbl_update';

$strTableAnnouncement='tbl_announcement';

////// Config site's options////////
	
	$strWebsiteName="localhost";

	$strAdminEmail="admin@nghiencuusinh.org";
	
	////// Include directory///////
	$strIncDir = 'incs/';
	
	//////// Max and Min Lengths for Username and Password////////
	define("constMinLength", 6);
	define("constMaxLength", 15);
		//////// Allowed Characters in usernames
	$AllowedChars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789âăáấắạậặãẫẵàầằảẩẳôơóốớọộợõỗỡòồờỏổởêéếèềẹệẽễẻểíìỉĩịưúửùừủửũữụựýỳỷỹỵÂĂÁẤẮẠẬẶÃẪẴÀẦẰẢẨẲÔƠÓỐỚỌỘỢÕỖỠÒỒỜỎỔỞÊÉẾÈỀẸỆẼỄẺỂÍÌỈĨỊƯÚỬÙỪỦỬŨỮỤỰÝỲỶỸỴ_ ";

	

	///// Array of Fields //////

	$arrFieldList[]='Chọn một chuyên ngành...';
	
	$arrFieldList[]='Biology';
	
//	$arrFieldList[]='Chemical Engineering';

	$arrFieldList[]='Chemistry';
	
//	$arrFieldList[]='Civil Engineering';

	$arrFieldList[]='Computer Science';

//	$arrFieldList[]='Economics';

	$arrFieldList[]='Electrical Engineering';

//	$arrFieldList[]='Environmental Engineering';

//	$arrFieldList[]='Industrial Engineering';

	$arrFieldList[]='Mechanical Engineering';

//	$arrFieldList[]='Materials Engineering';

	$arrFieldList[]='Mathematics';
	
	$arrFieldList[]='Physics';

//	$arrFieldList[]='Engineering - Other';

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