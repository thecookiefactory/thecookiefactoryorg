<div style='margin-top: 20px; margin-bottom: 20px;'>

<?php

checkembed();
include "analyticstracking.php";

if (isset($_POST["submit"])) {

$name = mysql_real_escape_string($_POST["username"]);
$password = md5($_POST["password"]);
$email = mysql_real_escape_string($_POST["email"]);

$cq = mysql_query("SELECT * FROM users WHERE name='$name'");

if (mysql_num_rows($cq) == 0) {

if(filter_var($email, FILTER_VALIDATE_EMAIL)) {

$query = mysql_query("INSERT INTO users VALUES('','$name','$password','$email','0')");
echo "<p>Succesfully registered4</p>";

} else {
echo "not a valid email";
}

} else {
echo "this user is already registered";
}

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