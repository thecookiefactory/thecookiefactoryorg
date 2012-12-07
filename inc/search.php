<?php

checkembed($r_c);
include "analyticstracking.php";
echo "<div class='search-border-upper'></div>";
echo "<div class='search-border-lower'></div>";
if (isset($_POST["searchb"])) {

    $term = strip($_POST["searchb"]);
    
    if (strlen($term) >= 3) {

    $squery = mysqli_query($con, "SELECT * FROM `news` WHERE `text` LIKE '%".$term."%' or `title` LIKE '%".$term."%' ORDER BY `id` DESC");
    $nr = mysqli_num_rows($squery);

    

    if ($nr == 0) {
        if (strlen($term) > 23) {
            echo "<div class='search-title'>No results found for your search term</div>";
        } else {
            echo "<div class='search-title'>No results found for <span class='search-term'>".$term."</span></div>";
        }
        

    } else {
            
        if (strlen($term) > 23) {
            if ($nr == 1) {
                echo "<div class='search-title'>".$nr." result found for your search term</div>";
            } else {
                echo "<div class='search-title'>".$nr." results found for your search term</div>";
            }
        } else {
            if ($nr == 1) {
                echo "<div class='search-title'>".$nr." result found for <span class='search-term'>".$term."</span></div>";
            } else {
                echo "<div class='search-title'>".$nr." results found for <span class='search-term'>".$term."</span></div>";
            }
        }

        echo "<div class='search-results'>";

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

            echo "<span class='article-metadata-item'><span class='article-author'>".getname($srow["authorid"])."</span></span><span class='article-metadata-item'><span class='article-date'>".displaydate($srow["dt"])."</span></span></div>";
            
            //if edited
            if ($srow["editorid"] > 0) {
                echo "<div class='article-edit-metadata'><span class='article-metadata-item'><span class='article-author'>".getname($srow["editorid"])."</span></span><span class='article-metadata-item'><span class='article-date'>".displaydate($srow["editdt"])."</span></span></div>";
            }
            
            echo "</div>";        

            // BODY
            echo "<article>
            <span class='article-text'>".substr($srow["text"], 0, 100)."</span>
            </article>
            <hr class='article-separator'>";
        }

        echo "</div>";

        }

    } else {
    echo "<div class='search-title'>Please enter a keyword longer than 2 characters.</div>";
    }

} else {
    
    echo "<div class='search-title'>No keyword defined.</div>";

} 

?>