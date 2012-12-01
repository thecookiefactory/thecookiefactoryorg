<?php

checkembed($r_c);
include "analyticstracking.php";

if (isset($_POST["searchb"])) {

    $term = strip($_POST["searchb"]);
    
    if (strlen($term) >= 3) {

    $squery = mysqli_query($con, "SELECT * FROM `news` WHERE `text` LIKE '%".$term."%' or `title` LIKE '%".$term."%' ORDER BY `id` DESC");
    $nr = mysqli_num_rows($squery);

    if ($nr == 0) {

        echo "<h1>No results found for ".$term.".</h1>";

    } else {

            echo "<h1>".$nr." results found for: ".$term."</h1>";
            
			while ($srow = mysqli_fetch_assoc($squery)) {
            // TITLE, AUTHOR & DATE
            echo "<div class='article-header'>
            <div class='article-title'><h1><a href='?p=news&amp;id=".$srow["id"]."'>".$srow["title"]."</a></h1></div>
            <div class='article-metadata'>";
            
            if ($srow["comments"] == 1) {
            
                $cq = mysqli_query($con, "SELECT `id` FROM `newscomments` WHERE `newsid`=".$srow["id"]);
                $commnum = mysqli_num_rows($cq);
                echo "<span class='article-metadata-item'><a href='?p=news&amp;id=".$srow["id"]."#comments'>".$commnum." comments</a></span>";
                }

            echo "<span class='article-metadata-item'><span class='article-author'>".getname($srow["authorid"])."</span></span><span class='article-metadata-item'><span class='article-date'>".$srow["date"]."</span></span></div>";
            
            //if edited
            if ($srow["edit"] == 1) {
                echo "<div class='article-edit-metadata'><span class='article-metadata-item'><span class='article-author'>".getname($srow["editorid"])."</span></span><span class='article-metadata-item'><span class='article-date'>".$srow["editdate"]."</span></span></div>";
            }
            
            echo "</div>";        

            // BODY
            echo "<article>
            <span class='article-text'>".substr($srow["text"], 0, 100)."</span>
            </article>
            <hr class='article-separator'>";
        }
    
    }
    } else {
    echo "<h1>Please enter a keyword longer than 2 characters.</h1>";
    }

} else {
    
    echo "<h1>No keyword defined.</h1>";

} 

?>