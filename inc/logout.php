<?php
checkembed($r_c);
include "analyticstracking.php";

if (checkuser()) {
	session_destroy();
	if (isset($_COOKIE["userid"])) {
		setcookie("userid", "", time()-3600);
	}
	echo "logged out successfully";
	$redirect = true;
} else {
	echo "you are not even logged in you baddie";
}
?>