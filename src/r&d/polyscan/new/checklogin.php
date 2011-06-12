<?php
include('db_info.php');
require_once('user_api.php');
require_once('security.php');

if(isset($_POST['submit']))
{
	$username = $_POST['user'];
	$safeuser = sqli($username);
	$password = $_POST['pass'];
	
	$check = mysql_query("SELECT salt, token FROM users WHERE username='$safeuser'");
	$data = mysql_fetch_array($check);
	
	$salt = $data['salt'];
	//SHA256(SHA256($username . $password) . $salt)
	$token = hash("SHA256", hash('SHA256', $username . $password) . $salt);
	
	if($token === $data['token'])
	{
		setcookie("username", obf($username), 0);
		setcookie("token", obf($token), 0);
		header('Location: index.php');
	}
	else
	{
		echo "baaaaaad passsssssssss";
		//bad password
	}
}

?>