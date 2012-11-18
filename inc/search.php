<?php

checkembed($r_c);
include "analyticstracking.php";

if (isset($_POST["searchb"])) {

	$term = mysqli_real_escape_string($_POST["searchb"]);
	
	if (strlen($term) >= 3) {

	$squery = mysqli_query("SELECT * FROM `news` WHERE `text` LIKE '%$term%' or `title` LIKE '%$term%' ORDER BY `id` DESC");
	$nr = mysqli_num_rows($squery);

	if ($nr == 0) {

		echo "<h1>No results found for ".$term.".</h1>";

	} else {

		echo "<h1>".$nr." results found for: ".$term."</h1>";
		while ($srow = mysqli_fetch_assoc($squery)) {
			echo "<a href='?p=news&amp;id=".$srow["id"]."'><h1>".$srow["title"]."</h1></a>";
			echo $srow["date"]." - ".$srow["author"];
			echo "<p>".substr($srow["text"], 0, 100)."...</p>";
		}
	
	}
	} else {
	echo "<h1>Please enter a keyword longer than 2 characters.</h1>";
	}

} else {
	
	echo "<h1>No keyword defined.</h1>";

} 

?>