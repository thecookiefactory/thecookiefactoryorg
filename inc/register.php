<?php

checkembed($r_c);
include "analyticstracking.php";

if (checkuser()) {
    
    ?>
    
    <p>You are already logged in! <a href='?p=logout'>Log out</a>?</p>
    
    <?php

} else {

    if (isset($_POST["submit"])) {

        $username = $_POST["username"];
        $password = $_POST["password"];
        $email = $_POST["email"];

        register($username, $password, $email);

    }

    if (!isset($redirect)) {
    
        ?>
        
        <div class='account-form'><form action='?p=register' method='post'>
        <span class='account-text'><span class='account-title'>Hey there!</span><br>My name is </span>
        <input class='account-input' pattern='\w{2,10}' type='text' placeholder='username' name='username' required='required' autocomplete='off' oninput='checkInputBox(this, true);' autofocus><span class='account-text'>, and you will know it really is me when I tell you my secret password, which is </span>
        <input class='account-input' pattern='.{6,30}' type='password' placeholder='password' name='password' required='required' autocomplete='off' oninput='checkInputBox(this, true)'><span class='account-text'>. Should you ever want to contact me, feel free to do so at my email address, </span>
        <input class='account-input' type='email' placeholder='email' name='email' required='required' autocomplete='off' oninput='checkInputBox(this, true);'>
        <span class='account-text'>(I know I won't be getting any spam from you). I guess that's pretty much all I need to say about myself, so </span>
        <input class='account-input account-button' type='submit' value='just get me in already!' name='submit'>
        </form></div>
        
        <?php

    }
}
