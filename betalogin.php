<?php
session_start();

$r_c = 42;
require "inc/essential.php";

if (isset($_POST["beta"])) {
    
    $key = strip($_POST["beta"]);
    $q = mysqli_query($con, "SELECT * FROM `betatest` WHERE `key`='".$key."'");

    if (mysqli_num_rows($q) == 1) {

        echo "<a href='index.php?p=news'>welcome</a>";
        $_SESSION["beta"] = $key;

    }

}

?>
<form action='betalogin.php' method='post'>
<input type='text' placeholder='betatest login key' name='beta'>
<input type='submit'>
</form>