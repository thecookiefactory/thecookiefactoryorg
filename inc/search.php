<?php

checkembed($r_c);

include "analyticstracking.php";
include "markdown/markdown.php";

?>

<?php

if (isset($_POST["searchb"]) && strip($_POST["searchb"]) != "") {

    $term = strip($_POST["searchb"]);

    if (strlen($term) >= 3) {

        if (isset($_POST["inn"]) || !isset($_POST["inf"])) {

            $nsearch = true;
            $squery = mysqli_query($con, "SELECT * FROM `news` WHERE `text` LIKE '%".$term."%' or `title` LIKE '%".$term."%' ORDER BY `id` DESC");
            $nr = mysqli_num_rows($squery);
            $sss = ($nr == 1) ? "" : "s";

            if ($nr == 0) {

                if (strlen($term) > 23) {
                    ?>

                    <div class='search-title'>No <?php echo resultbutton(); ?>s found for your search term</div>

                    <?php
                } else {
                    ?>

                    <div class='search-title'>No <?php echo resultbutton(); ?>s found for <span class='search-term'><?php echo $term; ?></span></div>

                    <?php
                }

            } else {

                if (strlen($term) > 23) {
                    ?>

                    <div class='search-title'><?php echo $nr; ?> <?php echo resultbutton(); ?><?php echo $sss; ?> found for your search term</div>

                    <?php
                } else {
                    ?>

                    <div class='search-title'><?php echo $nr; ?> <?php echo resultbutton(); ?><?php echo $sss; ?> found for <span class='search-term'><?php echo $term; ?></span></div>

                    <?php
                }

                ?>

                <div class='search-results'>

                <?php

                while ($srow = mysqli_fetch_assoc($squery)) {
                    // TITLE, AUTHOR & DATE
                    ?>

                    <div class='article-header'>
                    <div class='article-title'><h1><a href='?p=news&amp;id=<?php echo $srow["id"]; ?>'><?php echo $srow["title"]; ?></a></h1></div>
                    <div class='article-metadata'>

                    <?php

                    if ($srow["comments"] == 1) {

                        $ct = mysqli_query($con, "SELECT `id` FROM `forums` WHERE `newsid`=".$srow["id"]);
                        $tid = mysqli_fetch_assoc($ct);
                        $tid = $tid["id"];

                        $cq = mysqli_query($con, "SELECT `id` FROM `forumposts` WHERE `tid`=".$tid);
                        $commnum = mysqli_num_rows($cq);
                        ?>

                        <span class='article-metadata-item'><a href='?p=news&amp;id=<?php echo $srow["id"]; ?>#comments'><?php echo $commnum; ?> comments</a></span>

                        <?php

                    }

                    ?>

                    <span class='article-metadata-item'><span class='article-author'><?php echo getname($srow["authorid"]); ?></span></span><span class='article-metadata-item'><span class='article-date'><?php echo displaydate($srow["dt"]); ?></span></span></div>

                    <?php

                    //if edited
                    if ($srow["editorid"] > 0) {
                        ?>

                        <div class='article-edit-metadata'><span class='article-metadata-item'><span class='article-author'><?php echo getname($srow["editorid"]); ?></span></span><span class='article-metadata-item'><span class='article-date'><?php echo displaydate($srow["editdt"]); ?></span></span></div>

                        <?php
                    }

                    ?>
                    </div>

                    <?php

                    // BODY
                    ?>

                    <article>
                    <span class='article-text'><?php echo Markdown(substr($srow["text"], 0, 100)); ?></span>
                    </article>
                    <hr class='article-separator'>

                    <?php
                }
                ?>

                </div>

                <?php
            }

        } else {

            $squery1 = mysqli_query($con, "SELECT * FROM `forums` WHERE `text` LIKE '%".$term."%' or `title` LIKE '%".$term."%' ORDER BY `id` DESC") or die(mysqli_error($con));
            $squery2 = mysqli_query($con, "SELECT * FROM `forumposts` WHERE `text` LIKE '%".$term."%' ORDER BY `id` DESC") or die(mysqli_error($con));
            $nr = mysqli_num_rows($squery1) + mysqli_num_rows($squery2);
            $sss = ($nr == 1) ? "" : "s";
            ?>
            <?php echo $nr." ".resultbutton().$sss; ?> found in the forums
            <?php

            while ($srow = mysqli_fetch_assoc($squery1)) {
                echo "<a href='?p=forums&amp;id=".$srow["id"]."'>".$srow["title"]."</a>";
            }
            while ($srow = mysqli_fetch_assoc($squery2)) {
                echo "<a href='?p=forums&amp;id=".$srow["tid"]."'>".$srow["text"]."</a>";
            }

        }

    } else {
        ?>

        <div class='search-title'>Please enter a keyword longer than 2 characters.</div>

        <?php
    }

} else {
    ?>

    <div class='search-title'>No keyword defined.</div>

    <?php
}

function resultbutton() {
    global $term;
    global $nsearch;
    $name = (isset($nsearch) && $nsearch == true) ? "inf" : "inn";
    $prettyname = ($name == "inf") ? "article" : "forum post";
    return "<form method='post' action='?p=search'><input type='hidden' value='".$term."' name='searchb'><input class='search-type' value='".$prettyname."' type='submit' name='".$name."'></form>";
}
