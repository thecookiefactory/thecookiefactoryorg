<?php

session_start();

$r_c = 41;
require "../inc/functions.php";

if (!checkadmin()) die("403");

?>

<!doctype html>
<html>
<head>
    <meta http-equiv='Content-Type' content='text/html;charset=UTF-8'>
    <style type='text/css'>
        .admin-name {
            color: #ff11dd;
        }
    </style>
</head>
<body>

<h1>a list of all the registered users</h1>

<?php

$q = mysqli_query($con, "SELECT `id` FROM `users`");

while ($r = mysqli_fetch_assoc($q)) {

    echo getname($r["id"])."<br>";

}

?>

<a href='index.php'> &lt;&lt; back to the main page</a>
</body>
</html>
