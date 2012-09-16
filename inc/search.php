<?php

if (isset($_POST["searchb"])) {

$term = $_POST["searchb"];
echo "<h1>Results for: $term</h1>";

$squery = mysql_query("SELECT * FROM news WHERE text LIKE '%$term%' ORDER BY id DESC");
if (mysql_num_rows($squery) == 0) {
echo "<p>No results found.</p>";
} else {
while ($srow = mysql_fetch_assoc($squery)) {
			echo "<a href='?pnews&id==".$srow["id"]."'><h1>".$srow["title"]."</h1></a>";
			echo $srow["date"]." - ".$srow["author"];
			echo "<p>".substr($srow["text"], 0, 100)."...</p>";
}
}

} else {
echo "<p>No keyword defined.</p>";
} 

?>