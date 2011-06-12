<?php

$dbhost = 'localhost';

$dbuser = 'root';

$dbpass = '1234567890';

$conn = mysql_connect($dbhost, $dbuser, $dbpass) or die('Error connecting to mysql');

$dbname = 'main';

mysql_select_db($dbname);





class server{
	public $ip;
	public $cores;
	public $num;
}

function sqlsani($data){
	return mysql_real_escape_string($data);
}

function xsssani($data){
	return htmlspecialchars($data, ENT_QUOTES);
}

function Finished($hash){
	$hash = sqlsani($hash);
	return mysql_num_rows(mysql_query("SELECT complete FROM queue WHERE hash='$hash'")) >= 1;
}

function CheckConnect($server){
	return true;
}

function hex2bin($h){
		if (!is_string($h)) return null;
		$r='';
		for ($a=0; $a<strlen($h); $a+=2) { $r.=chr(hexdec($h{$a}.$h{($a+1)})); }
		return $r;
}

function rand_str($length = 8, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890')
{
    $chars_length = (strlen($chars) - 1);
    $string = $chars{rand(0, $chars_length)};
    for ($i = 1; $i < $length; $i = strlen($string))
    {
        $r = $chars{rand(0, $chars_length)};
        if ($r != $string{$i - 1}) $string .=  $r;
    }
    return $string;
}

function afterupload($filename, $archive, $rnd, $perm){
$i = 0;
if($archive == 'on'){
$dir = 'C:/scans/' . $rnd . '/';
exec("izarce -p".$dir." -e ".$dir.$rnd);
unlink($dir.$rnd);
if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
		if($file !== "." && $file !== ".." && $file !== "index.php" && filetype($dir . $file) !== 'dir'){
		$cont = file_get_contents($dir.$file);
		$cont = sqlsani(bin2hex($cont));
		$n = count($i) + 1;
		AddToQueue($file, $cont, $filename, $rnd, 0, $perm, $n);
		
        }
		}
        closedir($dh);
    }
}
return $id;
unlink($dir);
} else {
$aname = '0';
$rnd = '0';
$filecontents = sqlsani(bin2hex($filecontents));
$id = AddToQueue($filename, $filecontents, $aname, $rnd);
return $id;
}}

function AddToQueue($filename, $filecontents, $arcname, $arcid, $hash, $perm, $n){
    $sha256 = hash("SHA256", $filecontents);
	if($hash == 0){
	$hash = rand_str();
	}
	$md5 = md5($filecontents);
	$sha1 = sha1($filecontents);
	$filesize = strlen($filecontents);
	$hash = sqlsani($hash);
	$filename = sqlsani($filename);
	$totalhosting = new server();
	$totalhosting->ip = "";
	$totalhosting->cores = 2;
	$totalhosting->num = 0;
	$fire = new server();
	$fire->ip =  "";
	$fire->cores = 6; //YEAHHHHHHHHH
	$fire->num = 1;
	$lowest = 0;
	$lowestval = 99999999;
	$qs = Array($totalhosting); //simple array of all servers TODO: add fire when i have it :)
	for($i = 0; $i < sizeof($qs); $i++)
	{
		$server = $qs[$i];
		$current = NumRows($server->num) / $server->cores; //jobs per core
		if($current < $lowestval && CheckConnect($server->ip))
		{
			$lowestval = $current;
			$lowest = $server->num;
		}
	}
	//this will add the job to the server with the least amount of jobs to do PER CORE
	
	$filecheck = mysql_query("SELECT * FROM queue WHERE sha1='$sha1' AND username='0'");
	$nfilecheck = mysql_num_rows($filecheck);
	$filecheck = mysql_fetch_array($filecheck);
	if($filecheck == 0 || $perm == 1){
	if($arcname !== 0){
	mysql_query("INSERT INTO archive (arcid, arcname, username, hash) VALUES('$arcid', '$arcname', '$perm', '$hash')");
	}
	mysql_query("INSERT INTO queue (queue, hash, filename, complete, filedata, md5, sha1, sha256, filesize, username) VALUES('$lowest', '$hash', '$filename', '0', '$filecontents', '$md5', '$sha1', '$sha256', '$filesize', $perm)");	
	$id[$n] = $hash;
	$n++;
	$id[$n] = "ok";
	return $id;
	} else if($filecheck['complete'] !== '1' && $arcid == 0){
	$id[$n] = sqlsani($filecheck['hash']);
	$n++;
	$id[$n] = "ok";
	return $id;
	} else if($arcid !== 0) {
	mysql_query("INSERT INTO archive (arcid, arcname, username, hash) VALUES('$arcid', '$arcname', '$perm', '$hash')");
	mysql_query("UPDATE queue SET complete = '4' WHERE sha1='$sha1' AND username='0'");	
	} else {
	$result = mysql_fetch_array(mysql_query("SELECT * FROM queue WHERE sha1='$sha1' AND username='0'"));
	$id[$n] = sqlsani($result['hash']);
	$n++;
	$id[$n] = "rescan";
	return $id;
}}

function Numrows($i){
	return mysql_num_rows(mysql_query("SELECT * FROM queue WHERE complete='0' OR complete='2' AND queue='$i'"));
}

function infront($hash){
$id = mysql_fetch_array(mysql_query("SELECT * FROM queue WHERE hash='$hash'"));
$idd = sqlsani($id['id']);
$q = sqlsani($id['queue']);
return mysql_num_rows(mysql_query("SELECT * FROM queue WHERE id < '$idd' AND queue='$q' AND (complete='2' OR complete='0')"));
}

function nextlast(){
$update = mysql_query("SELECT * FROM settings");
$update = mysql_fetch_array($update);
if($update['complete'] == '1'){
print 'Currently Updating';
} else {
$nstamp = $update['nexttime'] - time();
$lstamp = time() - $update['lasttime'];
print 'Next Update: ' . duration($nstamp) . '<br />';
print 'Last Update: ' . duration($lstamp);
}}

function duration($timestamp) {
    $years = floor($timestamp / (60 * 60 * 24 * 365));
    $timestamp %= 60 * 60 * 24 * 365;
    $weeks = floor($timestamp / (60 * 60 * 24 * 7));
    $timestamp %= 60 * 60 * 24 * 7;
    $days = floor($timestamp / (60 * 60 * 24));
    $timestamp %= 60 * 60 * 24;
    $hrs = floor($timestamp / (60 * 60));
    $timestamp %= 60 * 60;
    $mins = floor($timestamp / 60);
    $secs = $timestamp % 60;
    $str = "";
    if ($years == 1) {
        $str .= "{$years} year ";
    }elseif ($years > 1) {
        $str .= "{$years} yearss ";
    }
    if ($weeks == 1) {
        $str .= "{$weeks} week ";
    }elseif ($weeks > 1) {
        $str .= "{$weeks} weeks ";
    }   
    if ($days == 1) {
        $str .= "{$days} day ";
    }elseif ($days > 1) {
        $str .= "{$days} days ";
    }
    if ($hrs == 1) {
        $str .= "{$hrs} hour ";
    }elseif ($hrs > 1) {
        $str .= "{$hrs} hours ";
    }
    if ($mins == 1) {
        $str .= "{$mins} minute ";
    }elseif ($mins > 1) {
        $str .= "{$mins} minutes ";
    }
    if ($mins > 1 && $secs >= 1) {
        $str .= "{$secs} seconds ";
    }
    return $str;
}


?>
