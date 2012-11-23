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

echo "<div class='account-form'><form action='?p=register' method='post'>
<span class='account-text'><span class='account-title'>Hey there!</span><br>My name is </span>
<input class='account-input' pattern='\w{2,10}' type='text' placeholder='username' name='username' required='required' autocomplete='off' onkeypress='return filterInput(event);' oninput='checkInputBox(this); this.setCustomValidity(&quot;&quot;);' oninvalid='this.setCustomValidity(&quot;Must be 2-10 alphanumeric characters.&quot;)' autofocus><span class='account-text'>, and you will know it really is me when I tell you my secret password, which is </span>
<input class='account-input' pattern='.{6,30}' type='password' placeholder='password' name='password' required='required' autocomplete='off' oninput='checkInputBox(this); this.setCustomValidity(&quot;&quot;);' oninvalid='this.setCustomValidity( &quot;Must be 6-30 characters.&quot;)'><span class='account-text'>. Should you ever want to contact me, feel free to do so at my email address, </span>
<input class='account-input' type='email' placeholder='email' name='email' required='required' autocomplete='off' oninput='checkInputBox(this); this.setCustomValidity(&quot;&quot;);' oninvalid='this.setCustomValidity(&quot;Must be a valid email address.&quot;)'>
<span class='account-text'>(I know I won't be getting any spam from you). I guess that's pretty much all I need to say about myself, so </span>
<input class='account-input account-button' type='submit' value='just get me in already!' name='submit'>
</form></div>";
	}
}
?>
</div>
