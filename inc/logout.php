<?php
checkembed();
include "analyticstracking.php";

if (checkuser()) {
	session_destroy();
	if (isset($_COOKIE["username"])) {
		setcookie("username", "", time()-3600);
	}
	echo "logged out successfully";
	echo "<script type='text/javascript'>
	<!--
	window.location = '?p=news'
	//-->
	</script>";
} else {
	echo "you are not even logged in you baddie";
}
?>