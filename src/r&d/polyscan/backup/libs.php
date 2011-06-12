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

function sqlsani($data)

{

	return mysql_real_escape_string($data);

}

function xsssani($data)

{

	return htmlspecialchars($data, ENT_QUOTES);

}



function Finished($hash)

{

	$hash = sqlsani($hash);

	return mysql_num_rows(mysql_query("SELECT complete FROM queue WHERE hash='$hash'")) >= 1;

}

function CheckConnect($server)
{
	return true;
}
function hex2bin($h)

	{

		if (!is_string($h)) return null;

		$r='';

		for ($a=0; $a<strlen($h); $a+=2) { $r.=chr(hexdec($h{$a}.$h{($a+1)})); }

		return $r;

	}

function AddToQueue($filename, $filecontents)
{
    $hash = hash("SHA256", $filecontents);
	$md5 = md5($filecontents);
	$sha1 = sha1($filecontents);
	$filesize = strlen($filecontents);

	$filecontents = sqlsani(bin2hex($filecontents));
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
	mysql_query("DELETE FROM queue WHERE hash='$hash'");
	mysql_query("INSERT INTO queue (queue, hash, filename, complete, filedata, md5, sha1, filesize) VALUES('$lowest', '$hash', '$filename', '0', '$filecontents', '$md5', '$sha1', '$filesize')");
    return $hash;
}



function Numrows($i)

{

	return mysql_num_rows(mysql_query("SELECT * FROM queue WHERE complete='0' OR complete='2' AND queue='$i'"));

}



function Infront($hash)

{
$id = mysql_query("SELECT * FROM queue WHERE hash='$hash'");
$id = mysql_fetch_array($id);
$idd = $id['id'];
$q = $id['queue'];
return mysql_num_rows(mysql_query("SELECT * FROM queue WHERE id < '$idd' AND queue='$q' AND complete='2' OR complete='0'"));
}



?>
