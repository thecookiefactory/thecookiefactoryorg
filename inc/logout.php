<?php
checkembed($r_c);
include "analyticstracking.php";

if (checkuser()) {
    
    logout();
    
} else {

    echo "You are not even logged in you baddie";
    
}
?>