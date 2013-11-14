<?php if (!isset($r_c)) header("Location: /notfound.php"); ?>
<?php $msgs=array("Houston, we have a four-oh-four.",
                  "If you're happy and you know it, four-oh-four!",
                  "You get a 404, you get a 404, everybody gets a 404!",
                  "A 404 a day keeps the content away.",
                  "Do you believe in &#9835;four after four&#9835;? (With a zero inbetween.)",
                  "A 403 isn't cool. You know what's cool? A 404."); ?>

<div class="notfound-box"></div>
<div class="notfound-quote">
    <span class="notfound-quote-text">&bdquo;<?php echo $msgs[array_rand($msgs)]; ?>&ldquo;</span>
    <br>
    <span class="notfound-quote-data">&mdash;thecookiefactory, <?php $date = getdate(); echo($date["month"] . " " . $date["mday"] . ", " . $date["year"]); ?></span></div>
