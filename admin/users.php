<?php

session_start();

$r_c = 1;
require_once "../inc/functions.php";
require_once "../inc/classes/user.class.php";

$user = new user((isset($_SESSION["userid"]) ? $_SESSION["userid"] : null));

if (!$user->isAdmin()) die("403");

?>

<!doctype html>
<html>
<head>
    <meta http-equiv='Content-Type' content='text/html;charset=UTF-8'>
    <title>thecookiefactory.org admin</title>
    <style type='text/css'>
        .admin-name {
            color: #ff11dd;
        }
    </style>
</head>
<body>

<h1>a list of all the registered users</h1>

<?php

$q = $con->query("SELECT `users`.`id` FROM `users`");

while ($r = $q->fetch()) {

    $u = new user($r["id"]);
    echo $u->getName() . "<br>";

}

?>

<a href='index.php'> &lt;&lt; back to the main page</a>
</body>
</html>
