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
    
        $query = mysqli_query($con, "SELECT * FROM `forums` WHERE `id`=".$id);

        $row = mysqli_fetch_assoc($query);

        ?>
        <h1>
            <?php echo $row["title"]; ?>
        </h1>
        <?php echo (($row["closed"] == 1) ? "<div class='forums-thread-closedtext'>closed</div>" : ""); ?>
        <?php echo (($row["mapid"] != 0) ? "<a href='?p=maps#".$row["mapid"]."'>related map</a>" : ""); ?>
        
        <?php echo "#1"; ?>
        <?php echo getname($row["authorid"], true); ?>
        <?php echo displaydate($row["dt"]); ?>
        <p><?php echo tformat($row["text"]); ?></p>
        <?php

            //fetching comments
            $cq = mysqli_query($con, "SELECT * FROM `forumposts` WHERE `tid`=".$id);

            $cn = 2;

            while ($cr = mysqli_fetch_assoc($cq)) {

                ?>

                <?php echo "<hr>"; ?>
                <?php echo "#".$cn; ?>
                <?php echo getname($cr["authorid"]); ?>
                <?php echo displaydate($cr["dt"]); ?>
                <p><?php echo tformat($cr["text"]); ?></p>

                <?php
                $cn++;
            } ?>

        <?php
    
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
        echo "<a href='?action=edit&amp;id=".$row["id"]."'>edit</a>";
        if ($row["mapid"] == 0 && $row["newsid"] == 0) echo " <a href='?action=delete&amp;id=".$row["id"]."'>delete</a>";
        echo "</td>";
        echo "</tr>";

    }

    echo "</table>";

}
?>

<a href='index.php'> &lt;&lt; back to the main page</a>
</body>
</html>