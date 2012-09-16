<div style='margin-top: 20px; margin-bottom: 20px;'>

<?php

if (isset($_POST["username"]) && !isset($_SESSION["username"])) {
$username = mysql_real_escape_string(htmlentities($_POST["username"]));
$password = md5($_POST["password"]);
$q = mysql_query("SELECT * FROM users WHERE name='$username'");
$row = mysql_fetch_assoc($q);
if ($username == $row["name"] && $password == $row["password"]) {
$_SESSION["username"] = $username;
} else {
echo "username or password incorrect";
}
}

if (isset($_SESSION["username"])) {
echo "you are already logged in";
} else {
echo "<form action='?p=login' method='post'>
<input type='text' name='username' placeholder='username' /><br />
<input type='password' name='password' placeholder='password' /><br />
<input type='submit' name='submit' value='Log in' /> or <a href='?p=register'>register</a>
</form>";
}

?>
</div>