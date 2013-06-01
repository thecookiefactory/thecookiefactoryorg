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

echo "<h1>manage forums</h1>";

if (isset($_GET["action"]) && ($_GET["action"] == "edit" || $_GET["action"] == "delete" || $_GET["action"] == "write") && (isset($_GET["id"]) && is_numeric($_GET["id"]))) {

    $id = strip($_GET["id"]);

    if ($_GET["action"] == "edit") {
    
        echo "edit".$_GET["id"];
        
        
    
    } else if ($_GET["action"] == "delete") {
    
        if (isset($_POST["delete"])) {
            
            mysqli_query($con, "DELETE FROM `forumposts` WHERE `tid` = ".$id);
            mysqli_query($con, "DELETE FROM `forums` WHERE `id` = ".$id);
            
            echo "DELETED!!";
            
        } else {
            
            echo "Delete thread id ".$id."?";
            echo "<form action='?action=delete&amp;id=".$id."' method='post'>
            <input type='submit' name='delete' value='Yes, delete'> or <a href='maps.php'>maps admin panel</a> - <a href='../index.php?p=maps'>maps page</a>
            </form>";
            
            }
    
    }

} else {

    $query = mysqli_query($con, "SELECT * FROM `forums` ORDER BY `id` DESC") or die(mysqli_error($con));

    echo "<table style='border-spacing: 5px;'>";
    echo "<tr><th>maps</th><th>editing tools</th></tr>";

    while ($row = mysqli_fetch_assoc($query)) {
        
        echo "<tr>";
        echo "<td>";
        echo "#".$row["id"]." - ".$row["title"]." - ".getname($row["authorid"]);
        echo "</td>";
        echo "<td>";
        echo "<a href='?action=edit&amp;id=".$row["id"]."'>edit</a> <a href='?action=delete&amp;id=".$row["id"]."'>delete</a>";
        echo "</td>";
        echo "</tr>";

    }

    echo "</table>";

}
?>

</body>
</html>