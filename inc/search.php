<?php

checkembed($r_c);

include "analyticstracking.php";
include "markdown/markdown.php";

?>

<?php

if (isset($_POST["searchb"]) && strip($_POST["searchb"]) != "") {

    $term = strip($_POST["searchb"]);

    if (strlen($term) >= 3) {

        if (!isset($_POST["inn"]) && !isset($_POST["inf"])) {
            $_POST["inn"] = "on";
        }

        if (isset($_POST["inn"]) && $_POST["inn"] == "on") {
            $squery = mysqli_query($con, "SELECT * FROM `news` WHERE `text` LIKE '%".$term."%' or `title` LIKE '%".$term."%' ORDER BY `id` DESC");
            $nr = mysqli_num_rows($squery);

            if ($nr == 0) {

                if (strlen($term) > 23) {
                    ?>

                    <div class='search-title'>No <button class="search-type" type="button">result</button>s found for your search term</div>

                    <?php
                } else {
                    ?>

                    <div class='search-title'>No <button class="search-type" type="button">result</button>s found for <span class='search-term'><?php echo $term; ?></span></div>

                    <?php
                }

            } else {

                if (strlen($term) > 23) {
                    if ($nr == 1) {
                        ?>

                        <div class='search-title'><?php echo $nr; ?> <button class="search-type" type="button">result</button> found for your search term</div>

                        <?php
                    } else {
                        ?>

                        <div class='search-title'><?php echo $nr; ?> <button class="search-type" type="button">result</button>s found for your search term</div>

                        <?php
                    }
                } else {
                    if ($nr == 1) {
                        ?>

                        <div class='search-title'><?php echo $nr; ?> <button class="search-type" type="button">result</button> found for <span class='search-term'><?php echo $term; ?></span></div>

                        <?php
                    } else {
                        ?>

                        <div class='search-title'><?php echo $nr; ?> <button class="search-type" type="button">result</button>s found for <span class='search-term'><?php echo $term; ?></span></div>

                        <?php
                    }
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

                        $cq = mysqli_query($con, "SELECT `id` FROM `forumposts` WHERE `tid`=".$srow["id"]);
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
        }

        if (isset($_POST["inf"]) && $_POST["inf"] == "on") {

            $squery1 = mysqli_query($con, "SELECT * FROM `forums` WHERE `text` LIKE '%".$term."%' or `title` LIKE '%".$term."%' ORDER BY `id` DESC") or die(mysqli_error($con));
            $squery2 = mysqli_query($con, "SELECT * FROM `forumposts` WHERE `text` LIKE '%".$term."%' ORDER BY `id` DESC") or die(mysqli_error($con));
            ?>
            <?php echo mysqli_num_rows($squery1) + mysqli_num_rows($squery2); ?> results found in the forums
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

?>

<form method='post' action='?p=search'>
search in
<input type='checkbox' name='inn'
<?php if (isset($_POST["inn"]) && $_POST["inn"] == "on") echo "checked" ?>
> news
<input type='checkbox' name='inf'
<?php if (isset($_POST["inf"]) && $_POST["inf"] == "on") echo "checked" ?>
> forums
<input type='hidden' name='searchb' value='<?php echo $term; ?>'>
<input type='submit' value='go'>
</form>
