<?php
//	website configuration with options as superglobal variable

//	Database infomation
$GLOBALS['strDatabaseHost'] = 'nghiencuusinh.org';
$GLOBALS['strAdmin'] = 'web675_u1';
$GLOBALS['strAdminPass'] = 'ncs2008db';
$GLOBALS['strDatabaseName'] = 'web675_db1';
$GLOBALS['strTableUserName'] = 'tbl_user_test';
$GLOBALS['strTableRequestName'] = 'tbl_request_test';
$GLOBALS['strTableAdmin'] = 'tbl_update_test';
$GLOBALS['strTableAnnouncement']='tbl_announcement';
////// Config site's options////////
	
	$GLOBALS['strWebsiteName']="Nghiencuusinh.org";

	$GLOBALS['strAdminEmail']="admin@nghiencuusinh.org";
	
	////// Include directory///////
	$GLOBALS['strIncDir'] = 'incs/';
	
	//////// Max and Min Lengths for Username and Password////////
	define("constMinLength", 6);
	define("constMaxLength", 15);
		//////// Allowed Characters in usernames
	$GLOBALS['AllowedChars'] = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789âăáấắạậặãẫẵàầằảẩẳôơóốớọộợõỗỡòồờỏổởêéếèềẹệẽễẻểíìỉĩịưúửùừủửũữụựýỳỷỹỵÂĂÁẤẮẠẬẶÃẪẴÀẦẰẢẨẲÔƠÓỐỚỌỘỢÕỖỠÒỒỜỎỔỞÊÉẾÈỀẸỆẼỄẺỂÍÌỈĨỊƯÚỬÙỪỦỬŨỮỤỰÝỲỶỸỴ_ ";

	

	///// Array of Fields //////

	$GLOBALS['arrFieldList'][]='Chọn một chuyên ngành...';
	
	$GLOBALS['arrFieldList'][]='Biology';
	
//	$GLOBALS['arrFieldList'][]='Chemical Engineering';

	$GLOBALS['arrFieldList'][]='Chemistry';
	
//	$GLOBALS['arrFieldList'][]='Civil Engineering';

	$GLOBALS['arrFieldList'][]='Computer Science';

//	$GLOBALS['arrFieldList'][]='Economics';

	$GLOBALS['arrFieldList'][]='Electrical Engineering';

//	$GLOBALS['arrFieldList'][]='Environmental Engineering';

//	$GLOBALS['arrFieldList'][]='Industrial Engineering';

	$GLOBALS['arrFieldList'][]='Mechanical Engineering';

//	$GLOBALS['arrFieldList'][]='Materials Engineering';

	$GLOBALS['arrFieldList'][]='Mathematics';
	
	$GLOBALS['arrFieldList'][]='Physics';

//	$GLOBALS['arrFieldList'][]='Engineering - Other';

	///

	$GLOBALS['NumberOfField']=count($arrFieldList);

	// maximum times to pass an order.

	// this is used for status of a request.

	$GLOBALS['max_pass'] = 2;		

	//  array of request status					

	// status = -1 => finished

	// status = -2 => failed

	////////////////////////////////

	$GLOBALS['store_article_on_server'] = false;

	$GLOBALS['archive_request'] = true;

	$GLOBALS['suppliers_per_request'] = 1;

	///////////////////////////

	$GLOBALS['cross_field_request'] = false;
	
	///////////////////////////
	$GLOBALS['WarnSupplierThreshold'] = 3;
	$GLOBALS['DisableSupplierThreshold'] = 4;

?>