<?php
$r_c = 42;
require "essential.php";
$query = mysqli_query($con, "SELECT * FROM `users` WHERE `name`='".$_GET["checkuser"]."'");
echo mysqli_num_rows($query);
?>
