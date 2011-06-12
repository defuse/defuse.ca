<?php
include 'libs.php';
if($_GET['type'] == 'arc'){
echo '1';
die();
}
$hash = $_GET['hash'];
$safehash = sqlsani($hash);

$result = mysql_query("SELECT * FROM queue WHERE hash='$safehash'");
if(mysql_num_rows($result) == 0)
{
	echo "Invalid id.";
	die();
}

$result = mysql_fetch_array($result);
$finished = $result['complete'];
echo $finished;
?>