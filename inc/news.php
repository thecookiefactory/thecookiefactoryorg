<?php

checkembed();

include "analyticstracking.php";

if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
// DISPLAY ONE PIECE OF NEWS

	$query = mysql_query("SELECT * FROM news WHERE id=".$_GET["id"]);
	
	if (mysql_num_rows($query) == 1) {
	
		$row = mysql_fetch_assoc($query);
	
		echo "<div class='article-header'>
		<span class='article-title'>".$row["title"]."</span><span class='article-metadata'>";
		
		echo "by ".$row["author"]." &ndash; on ".$row["date"]."</span>
		</div><br></span>";
		echo "<div class='article-body'>
		<span class='article-text'><p>".nl2br($row["text"])."</p></span>
		</div>";

		if ($row["comments"] == 1) {
			
			if (isset($_POST["cp"])) {
				
				$newsid = $_GET["id"];
				$author = $_SESSION["username"];
				$text = mysql_real_escape_string(htmlentities($_POST["text"]));
				$date = date("Y-m-d");
				$time = date("H:i", time());
				
				$iq = mysql_query("INSERT INTO newscomments VALUES('','$author','$text','$date','$time','$newsid')");
				
				}
			
				$cq = mysql_query("SELECT * FROM newscomments WHERE newsid=".$row["id"]." ORDER BY id ASC");
				$commnum = mysql_num_rows($cq);
				echo "<hr><a name='comments'></a><a href='?p=news&id=".$row["id"]."#comments' id='comments-title'>".$commnum." comments</a><br>";
			
				while ($crow = mysql_fetch_assoc($cq)) {
					echo "<div class='comment'><span class='comment-metadata'>";
					echo "<span class='comment-author'>".$crow["username"]."</span> &middot; <span class='comment-date'>".$crow["date"]."</span>";
					echo "</span><br><p class='comment-text'><span class='comment-text'>".nl2br($crow["text"])."</span></p>";
					echo "</div>";
				}

				if (checkuser()) {			
					echo "<hr><form action='?p=news&id=".$_GET["id"]."' method='post'>
					<textarea name='text'></textarea>
					<input type='submit' name='cp'>
					</form>";
				} else {
					echo "<span id='loginnotice'>you have to be logged in to post comments</span>";
				}
			
			} else 
				echo "commenting disabled";
	} else {
		echo "No.";
	}
} else {
// DISPLAY ALL THE NEWS

	$query = mysql_query("SELECT * FROM news ORDER BY id DESC");

	while ($row = mysql_fetch_assoc($query)) {
	
		// TITLE, AUTHOR & DATE
		echo "<div class='article-header'>
		<span class='article-title'><a href='?p=news&amp;id=".$row["id"]."'>".$row["title"]."</a></span>
		<span class='article-metadata'>";
		
		if ($row["comments"] == 1) {
		
			$cq = mysql_query("SELECT id FROM newscomments WHERE newsid=".$row["id"]);
			$commnum = mysql_num_rows($cq);
			echo "<a href='?p=news&amp;id=".$row["id"]."#comments'>".$commnum." comments </a> &ndash; ";
			}

		echo "by ".$row["author"]." &ndash; on ".$row["date"]."</span>
		</div><br>";		

		// BODY
		echo "<div class='article-body'>
		<span class='article-text'><p>".nl2br($row["text"])."</p></span>
		</div>
		<hr class='article-separator'>";

	}

}

?>