<?php
require_once('db_info.php');
require_once('security.php');

function GetUsername()
{
	if(VerifyUser())
	{
		return GetCookieUser();
	}
	else
	{
		return "public";
	}
}

function VerifyUser()
{
	$user = GetCookieUser();
	$token = GetCookieToken();
	$safeuser = sqli($user);
	$check = mysql_query("SELECT token FROM users WHERE username='$safeuser'");
	if(mysql_num_rows($check) == 0)
		return false;
	$check = mysql_fetch_array($check);
	if($check['token'] !== $token)
		return false;
	return true;
}

//return true if they can private scan (have payments)
function CanPrivateScan()
{
	return NumCredit() > 0 || HasUnlimited();
}

function NumCredit($username = "")
{
	if(!VerifyUser())
		return 0;
	if(empty($username))
	{
		$username = sqli(GetCookieUser());
	}
	else
	{
		$username = sqli($username);
	}
	$credit = mysql_query("SELECT credit FROM users WHERE username='$username'");
	$credit = mysql_fetch_array($credit);
	$numcredit = (int)$credit['credit'];
	return $numcredit;
}

function HasUnlimited($username = "")
{
	if(!VerifyUser())
		return false;
	if(empty($username))
	{
		$username = sqli(GetCookieUser());
	}
	else
	{
		$username = sqli($username);
	}
	$runsout = mysql_query("SELECT runsout FROM users WHERE username='$username'");
	$runsout = mysql_fetch_array($runsout);
	$time = (int)$credit['runsout'];
	return time() < $time;
}

function UnlimitedExpire()
{
	if(!VerifyUser())
		return false;
	$username = sqli(GetCookieUser());
	$runsout = mysql_query("SELECT runsout FROM users WHERE username='$username'");
	$runsout = mysql_fetch_array($runsout);
	$time = (int)$credit['runsout'];
	return $time;
}

function RemoveCredit($amt)
{
	$amt = sqli($amt);
	if(!VerifyUser())
		return;
	$safeuser = sqli(GetCookieUser());
	mysql_query("UPDATE users SET credit=(credit - $amt) WHERE username='$safeuser'");
}

function GetCookieUser()
{
	if(!isset($_COOKIE['username']))
		return "";
	$val = $_COOKIE['username'];
	$de = deobf($val);

	return $de;
}

function GetCookieToken()
{
	if(!isset($_COOKIE['token']))
		return "";
	$val = $_COOKIE['token'];
	$de = deobf($val);
	return $de;
}

?>