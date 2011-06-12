<?php
include('db_info.php');
require_once('user_api.php');
require_once('security.php');
require_once('ids.php');

if($_GET['type'] == 'arc'){
echo '1';
die();
}
$hash = $_GET['id'];

$safehash = sqli(deobf($hash));

$result = mysql_query("SELECT * FROM queue WHERE id='$safehash'");
if(mysql_num_rows($result) == 0)
{
	echo "Invalid id.";
	die();
}

$result = mysql_fetch_array($result);
$finished = $result['complete'];
echo $finished;
?>