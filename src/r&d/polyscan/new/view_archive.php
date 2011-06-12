<?php
include('db_info.php');
require_once('user_api.php');
require_once('security.php');
require_once('ids.php');

if(isset($_GET['id']))
{
	$safeid = sqli(deobf($_GET['id']));
	$query = mysql_query("SELECT * FROM archive WHERE id='$safeid'");
	if(!is_numeric($safeid) || mysql_num_rows($query) == 0) //die if it doesnt exist (they are hacking us)
	{
		if(is_numeric($_GET['id']))
		{
			echo "Invalid Archive ID.";
		}
		else
		{
			ReportIntrusion("INVALID_ARC_ID", $_GET['id']);
			echo "Hacking attempt. IP recorded.";
		}
		die();
	}
	
	//this code only runs when archive is valid:
	$data = mysql_fetch_array($query);
	
	$arcid = sqli($data['id']);
	
	//find all the files that belong to this archive by looking them up:
	$files = mysql_query("SELECT * FROM queue WHERE arcid='$arcid'"); //is how your supposed to do it :)

	$count = mysql_num_rows($files);
	echo "Archive: " . xss($data['arcname']) . "<br />" . "File Count: " . xss($count) . "<br />";
	echo "<h3>Files:</h3><table><tr><th>Name</th><th>MD5</th><th>Status</th></tr>";
	while($file = mysql_fetch_array($files))
	{
		$id = obf($file['id']);
		$safename = xss($file['filename']);
		$md5 = xss($file['md5']);
		$complete = $file['complete'];
		$status = "Scanning..";
		if($complete == 1)
		{
			$status = "Complete";
		}
		echo "<tr>";
		echo "<td><a href=\"index.php?p=view&id=$id\">$safename</a></td><td>$md5</td><td>$status</td>";
		echo "</tr>";
	}
	echo "</table>";
}
?>