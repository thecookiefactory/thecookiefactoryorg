<?php

if (!isset($r_c)) header("Location: notfound.php");

include "analyticstracking.php";
include "markdown/markdown.php";

?>

<?php

if (isset($_POST["searchb"]) && vf($_POST["searchb"])) {

    $term = strip($_POST["searchb"]);

    if (strlen($term) > 50) {
        ?>

        <div class='search-title'>Please enter a keyword shorter than 50 characters.</div>

        <?php
    } else if (strlen($term) >= 3) {

        if (isset($_POST["inn"]) || ($_SESSION["lp"] != "forums" && !isset($_POST["inf"]))) {

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

            $squery1 = mysqli_query($con, "SELECT * FROM `forums` WHERE (`text` LIKE '%".$term."%' or `title` LIKE '%".$term."%') and `cat`<>0 ORDER BY `id` DESC") or die(mysqli_error($con));
            $squery2 = mysqli_query($con, "SELECT `tid` FROM `forumposts` WHERE `text` LIKE '%".$term."%' ORDER BY `id` DESC") or die(mysqli_error($con));

            $ra = array();

            while ($row = mysqli_fetch_assoc($squery1)) {

                if (!in_array($row["id"], $ra)) {

                    array_push($ra, $row["id"]);

                }

            }

            while ($row = mysqli_fetch_assoc($squery2)) {

                if (!in_array($row["tid"], $ra)) {

                    array_push($ra, $row["tid"]);

                }

            }

            if (!empty($ra)) {

                $squery = mysqli_query($con, "SELECT * FROM `forums` WHERE `cat`<>0 AND `id` IN (".implode(',', array_map('intval', $ra)).") ORDER BY `id` DESC");

                $nr = mysqli_num_rows($squery);

            } else {

                $nr = 0;

            }

            $sss = ($nr == 1) ? "" : "s";

            if ($nr == 0) {

                if (strlen($term) > 21) {
                    ?>

                    <div class='search-title'>No <?php echo resultbutton(); ?>s found for your search term</div>

                    <?php
                } else {
                    ?>

                    <div class='search-title'>No <?php echo resultbutton(); ?>s found for <span class='search-term'><?php echo $term; ?></span></div>

                    <?php
                }

            } else {

                if (strlen($term) > 21) {
                    ?>

                    <div class='search-title'><?php echo $nr." ".resultbutton().$sss; ?> found for <span class='search-term'><?php echo $term; ?></span></div>

                    <?php
                } else {
                    ?>

                    <div class='search-title'><?php echo $nr." ".resultbutton().$sss; ?> found for <span class='search-term'><?php echo $term; ?></span></div>

                    <?php
                }
            ?>

            <div class='search-results'>

                <style type='text/css' scoped>

                    <?php
                    $cq = mysqli_query($con, "SELECT * FROM `forumcat`");

                    while ($cr = mysqli_fetch_assoc($cq)) {

                        echo ".forums-category-".$cr["name"]."         {background-color: #".$cr["hex"]."; }\n";
                        echo ".forums-category-".$cr["name"].":hover   {background-color: #".$cr["hexh"]."; }\n";

                    }
                    ?>

                </style>
                <table class='forums-table'>
                        <colgroup>
                            <col class='forums-column-category'>
                            <col class='forums-column-title'>
                            <col class='forums-column-modifydate'>
                            <col class='forums-column-postcount'>
                        </colgroup>
                    <tbody>

                <?php

                while ($row = mysqli_fetch_assoc($squery)) {

                    ?>
                    <tr class='forums-entry'>
                        <td class='forums-entry-category forums-category-<?php echo getcatname($row["cat"]); ?>'>
                            <a href='?p=forums&cat=<?php echo $row["cat"]; ?>'>
                                <div class='forums-entry-category-text'>

                                    <?php echo getcatname($row["cat"]); ?>

                                </div>
                            </a>
                        </td>
                        <td class='forums-entry-main <?php echo (($row["closed"] == 1) ? "forums-entry-closed" : ""); ?>'>
                            <a class='forums-entry-title' href='?p=forums&id=<?php echo $row["id"]; ?>'>

                                <?php echo $row["title"]; ?>

                            </a>
                            <br>
                            <span class='forums-entry-metadata'>

                                created by <?php echo getname($row["authorid"])." ".displaydate($row["dt"]); ?>

                            </span>
                        </td>
                        <td class='forums-entry-modifydate'>
                            <span class='forums-entry-miniheader'>

                                <?php echo "Last reply posted"?>

                            </span>
                            <br>

                            <?php echo displaydate($row["ldt"]); ?>

                        </td>
                        <td class='forums-entry-postcount'>
                            <span class='forums-entry-miniheader'>
                                Thread has
                            </span>
                            <br>

                            <?php
                                echo mysqli_num_rows(mysqli_query($con, "SELECT `id` FROM `forumposts` WHERE `tid`=".$row["id"])).(mysqli_num_rows(mysqli_query($con, "SELECT `id` FROM `forumposts` WHERE `tid`=".$row["id"])) == 1 ? " reply" : " replies");
                            ?>

                        </td>
                    </tr>

                    <?php

                }

                ?>
                    </tbody>
                </table>

            </div>

             <?php

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

function getcatname($x) {

    global $con;

    $fq = mysqli_query($con, "SELECT `name` FROM `forumcat` WHERE `id`=".$x);
    $fr = mysqli_fetch_assoc($fq);

    return $fr["name"];

}
