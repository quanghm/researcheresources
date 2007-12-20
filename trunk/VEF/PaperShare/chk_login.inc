<?php
	session_start();
	if (!isset($_SESSION["username"])) { $_SESSION["username"] = "";}
	if (!isset($_SESSION["password"])) {$_SESSION["password"] = "";}
	if (!isset($_SESSION["user_level"])) {$_SESSION["user_level"] = "";}
function logged_in()
{
	return (($_SESSION["username"] !== "")&&($_SESSION["password"]!==""));
}

?>