<?php

include('db_info.php');
require_once('user_api.php');
require_once('security.php');
require_once('ids.php');
$update = mysql_fetch_array(mysql_query("SELECT * FROM settings"));
$total = 0;

if($update['complete'] == '1'){
function isupdated($st)
{
	if($st=="2")
	{
		return "In Queue";
	}
	elseif($st=="1")
	{
		return "Updating";
	}
	elseif($st=="0")
	{
		return "Done";	
	}
}

	echo '<table>';
    echo '<tr><td>A-Sqaured:</td><td>' . isupdated($update['asquared']) . '</td></tr>';
    echo '<tr><td>Avira:</td><td>' . isupdated($update['avira']) . '</td></tr>';
    echo '<tr><td>BitDefender:</td><td>' . isupdated($update['bitdefender']) . '</td></tr>';
    echo '<tr><td>e-Safe:</td><td>' . isupdated($update['esafe']) . '</td></tr>';
    echo '<tr><td>Eset:</td><td>' . isupdated($update['eset']) . '</td></tr>';
    echo '<tr><td>F-Prot:</td><td>' . isupdated($update['fprot']) . '</td></tr>';
    echo '<tr><td>Ikarus:</td><td>' . isupdated($update['ikarus']) . '</td></tr>';
    echo '<tr><td>Kaspersky:</td><td>' . isupdated($update['kav']) . '</td></tr>';
    echo '<tr><td>McAfee:</td><td>' . isupdated($update['mcafee']) . '</td></tr>';
    echo '<tr><td>Norton:</td><td>' . isupdated($update['norton']) . '</td></tr>';
	echo '<tr><td>Panda:</td><td>' . isupdated($update['panda']) . '</td></tr>';
	echo '<tr><td>QuickHeal:</td><td>' . isupdated($update['quickheal']) . '</td></tr>';
	echo '<tr><td>Solo:</td><td>' . isupdated($update['solo']) . '</td></tr>';
    echo '<tr><td>Sophos:</td><td>' . isupdated($update['sophos']) . '</td></tr>';
    echo '<tr><td>VBA32:</td><td>' . isupdated($update['vba32']) . '</td></tr>';
    echo '<tr><td>VirusBuster:</td><td>' . isupdated($update['virusbuster']) . '</td></tr>';
	echo '</table>';
die();
}


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
			ReportIntrusion("INVALID_ID_RES", $_GET['id']);
			echo "Hacking attempt. IP recorded.";
		}
		die();
	}

	$data = mysql_fetch_array($query);
			$q = sqli($data['queue']);
		$front = mysql_num_rows(mysql_query("SELECT * FROM queue WHERE id < '$safeid' AND queue='$q' AND (complete='2' OR complete='0')"));

	if($data['complete'] == '0' || $data['complete'] == '2' && $front > 0)
	{

		echo "Your file is behind " . $front . " files to be scanned.";

	}
	else
	{
		$filename = isdetected($data['filename']);
		$sha256 = isdetected($data['sha256']);
		$sha1 = isdetected($data['sha1']);
		$md5 = isdetected($data['md5']);
		$filesize = isdetected($data['filesize']);
		$asquared = isdetected($data['asquared']);
		$avira = isdetected($data['avira']);
		$bitdefender = isdetected($data['bitdefender']);
		$esafe = isdetected($data['esafe']);
		$eset = isdetected($data['eset']);
		$fprot = isdetected($data['fprot']);
		$ikarus = isdetected($data['ikarus']);
		$kav = isdetected($data['kav']);
		$mcafee = isdetected($data['mcafee']);
		$norton = isdetected($data['norton']);
		$panda = isdetected($data['panda']);
		$quickheal = isdetected($data['quickheal']);
		$solo = isdetected($data['solo']);
		$sophos = isdetected($data['sophos']);
		$vba32 = isdetected($data['vba32']);
		$virusbuster = isdetected($data['virusbuster']);
		$sigcheck = $data['sigcheck'];
		/*$strings = xss(hex2bin($data['getstrings']));
		$strings = str_replace("\n", "\n", $strings);*/

		echo '<table>';
		echo '<tr><td>Strings:</td><td><a href="strings.php?id=' . $xssid . '">View ASCII Strings</a></td></tr>';
		echo '<tr><td>A-Sqaured:</td><td>' . $asquared . '</td></tr>';
		echo '<tr><td>Avira:</td><td>' . $avira . '</td></tr>';
		echo '<tr><td>BitDefender:</td><td>' . $bitdefender . '</td></tr>';
		echo '<tr><td>e-Safe:</td><td>' . $esafe . '</td></tr>';
		echo '<tr><td>Eset:</td><td>' . $eset . '</td></tr>';
		echo '<tr><td>F-Prot:</td><td>' . $fprot . '</td></tr>';
		echo '<tr><td>Ikarus:</td><td>' . $ikarus . '</td></tr>';
		echo '<tr><td>Kaspersky:</td><td>' . $kav . '</td></tr>';
		echo '<tr><td>McAfee:</td><td>' . $mcafee . '</td></tr>';
		echo '<tr><td>Norton:</td><td>' . $norton . '</td></tr>';
		echo '<tr><td>Panda:</td><td>' . $panda . '</td></tr>';
		echo '<tr><td>QuickHeal:</td><td>' . $quickheal . '</td></tr>';
		echo '<tr><td>Solo:</td><td>' . $solo . '</td></tr>';
		echo '<tr><td>Sophos:</td><td>' . $sophos . '</td></tr>';
		echo '<tr><td>VBA32:</td><td>' . $vba32 . '</td></tr>';
		echo '<tr><td>VirusBuster:</td><td>' . $virusbuster . '</td></tr>';
		echo '</table>';
		/*echo '<br />';
		echo '<table>';
		echo '<tr><td>Strings:</td><td><textarea name="s" rows="10" cols="50"><a href="strings.php?id=' . $xssid . '">View ASCII Strings</a></textarea></td></tr></table>';
		*/
	}


}
	//wtf?
	function isdetected($det)
	{
		global $total;
		if($det=="0" || empty($det))
		{
			$total--;
			return "Scanning..";
		}
		elseif($det=="1")
		{
			$total--;
			return "Clean";
		}
		elseif($det=="3")
		{
			$total--;
			return "Clean";
		}
		elseif($det == "DONTDO")
		{
			return "Not Scanned.";
		}
		else
		{
			return xss($det);
		}
	}
?>