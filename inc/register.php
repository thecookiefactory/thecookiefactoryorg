<div style='margin-top: 20px; margin-bottom: 20px;'>

<?php

checkembed($r_c);
include "analyticstracking.php";

if (checkuser()) {
	
	echo "<p>You are already logged in! Click <a href='?p=logout'>here</a> if you want to log out.</p>";

} else {

	if (isset($_POST["submit"])) {

		$username = $_POST["username"];
		$password = $_POST["password"];
		$email = $_POST["email"];

		register($username, $password, $email);

	}

	if (!isset($redirect)) {

		echo "<form action='?p=register' method='post'>
		<input type='text' placeholder='username' name='username' required='required' autofocus /><br />
		<input type='password' placeholder='password' name='password' required='required' /><br />
		<input type='email' placeholder='e-mail' name='email' required='required' /><br />
		<input type='submit' value='Register' name='submit' />
		</form>";

	}

}


?>

</div>