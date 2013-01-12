<?php

checkembed($r_c);
include "analyticstracking.php";

include "markdown.php";

$_SESSION["lp"] = "news";

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
// DISPLAY ALL THE NEWS

    if (!isset($_GET["page"]) || !is_numeric($_GET["page"]) || $_GET["page"] < 1)
        $page = 1;
    else
        $page = $_GET["page"];
    
    $xo = ($page - 1) * 5;
    $query = mysqli_query($con, "SELECT * FROM `news` ORDER BY `id` DESC LIMIT ".$xo.", 5");
    
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

        echo "<span class='article-metadata-item'><span class='article-author'>".getname($row["authorid"])."</span></span><span class='article-metadata-item'><span class='article-date'>".displaydate($row["dt"])."</span></span></div>";
        
        //if edited
        if ($row["editorid"] > 0) {
            echo "<div class='article-edit-metadata'><span class='article-metadata-item'><span class='article-author'>".getname($row["editorid"])."</span></span><span class='article-metadata-item'><span class='article-date'>".displaydate($row["editdt"])."</span></span></div>";
        }
        
        echo "</div>";        

        // BODY
        echo "<article>
        <span class='article-text'>".Markdown($row["text"])."</span>
        </article>
        <hr class='article-separator'>";

    }
    }
    
    //page links
    $nr = mysqli_num_rows(mysqli_query($con, "SELECT * FROM `news`"));
    for ($i = 1; $i <= ceil($nr / 5); $i++) {
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
        
        echo "<span class='article-metadata-item'><span class='article-author'>".getname($row["authorid"])."</span></span><span class='article-metadata-item'><span class='article-date'>".displaydate($row["dt"])."</span></span></div>";
        //if edited
        if ($row["editorid"] > 0) {
            echo "<div class='article-edit-metadata'><span class='article-metadata-item'><span class='article-author'>".getname($row["editorid"])."</span></span><span class='article-metadata-item'><span class='article-date'>".displaydate($row["editdt"])."</span></span></div>";
        }
        echo "</div><article>
        <span class='article-text'>".Markdown($row["text"])."</span>
        </article>";

        if ($row["comments"] == 1) {
            
            if (isset($_POST["cp"]) && trim($_POST["text"]) != "") {
                
                    $newsid = strip($_GET["id"]);
                    $author = $_SESSION["userid"];
                    $text = strip($_POST["text"]);
                    $dt = time();
                
                    $iq = mysqli_query($con, "INSERT INTO `newscomments` VALUES('','$author','$text','$dt','$newsid')");
                
                }
            
                $cq = mysqli_query($con, "SELECT * FROM `newscomments` WHERE `newsid`=".$row["id"]." ORDER BY id ASC");
                $commnum = mysqli_num_rows($cq);
               
               if ($commnum > 0) {
                    echo "<hr><div id='comments'><a href='?p=news&amp;id=".$row["id"]."#comments' class='comments-title'>".$commnum." comments</a></div><br>";

                        while ($crow = mysqli_fetch_assoc($cq)) {
                            echo "<div class='comment'><span class='comment-metadata'>";
                            echo "<span class='comment-author'>".getname($crow["authorid"])."</span><span class='comment-date'>".displaydate($crow["dt"])."</span>";
                            if (checkadmin()) echo "<span class='comment-deletebutton'><a href='admin/comments.php?id=".$crow["id"]."'>delete this</a></span>";
                            echo "</span><br><p class='comment-text'><span class='comment-text'>".Markdown($crow["text"])."</span></p>";
                            echo "</div>";
                        }
                    
                }

                if (checkuser()) {  
                    echo "<hr><h1 class='comments-title'>Post a comment</h1>";      
                    echo "<div id='comment-form'><form action='?p=news&amp;id=".$_GET["id"]."' method='post'>
                    <textarea name='text' id='comment-textarea' required></textarea>
                    <input type='submit' name='cp' value='&gt;' id='comment-submitbutton'>
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
