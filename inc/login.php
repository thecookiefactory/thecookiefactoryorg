<div style='margin-top: 20px; margin-bottom: 20px;'>

<?php

checkembed($r_c);
include "analyticstracking.php";

if (checkuser()) {
    
    echo "<p>You are already logged in! Click <a href='?p=logout'>here</a> if you want to log out.</p>";

} else {

    if (isset($_POST["submit"])) {
        
        $username = $_POST["username"];
        $password = $_POST["password"];
        
        login($username, $password);
        
    }

    if (!isset($redirect)) {
        
        echo "<form action='?p=login' method='post' name='login'>
        <input type='text' pattern='.{2,10}' name='username' placeholder='username' required='required' autofocus /><br />
        <input type='password' pattern='.{6,30}' name='password' placeholder='password' required='required' /><br />
        <input type='checkbox' name='remember'> remember me<br>
        <input type='submit' name='submit' value='Log in' /> or <a href='?p=register'>register</a>
        </form>";
        
    }
    
}



?>
</div>