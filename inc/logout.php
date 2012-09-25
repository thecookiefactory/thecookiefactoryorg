<?php
if ($r_c != 42)
die("This site must be embedded to use.");

if (checkuser()) {
session_destroy();
echo "logged out successfully";
echo "<script type='text/javascript'>
<!--
window.location = '?p=news'
//-->
</script>";
} else {
echo "you are not even logged in you baddie";
}
?>