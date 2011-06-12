<?php
include('db_info.php');
require_once('user_api.php');
require_once('security.php');
require_once('ids.php');
$docount = 0;
if(isset($_GET['id']) && isset($_POST['submit']) ) //if they are requiesting the id
{
	$asquared = DoScan("asquared");
	$avira = DoScan("avira");
	$bitdefender = DoScan("bitdefender");
	$eset = DoScan("eset");
	$esafe = DoScan("esafe");
	$fprot = DoScan("fprot");
	$ikarus = DoScan("ikarus");
	$kaspersky = DoScan("kav");
	$mcafee = DoScan("mcafee");
	$norton = DoScan("norton");
	$panda = DoScan("panda");
	$quickheal = DoScan("quickheal");
	$solo = DoScan("solo");
	$sophos = DoScan("sophos");
	$vba32 = DoScan("sophos");
	$virusbuster = DoScan("virusbuster");
		if($docount == 0)
	{
		echo "Please select at least one antivirus."; die();
	}
	
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
	
	$user = $data['username'];
	$complete = (int)$data['complete'];
	$time = (int)$data['keepuntil'];
	if(time() < $time && $complete == 1 )
	{
		if($user != 'public' && !HasUnlimited($data['username']))
		{
			$safeuser = sqli($user);
			mysql_query("UPDATE users SET credit = (credit - 1) WHERE username='$safeuser'");
		}
		mysql_query("UPDATE queue SET complete='0', sigcheck='', asquared='$asquared', avira='$avira', bitdefender='$bitdefender', esafe='$esafe', eset='$eset', fprot='$fprot', ikarus='$ikarus', kav='$kasperksy', mcafee='$mcafee', norton='$norton', panda='$panda', quickheal='$quickheal', solo='$solo', sophos='$sophos', vba32='$vba32', virusbuster='$virusbuster' WHERE id='$safeid'");
		//echo mysql_error(); die();
		header("Location: index.php?p=view&id=$xssid");
	}
	else
	{
		echo "All your base are belong to us.";
	}

}
function DoScan($avname)
{
	global $docount;
	$docount++;
	if($_POST[$avname] === "true")
	{
		return "";
	}
	else
	{
		return "DONTDO";
	}
}
?>