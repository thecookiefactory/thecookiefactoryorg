<div style='margin-top: 20px; margin-bottom: 20px;'>

<?php

if ($r_c != 42)
die("This site must be embedded to use.");

if (isset($_POST["submit"])) {

$name = mysql_real_escape_string($_POST["username"]);
$password = md5($_POST["password"]);
$email = mysql_real_escape_string($_POST["email"]);

$query = mysql_query("INSERT INTO users VALUES('','$name','$password','$email','0')");
echo "<p>Succesfully registered4</p>";

} else {

echo "<form action='?p=register' method='post'>
<input type='text' placeholder='username' name='username' required='required' /><br />
<input type='password' placeholder='password' name='password' required='required' /><br />
<input type='text' placeholder='e-mail' name='email' required='required' /><br />
<input type='submit' value='Register' name='submit' />
</form>";

}

?>

</div>