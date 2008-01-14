<html> 
<head> 
<title>Upload file using PHP script</title> 
</head> 
<body> 
<form enctype="multipart/form-data" action="file:///C|/www/upload.php" method="post"> 
<input type="hidden" name="MAX_FILE_SIZE" value="1000000"> File: 
<input name="userfile" type="file"> 
<input type="submit" value="Upload"> 
</form> 
</body>
</html> 