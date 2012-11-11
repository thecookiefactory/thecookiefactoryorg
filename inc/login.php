<div style='margin-top: 20px; margin-bottom: 20px;'>

<?php

checkembed();
include "analyticstracking.php";

if (isset($_POST["username"]) && !isset($_SESSION["username"])) {
	$username = mysql_real_escape_string(htmlentities($_POST["username"]));
	$password = md5($_POST["password"]);
	$q = mysql_query("SELECT * FROM `users` WHERE `name`='$username' AND `password`='$password'");
	if (mysql_num_rows($q) == 1) {
		$_SESSION["username"] = $username;
		if (isset($_POST["remember"]) && $_POST["remember"] == "on") {
			setcookie("username", $username, time()+3600*24*30);
		}
		echo "<p>Welcome, ".$_SESSION["username"]."</p>";
		$redirect = true;
	} else {
		echo "<p>Username or password incorrect!</p>";
	}
}
if (!isset($redirect)) {
	if (isset($_SESSION["username"])) {
		echo "<p>You are already logged in! Click <a href='?p=logout'>here</a> if you want to log out.</p>";
	} else {
		echo "<form action='?p=login' method='post' name='login'>
		<input type='text' name='username' placeholder='username' required='required' autofocus /><br />
		<input type='password' name='password' placeholder='password' required='required' /><br />
		<input type='checkbox' name='remember'> remember me<br>
		<input type='submit' name='submit' value='Log in' /> or <a href='?p=register'>register</a>
		</form>";
	}
}

?>
</div>