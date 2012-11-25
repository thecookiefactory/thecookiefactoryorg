<?php

checkembed($r_c);

include "analyticstracking.php";

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
// DISPLAY ALL THE NEWS

    if (!isset($_GET["page"]) || !is_numeric($_GET["page"]) || $_GET["page"] < 1)
    $page = 1;
    else
    $page = $_GET["page"];
    
    $xo = ($page - 1) * 5;
    $yo = $page * 5;
    $query = mysqli_query($con, "SELECT * FROM `news` ORDER BY `id` DESC LIMIT ".$xo.", ".$yo);
    
    if (mysqli_num_rows($query) == 0) {
        echo "No news posts found.";
    } else {
        while ($row = mysqli_fetch_assoc($query)) {
    
        // TITLE, AUTHOR & DATE
        echo "<div class='article-header'>
        <div class='article-title'><h1><a href='?p=news&amp;id=".$row["id"]."'>".$row["title"]."</a></h1></div>
        <div class='article-metadata'>";
        
        if ($row["comments"] == 1) {
        
            $cq = mysqli_query($con, "SELECT `id` FROM `newscomments` WHERE `newsid`=".$row["id"]);
            $commnum = mysqli_num_rows($cq);
            echo "<span class='article-metadata-item'><a href='?p=news&amp;id=".$row["id"]."#comments'>".$commnum." comments</a></span>";
            }

        echo "<span class='article-metadata-item'><span class='article-author'>".getname($row["authorid"])."</span></span><span class='article-metadata-item'><span class='article-date'>".$row["date"]."</span></span></div>
        </div>";        

        // BODY
        echo "<article>
        <span class='article-text'>".nl2br($row["text"], false)."</span>
        </article>
        <hr class='article-separator'>";

    }
    }
    
    //page links
    $nr = mysqli_num_rows(mysqli_query($con, "SELECT * FROM `news`"));
    for ($i = 1; $i <= $nr%5; $i++) {
    if ($page == $i)
    echo "Page ".$i;
    else
    echo "<a href='?p=news&amp;page=".$i."'>Page ".$i."</a>";
    
    }
} else {
// DISPLAY ONE PIECE OF NEWS

    $query = mysqli_query($con, "SELECT * FROM `news` WHERE `id`=".$_GET["id"]);
    
    if (mysqli_num_rows($query) == 1) {
    
        $row = mysqli_fetch_assoc($query);
    
        echo "<div class='article-header'>
        <div class='article-title'><h1>".$row["title"]."</h1></div><div class='article-metadata'>";
        
        echo "<span class='article-metadata-item'><span class='article-author'>".getname($row["authorid"])."</span></span><span class='article-metadata-item'><span class='article-date'>".$row["date"]."</span></span></div>
        </div>";
        echo "<article>
        <span class='article-text'>".nl2br($row["text"], false)."</span>
        </article>";

        if ($row["comments"] == 1) {
            
            if (isset($_POST["cp"]) && trim($_POST["text"]) != "") {
                
                $newsid = $_GET["id"];
                $author = $_SESSION["userid"];
                $text = mysqli_real_escape_string($con, htmlentities($_POST["text"]));
                $date = date("Y-m-d");
                $time = date("H:i", time());
                
                $iq = mysqli_query($con, "INSERT INTO `newscomments` VALUES('','$author','$text','$date','$time','$newsid')");
                
                }
            
                $cq = mysqli_query($con, "SELECT * FROM `newscomments` WHERE `newsid`=".$row["id"]." ORDER BY id ASC");
                $commnum = mysqli_num_rows($cq);
                if ($commnum > 0) {
                    echo "<hr><div id='comments'><a href='?p=news&amp;id=".$row["id"]."#comments' class='comments-title'>".$commnum." comments</a></div><br>";
            
                    if (checkadmin()) {
                        while ($crow = mysqli_fetch_assoc($cq)) {
                        echo "<div class='comment'><span class='comment-metadata'>";
                        echo "<span class='comment-author'>".getname($crow["authorid"])."</span><span class='comment-date'>".$crow["date"]."</span><span class='comment-deletebutton'><a href='admin/comments.php?id=".$crow["id"]."'>delete this</a></span>";
                        echo "</span><br><p class='comment-text'><span class='comment-text'>".nl2br($crow["text"], false)."</span></p>";
                        echo "</div>";
                    }
                    } else {
                        while ($crow = mysqli_fetch_assoc($cq)) {
                        echo "<div class='comment'><span class='comment-metadata'>";
                        echo "<span class='comment-author'>".getname($crow["authorid"])."</span><span class='comment-date'>".$crow["date"]."</span>";
                        echo "</span><br><p class='comment-text'><span class='comment-text'>".nl2br($crow["text"], false)."</span></p>";
                        echo "</div>";
                    }
                    }
                    
                }

                if (checkuser()) {  
                    echo "<hr><h1 class='comments-title'>Post a comment</h1>";      
                    echo "<div id='comment-form'><form action='?p=news&amp;id=".$_GET["id"]."' method='post'>
                    <textarea name='text' id='comment-textarea' required></textarea>
                    <input type='submit' name='cp' value='>' id='comment-submitbutton'>
                    </form></div>";
                } else {
                    echo "<hr><h1 class='comments-title'>Log in to be able to post comments</h1><div class='clearfix'></div>";
                }
            
            } else 
                echo "<hr><h1 class='comments-title'>Commenting disabled</h1><div class='clearfix'></div>";
    } else {
        echo "No.";
    }
}

?>
