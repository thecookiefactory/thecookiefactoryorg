<?php

if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
// DISPLAY ONE PIECE OF NEWS

	$query = mysql_query("SELECT * FROM news WHERE id=".$_GET["id"]);
	
	if (mysql_num_rows($query) == 1) {
	
		$row = mysql_fetch_assoc($query);
	
		echo "<h1>".$row["title"]."</h1>";
		
			echo "<p>".nl2br($row["text"])."</p>";
			echo $row["date"]." - ".$row["author"];
		
			echo "Comments";
			if ($row["comments"] == 1) {
				$cq = mysql_query("SELECT * FROM newscomments WHERE newsid=".$row["id"]." ORDER BY id ASC");
				$commnum = mysql_num_rows($cq);
				echo "<a href='?p=news&id=".$row["id"]."#comments'>".$commnum." comments</a>";
			
				while ($crow = mysql_fetch_assoc($cq)) {
					echo "<div class='comment'>";
					echo $crow["username"]." - ".$crow["date"];
					echo "<p>".nl2br($crow["text"])."</p>";
					echo "</div>";
				}
			
				if (checkuser()) {			
					echo "<form>
					<textarea></textarea>
					<input type='submit' />
					</form>";
				} else {
					echo "you have to be logged in to post comments";
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
		<span class='article-title'><a href='?p=news&id=".$row["id"]."'>".$row["title"]."</a></span>
		<span class='article-metadata'>";
		
		if ($row["comments"] == 1) {
		
			$cq = mysql_query("SELECT id FROM newscomments WHERE newsid=".$row["id"]);
			$commnum = mysql_num_rows($cq);
			echo "<a href='?p=news&id=".$row["id"]."#comments'>".$commnum." comments </a> &ndash; ";
			}

		echo "by ".$row["author"]." &ndash; on ".$row["date"]."</span>
		</div><br />";		

		// BODY
		echo "<div class='article-body'>
		<span class='article-text'>".nl2br($row["text"])."</span>
		</div>
		<hr class='article-separator' />";

	}

}

?>