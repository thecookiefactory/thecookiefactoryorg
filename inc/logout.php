<?php

if (checkuser()) {
session_destroy();
echo "logged out successfully";
} else {
echo "you are not even logged in you baddie";
}
?>