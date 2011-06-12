<?php
$username="ossbox";
$password="Nw552SfbbZp";
$database="cracky_bin";
mysql_connect("localhost",$username,$password);
@mysql_select_db($database) or die( "Unable to select database");


function SafeEncode($data)
{
	return mysql_real_escape_string(base64_encode($data));
}

function SafeDecode($data)
{
	return base64_decode($data);
}

function xsssani($data)
{
	return htmlspecialchars($data, ENT_QUOTES);
}
function hex2bin($h)
	{
		if (!is_string($h)) return null;
		$r='';
		for ($a=0; $a<strlen($h); $a+=2) { $r.=chr(hexdec($h{$a}.$h{($a+1)})); }
		return $r;
	}

function smartslashes($data)
{
	if(get_magic_quotes_gpc())
	{
		return stripslashes($data);
	}
	else
	{
		return $data;
	}
}

?>
