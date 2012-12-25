<?php
checkembed($r_c);

include "analyticstracking.php";

$action = isset($_GET["action"]) ? strip($_GET["action"]) : "";
if ($action == "add" && checkuser()) {

    echo "add a new thread";
    
    if (isset($_POST["addnew"])) {
        
        $authorid = $_SESSION["userid"];
        $cat = strip($_POST["cat"]);
        $title = strip($_POST["title"]);
        $text = strip($_POST["text"]);
        $dt = time();
        
        mysqli_query($con, "INSERT INTO `forums` VALUES('','".$authorid."','".$dt."','".$title."','".$text."','".$cat."','0','".$dt."')");
        echo "added.";
        
    } else {
    
        echo "<form action='?p=forums&amp;action=add' method='post'>";
        echo "<input type='text' name='title' required>";
        echo "<textarea name='text' required></textarea>";
        echo "select cateryogy";
        echo "<select name='cat'>";
        $cq = mysqli_query($con, "SELECT * FROM `forumcat` ORDER BY `name` ASC");
        while ($cr = mysqli_fetch_assoc($cq)) {
            echo "<option value='".$cr["id"]."'>".$cr["name"]."</option>";
        }
        echo "</select>";
        echo "<input type='submit' name='addnew'>";
        echo "</form>";
    
    }

} else {
    
    if (isset($_GET["id"]) && is_numeric($_GET["id"])) {

        $query = mysqli_query($con, "SELECT * FROM `forums` WHERE `id`=".$_GET["id"]);
        $row = mysqli_fetch_assoc($query);
        echo "<br>".getcatname($row["cat"])." <a href='?p=forums&id=".$row["id"]."'>".$row["title"]."</a> ".getname($row["authorid"])." ".displaydate($row["dt"])." ".(($row["closed"] == 1) ? "closed" : "")." ";
        echo "created ".longago($row["dt"])." ";
        echo "last post at ".longago($row["ldt"]);
        echo "<br>".nl2br($row["text"]);
        
        if (isset($_POST["cp"]) && trim($_POST["text"]) != "") {
                
            $tid = strip($_GET["id"]);
            $author = $_SESSION["userid"];
            $text = strip($_POST["text"]);
            $dt = time();
                
            $iq = mysqli_query($con, "INSERT INTO `forumposts` VALUES('','$author','$text','$dt','0','$tid')");
            
            $uq = mysqli_query($con, "UPDATE `forums` SET `ldt`=".$dt." WHERE `id`=".$row["id"]);
        
        }
        
        if ($row["closed"] == 0) {
        
            //fetching comments
            $cq = mysqli_query($con, "SELECT * FROM `forumposts` WHERE `tid`=".$_GET["id"]);
            while ($cr = mysqli_fetch_assoc($cq)) {
                echo "<br>".getname($row["authorid"])." ".longago($cr["dt"]).nl2br($cr["text"]);
            }
        
            if (checkuser()) {  
                echo "<hr><h1 class='comments-title'>Reply to this thread</h1>"; 
                echo "[bbcode buttons]";
                echo "<div id='comment-form'><form action='?p=forums&amp;id=".$_GET["id"]."' method='post'>
                <textarea name='text' id='comment-textarea' required></textarea>
                <input type='submit' name='cp' value='&gt;' id='comment-submitbutton'>
                </form></div>";
            } else {
                echo "<hr><h1 class='comments-title'>Log in to be able to post replies</h1><div class='clearfix'></div>";
            }
        
        } else {
            echo "closed thread";
        }

    } else {

        if (checkuser()) echo "<a href='?p=forums&amp;action=add'>Create a new thread</a>";
        
        $query = mysqli_query($con, "SELECT * FROM `forums` ORDER BY `ldt` DESC");
        
        while ($row = mysqli_fetch_assoc($query)) {
            echo "<br>".getcatname($row["cat"])." <a href='?p=forums&id=".$row["id"]."'>".$row["title"]."</a> ".getname($row["authorid"])." ".displaydate($row["dt"])." ".(($row["closed"] == 1) ? "closed" : "")." ";
            echo "created ".longago($row["dt"])." ";
            echo "last post at ".longago($row["ldt"]);
            
            echo mysqli_num_rows(mysqli_query($con, "SELECT `id` FROM `forumposts` WHERE `tid`=".$row["id"]))." replies";
        }

    }
}

function getcatname($x) {

    global $con;

    $fq = mysqli_query($con, "SELECT `name` FROM `forumcat` WHERE `id`=".$x);
    $fr = mysqli_fetch_assoc($fq);

    return $fr["name"];

}

?>
