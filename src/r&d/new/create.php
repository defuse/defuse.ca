<?php
include('db_info.php');
require_once('user_api.php');
require_once('security.php');

if(isset($_POST['submit']))
{
	$username = $_POST['user'];
	$safeuser = sqli($username);
	$password = $_POST['pass'];
	$confirm = $_POST['confirm'];
	$salt = gen_salt();
	$check = mysql_query("SELECT username FROM users WHERE username='$safeuser'");
	if(mysql_num_rows($check))
	{
		echo "error"; die();
		//redirect to err
	}
	if($password !== $confirm)
	{
		echo "error"; die();
		//redirect to err
	}
	
	//SHA256(SHA256($username . $password) . $salt)
	$token = hash("SHA256", hash('SHA256', $username . $password) . $salt);
	$query = mysql_query("INSERT INTO users (username, token, salt, credit, runsout) VALUES ('$safeuser', '$token', '$salt', '0', '0')");
	if($query)
	{
		header('Location: index.php?p=login&created=true');
	}
}

function gen_salt()
{
	return bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
}
?>