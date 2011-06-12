<?php
require_once('inc/db.inc.php');

$q = mysql_query("SELECT * FROM charities");

while($cinfo = mysql_fetch_array($q))
{
	print $cinfo['name'] . " ---------> " . $cinfo['balance'];
	echo "<br /><br />";
}

$q = mysql_query("SELECT * FROM profit");
$info = mysql_fetch_array($q);
print $info['name'] . "---------->" . $info['balance'];

?>
