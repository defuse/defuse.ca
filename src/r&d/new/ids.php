<?php
require_once('security.php');
require_once('db_info.php');
	function ReportIntrusion($type, $message)
	{
		$ip = sqli(getRealIpAddr());
		$time = time();
		$safetype = sqli($type);
		$safemsg = sqli($message);
		mysql_query("INSERT INTO ids (time, ip, type, message) VALUES ('$time', '$ip', '$safetype', '$safemsg')");
	}

	function getRealIpAddr()
	{
		if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
		{
		  $ip=$_SERVER['HTTP_CLIENT_IP'];
		}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
		{
		  $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else
		{
		  $ip=$_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}
?>