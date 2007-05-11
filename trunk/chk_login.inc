<?php
	session_start();
	if (!isset($_SESSION["username"])) { $_SESSION["username"] = "";}
	if (!isset($_SESSION["password"])) {$_SESSION["password"] = "";}
function logged_in()
{
	return (($_SESSION["username"] !== "")&&($_SESSION["password"]!==""));
}

?>