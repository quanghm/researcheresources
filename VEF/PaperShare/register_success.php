<?php
		if (!isset($_SESSION["user"])) 
		{ $_SESSION["user"] = "";}
		else
		{
			include "config.php";
			include "dbconnect.php";
	
			////////////////////////////////////////////////////
			//////////// Select user from database /////////////			
			
			echo "Chào mừng ".$_SESSION["user"]." bạn hãy check mail để kích hoạt tài khoản vừa đăng ký.<br>";
			echo "Cảm ơn bạn đã tham gia cùng nghiencuusinh.org!";
		}
?>