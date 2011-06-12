<?php
include 'libs1.php';

$update = mysql_fetch_array(mysql_query("SELECT * FROM settings"));
if(sqlsani($update) == '1'){
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
    echo '<tr><td>A-Sqaured:</td><td>' . isupdated($update['asquared'] . '</td></tr>';
    echo '<tr><td>Avira:</td><td>' . isupdated($update['$avira'] . '</td></tr>';
    echo '<tr><td>BitDefender:</td><td>' . isupdated($update['$bitdefender'] . '</td></tr>';
    echo '<tr><td>e-Safe:</td><td>' . isupdated($update['$esafe'] . '</td></tr>';
    echo '<tr><td>Eset:</td><td>' . isupdated($update['$eset'] . '</td></tr>';
    echo '<tr><td>F-Prot:</td><td>' . isupdated($update['$fprot'] . '</td></tr>';
    echo '<tr><td>Ikarus:</td><td>' . isupdated($update['$ikarus'] . '</td></tr>';
    echo '<tr><td>Kaspersky:</td><td>' . isupdated($update['$kav'] . '</td></tr>';
    echo '<tr><td>McAfee:</td><td>' . isupdated($update['$mcafee'] . '</td></tr>';
    echo '<tr><td>Norton:</td><td>' . isupdated($update['$norton'] . '</td></tr>';
	echo '<tr><td>Panda:</td><td>' . isupdated($update['$panda'] . '</td></tr>';
	echo '<tr><td>QuickHeal:</td><td>' . isupdated($update['$quickheal'] . '</td></tr>';
	echo '<tr><td>Solo:</td><td>' . isupdated($update['$solo'] . '</td></tr>';
    echo '<tr><td>Sophos:</td><td>' . isupdated($update['$sophos'] . '</td></tr>';
    echo '<tr><td>VBA32:</td><td>' . isupdated($update['$vba32'] . '</td></tr>';
    echo '<tr><td>VirusBuster:</td><td>' . isupdated($update['$virusbuster'] . '</td></tr>';
	echo '</table>';
die();
}

if($_GET['type'] == 'arc'){
$arcid = sqlsani($_GET['hash']);
$qhash = mysql_query("SELECT * FROM queue WHERE arcid='$arcid'");
$i=1;
while($phash = mysql_fetch_array($qhash)){
if(count($phash)>0)
{
print "<a href='scanner1.php?type=file&id=".$phash['hash']."'>".$phash['filename']."</a><br />";

}
$i++;
}
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

$numavs = 16;
$total = $numavs;
if($finished == '0'){
	$n = infront($safehash);
    if($n != 0)
    {
      //echo 'Your file is being scanned right now.';exit();
    
	echo 'There are '.$n.' files in the queue to be scanned before yours.';exit();
    }
	
}
	
function isdetected($det)
{
	global $total;
	if($det=="0")
	{
		$total--;
		return "Scanning";
	}
	elseif($det=="1")
	{
	$total--;
	return "Clean";
	}
	else
	
		return xsssani($det);
}
//if(!$finished)
//{
	
//}
	
$filename = isdetected($result['filename']);
$hash = isdetected($result['hash']);
$sha1 = isdetected($result['sha1']);
$md5 = isdetected($result['md5']);
$filesize = isdetected($result['filesize']);

$asquared = isdetected($result['asquared']);
$avira = isdetected($result['avira']);
$bitdefender = isdetected($result['bitdefender']);
$esafe = isdetected($result['esafe']);
$eset = isdetected($result['eset']);
$fprot = isdetected($result['fprot']);
$ikarus = isdetected($result['ikarus']);
$kav = isdetected($result['kav']);
$mcafee = isdetected($result['mcafee']);
$norton = isdetected($result['norton']);
$panda = isdetected($result['panda']);
$quickheal = isdetected($result['quickheal']);
$solo = isdetected($result['solo']);
$sophos = isdetected($result['sophos']);
$vba32 = isdetected($result['vba32']);
$virusbuster = isdetected($result['virusbuster']);

$sigcheck = $result['sigcheck'];
$strings = xsssani(hex2bin($result['getstrings']));
$strings = str_replace("\n", "\n", $strings);
//echo $asquared;
echo "<table>
			<tr><td>File Name:</td><td>$filename</td></tr>
			<tr><td>SHA256:</td><td>$hash</td></tr>
            <tr><td>SHA1:</td><td>$sha1</td></tr>
            <tr><td>MD5:</td><td>$md5</td></tr>
            <tr><td>File size:</td><td>$filesize</td></tr>
			<tr><td>Detection Rate:</td><td>$total/$numavs</td></tr>
			</table>";
    echo '<br />';
	echo '<table>';
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
    echo '<br />';
    echo '<table>';
	    echo '<tr><td>Strings:</td><td><textarea name="s" rows="10" cols="50">' . $strings . '</textarea></td></tr></table>';
?>