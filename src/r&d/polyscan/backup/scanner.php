<?php
include 'libs.php';
if(isset($_GET['arcid'])){
$arcid = sqlsani($_GET['arcid']);
$qhash = mysql_query("SELECT * FROM queue WHERE arcid='$arcid'");
$i=1;
while($phash = mysql_fetch_array($qhash)){
if(count($phash)>0)
{
print "<a href='scanner1.php?hash=".$phash['hash']."'>".$phash['filename']."</a><br />";

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

$numavs = 16;
$total = $numavs;
function isdetected($det)
{
	global $total;
	if(empty($det))
	{
		$total--;
		return "Nothing found!";
	}
	else
		return xsssani($det);
}
$finished = $result['complete'] == '1';
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
$strings = str_replace("\n", "<br />", $strings);

?>
<html>
<head>
<?php
	if(!$finished)
	{
		echo '<meta http-equiv="refresh" content="6" >';
	}
?>
<title>scanning</title>
</head>
<body>
<?php
if(!$finished)
{
	$n = Infront($hash);
    if($n == '0')
    {
      echo 'Your file is being scanned right now.';
    } else {
	echo 'There are '.$n.' files in the queue to be scanned before yours.';
    }
}
else
{
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
    $sigcheck = explode("\r\n", $sigcheck);
    $imax = count($sigcheck);
    $i = 0;
    while($i < $imax)
    {
    if($i == "1"){
    $line = explode(":", $sigcheck[$i]);
    echo '<tr><td>' . $line[0] . '</td><td>' . $line[1] . ':' . $line[2] . '</td></tr>';
    } else {
    $line = explode(":", $sigcheck[$i]);
    echo '<tr><td>' . $line[0] . '</td><td>' . $line[1] . '</td></tr>';
    }
    $i++;
    }
    print '</table>';
    echo '<table>';
    echo $strings;
    echo '</table>';
}
?>
</body>
</html>
