<?php

if (!isset($r_c)) header("Location: notfound.php");

include "analyticstracking.php";

if (checkuser()) {

    ?>

    <p>You are already logged in! <a href='?p=logout'>Log out</a>?</p>

    <?php

} else {

    if (isset($_SESSION["steamid"])) {

        if (isset($_POST["submit"])) {

            $username = $_POST["username"];

            register($username);

        }

        ?>

        <div class='account-form'><form action='?p=register' method='post'>
        <span class='account-text'><span class='account-title'>Hey there!</span><br>My name is </span>
        <input class='account-input' pattern='\w{2,10}' type='text' placeholder='username' name='username' required='required' autocomplete='off' oninput='checkInputBox(this);' autofocus>
        <input class='account-input account-button' type='submit' value='and I am ready to roll!' name='submit'>
        </form></div>

        <?php

    } else {

        echo "In order to register you need to log in through Steam first. Sorry, we tried our best!";

    }

}
