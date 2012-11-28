<?php

session_start();
$r_c = 42;
require "../inc/essential.php";

if (!checkadmin()) die("403");

?>

<!doctype html>
<html>
<head>
    <meta http-equiv='Content-Type' content='text/html;charset=UTF-8'>
</head>
<body>

<?php

if (isset($_GET["id"]) && is_numeric($_GET["id"])) {

    $id = strip($_GET["id"]);
    $eq = mysqli_query($con, "SELECT * FROM `newscomments` WHERE `id`=".$id);
    
    if (mysqli_num_rows($eq) == 1) {
    
        $er = mysqli_fetch_assoc($eq);
        
        if (isset($_POST["delete"])) {
            
            $dq = mysqli_query($con, "DELETE FROM `newscomments` WHERE `id`=".$id);
            echo "The comment is successfully deleted.<br>";
            echo "<a href='../index.php?p=news'>Go back to the news page</a>";
        
        } else {
            
            echo "Delete comment id ".$id."(".$er["text"].")?";
            echo "<form action='?action=delete&amp;id=".$id."' method='post'>
            <input type='submit' name='delete' value='Yes, delete'> or just <a href='../index.php?p=news'>go back to the news page</a>
            </form>";
        
        }
    
    } else {
        
        echo "The specified id returned no comments.<br>";
        echo "<a href='../index.php?p=news'>Go back to the news page</a>";
    }

} else {
    
    echo "There was no id defined.<br>";
    echo "<a href='../index.php?p=news'>Go back to the news page</a>";

}

?>

</body>
</html>