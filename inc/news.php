<?php

checkembed($r_c);
include "analyticstracking.php";

include "markdown/markdown.php";

$_SESSION["lp"] = "news";

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
// DISPLAY ALL THE NEWS

    if (!isset($_GET["page"]) || !is_numeric($_GET["page"]) || $_GET["page"] < 1)
        $page = 1;
    else
        $page = strip($_GET["page"]);

    $xo = ($page - 1) * 5;
    $query = mysqli_query($con, "SELECT * FROM `news` WHERE `live` = 1 ORDER BY `id` DESC LIMIT ".$xo.", 5");

    if (mysqli_num_rows($query) == 0) {
        ?>

        No news posts found.

        <?php
    } else {

        while ($row = mysqli_fetch_assoc($query)) {

            // TITLE, AUTHOR & DATE
            ?>

            <div class='article-header'>
            <div class='article-title'><h1><a href='?p=news&amp;id=<?php echo $row["id"]; ?>'><?php echo $row["title"]; ?></a></h1></div>
            <div class='article-metadata'>

            <?php

            if ($row["comments"] == 1) {

                $cq = mysqli_query($con, "SELECT `id` FROM `newscomments` WHERE `newsid`=".$row["id"]);
                $commnum = mysqli_num_rows($cq);
                ?>

                <?php
                if ($commnum != 1) {
                    ?>
                    <span class='article-metadata-item'><a href='?p=news&amp;id=<?php echo $row["id"]; ?>#comments'><?php echo $commnum; ?> comments</a></span>
                    <?php
                } else {
                    ?>
                    <span class='article-metadata-item'><a href='?p=news&amp;id=<?php echo $row["id"]; ?>#comment'><?php echo $commnum; ?> comment</a></span>
                    <?php
                }

            }

            ?>

            <span class='article-metadata-item'><span class='article-author'><?php echo getname($row["authorid"]); ?></span></span><span class='article-metadata-item'><span class='article-date'><?php echo displaydate($row["dt"]); ?></span></span></div>

            <?php

            //if edited
            if ($row["editorid"] > 0) {
                ?>

                <div class='article-edit-metadata'><span class='article-metadata-item'><span class='article-author'><?php echo getname($row["editorid"]); ?></span></span><span class='article-metadata-item'><span class='article-date'><?php echo displaydate($row["editdt"]); ?></span></span></div>

                <?php
            }

            ?>

            </div>

            <?php

            // BODY
            ?>

            <article>
            <span class='article-text'><?php echo Markdown(str_replace("&gt;", ">", $row["text"])); ?></span>
            </article>
            <hr class='article-separator'>

            <?php

        }
    }

    //page links
    $nr = mysqli_num_rows(mysqli_query($con, "SELECT `id` FROM `news`"));
    for ($i = 1; $i <= ceil($nr / 5); $i++) {
        if ($page == $i)
            echo "Page ".$i;
        else
            echo "<a href='?p=news&amp;page=".$i."'>Page ".$i."</a>";
    }
} else {
    // DISPLAY ONE PIECE OF NEWS

    $id = strip($_GET["id"]);

    $query = mysqli_query($con, "SELECT * FROM `news` WHERE `id`=".$id);

    if (mysqli_num_rows($query) == 1) {

        $row = mysqli_fetch_assoc($query);

        ?>

        <div class='article-header'>
        <div class='article-title'><h1><?php echo $row["title"]; ?></h1></div><div class='article-metadata'>

        <span class='article-metadata-item'><span class='article-author'><?php echo getname($row["authorid"]); ?></span></span><span class='article-metadata-item'><span class='article-date'><?php echo displaydate($row["dt"]); ?></span></span></div>

        <?php
        //if edited
        if ($row["editorid"] > 0) {
            ?>

            <div class='article-edit-metadata'><span class='article-metadata-item'><span class='article-author'><?php echo getname($row["editorid"]); ?></span></span><span class='article-metadata-item'><span class='article-date'><?php echo displaydate($row["editdt"]); ?></span></span></div>

            <?php
        }
        ?>

        </div><article>
        <span class='article-text'><?php echo Markdown(str_replace("&gt;", ">", $row["text"])); ?></span>
        </article>

        <?php

        if ($row["comments"] == 1) {

            if (isset($_POST["cp"]) && trim($_POST["text"]) != "") {

                    $author = $_SESSION["userid"];
                    $text = strip($_POST["text"]);
                    $dt = time();

                    $iq = mysqli_query($con, "INSERT INTO `newscomments` VALUES('','$author','$text','$dt','$id')");

                }

                $cq = mysqli_query($con, "SELECT * FROM `newscomments` WHERE `newsid`=".$row["id"]." ORDER BY `id` ASC");
                $commnum = mysqli_num_rows($cq);

               if ($commnum > 0) {

                    if ($commnum != 1) {
                        ?>
                        <hr><div class='comments'><h1 class='comments-title'><a href='#comments'><?php echo $commnum; ?> comments</a></div>
                        <?php
                    } else {
                        ?>
                        <hr><div class='comments'><h1 class='comments-title'><a href='#comments'><?php echo $commnum; ?> comment</a></div>
                        <?php
                    }

                        while ($crow = mysqli_fetch_assoc($cq)) {
                            ?>

                            <div class='comment'><span class='comment-metadata'>
                            <span class='comment-author'><?php echo getname($crow["authorid"]); ?></span><span class='comment-date'><?php echo displaydate($crow["dt"]); ?></span>
                            <?php
                            
                            ?>
                            </span><div class='comment-text'><?php echo Markdown(str_replace("&gt;", ">", $crow["text"])); ?></div>
                            </div>

                            <?php
                        }

                }

                if (checkuser()) {
                    ?>

                    <hr><h1 class='comments-title'>Post a comment</h1>
                    <div class='comment-form'><form action='?p=news&amp;id=<?php echo $id; ?>' method='post'>
                    <textarea name='text' class='comment-textarea' required></textarea>
                    <input type='submit' name='cp' value='&gt;' class='comment-submitbutton'>
                    </form></div></div>

                    <?php
                } else {
                    ?>

                    <hr><h1 class='comments-title'>Log in to be able to post comments</h1></div>

                    <?php
                }

            } else {
                ?>

                <hr><h1 class='comments-title'>Commenting disabled</h1></div>

                <?php
            }
    
    } else {
        
        // redirecting to the main page instead of giving an error message
        header("Location: ?p=news");
        
    }
}
