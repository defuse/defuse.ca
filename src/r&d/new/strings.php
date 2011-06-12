<?php
include('db_info.php');
require_once('user_api.php');
require_once('security.php');
require_once('ids.php');
$safeuser = sqli(GetUsername());


if(isset($_GET['id'])) //if they are requiesting the id
{
	$deobfid = deobf($_GET['id']);
	$safeid = sqli($deobfid); //grab the id, this could be a recently uploaded file or an old file in archive
	$xssid = xss($_GET['id']);
	//grab the data for this id
	$query = mysql_query("SELECT * FROM queue WHERE id='$safeid'");
	
	if(!is_numeric($deobfid) || mysql_num_rows($query) == 0) //die if it doesnt exist (they are hacking us)
	{
		if(is_numeric($deobfid))
		{
			echo "Invalid Id.";
		}
		else
		{
			ReportIntrusion("INVALID_ID", $_GET['id']);
			echo "Hacking attempt. IP recorded.";
		}
		die();
	}

	$data = mysql_fetch_array($query);
	if(!empty($data['getstrings']))
	{
		echo "<pre>";
		echo xss(hex2bin($data['getstrings']));
		echo "</pre>";
	}
}

?>