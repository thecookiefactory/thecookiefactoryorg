<div style='margin-top: 20px; margin-bottom: 20px;'>

<?php

if ($r_c != 42)
die("This site must be embedded to use.");

if (isset($_POST["username"]) && !isset($_SESSION["username"])) {
$username = mysql_real_escape_string(htmlentities($_POST["username"]));
$password = md5($_POST["password"]);
$q = mysql_query("SELECT * FROM users WHERE name='$username'");
$row = mysql_fetch_assoc($q);
if ($username == $row["name"] && $password == $row["password"]) {
$_SESSION["username"] = $username;
echo "<p>Welcome, ".$_SESSION["username"]."</p>";
echo "<script type='text/javascript'>
<!--
window.location = '?p=news'
//-->
</script>";
} else {
echo "<p>Username or password incorrect!</p>";
}
}

if (isset($_SESSION["username"])) {
echo "<p>You are already logged in! Click <a href='?p=logout'>here</a> if you want to log out.</p>";
} else {
echo "<form action='?p=login' method='post'>
<input type='text' name='username' placeholder='username' required='required' /><br />
<input type='password' name='password' placeholder='password' required='required' /><br />
<input type='submit' name='submit' value='Log in' /> or <a href='?p=register'>register</a>
</form>";
}

?>
</div>