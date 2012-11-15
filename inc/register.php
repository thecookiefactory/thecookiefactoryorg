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

echo "<div class='register-form'><form action='?p=register' method='post'>
<span class='register-text'>Hey there! My name is </span>
<input class='register-input' type='text' placeholder='username' name='username' required='required' autofocus />
<span class='register-text'>, and you will know it really is me when I tell my secret password, which is </span>
<input class='register-input' type='password' placeholder='password' name='password' required='required' />
<span class='register-text'>. If you want to contact me, feel free to do it at my email address, </span>
<input class='register-input' type='email' placeholder='e-mail' name='email' required='required'>
<span class='register-text'>(I know I won't be getting any spam from you). I guess that's pretty much all I need to say about myself, so </span>
<input class='register-input register-button' type='submit' value='just get me in already!' name='submit'>
</form></div>";

	}

}

?>

</div>