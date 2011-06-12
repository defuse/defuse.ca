<?php

require_once('inc/db.inc.php');
require_once('inc/security.inc.php');
require_once('inc/stubshare.inc.php');

if(isset($_POST['submit']))
{
	if(empty($_POST['username']))
	{
		FailError("ERROR_NO_USERNAME");
		die();
	}

	if(ContainsNonAlpha($_POST['username']))
	{
		FailError("ERROR_NONALPHA_USERNAME");
		die();
	}

	if(UsernameExists($_POST['username']))
	{
		FailError("ERROR_EXISTING_USER");
		die();
	}

	if(empty($_POST['pass1']))
	{
		FailError("ERROR_NO_PASSWORD");
		die();
	}

	if($_POST['pass1'] != $_POST['pass2'])
	{
		FailError("ERROR_PASS_NOT_MATCH");
		die();
	}

	//Everything is valid..
	$safe_hash = mysql_real_escape_string(Security::HashPassword($_POST['pass1']));
	$safe_user = mysql_real_escape_string($_POST['username']);

	$res = mysql_query("INSERT INTO users (username, password, balance) VALUES('$safe_user', '$safe_hash', '50.00')");
	$userid = mysql_insert_id();

	//TODO: Obviously the free $50 and the following code are for demo purposes...

		$q = mysql_query("SELECT * FROM products");
		$num = 0;
		while($num < 6 && $nextProduct = mysql_fetch_array($q))
		{
			StubShare::AddStub($nextProduct['id'], $userid);
			$num++;
		}

	if(!$res)
	{
		FailError("ERROR_CREATING_ACCOUNT");
		die();
	}

	Security::TryLogin($_POST['username'], $_POST['pass1']);
	header('Location: userhome.php');
	
}

function FailError($type)
{
	header('Location: index.php?e=' . $type);
}

function ContainsNonAlpha($check)
{
	for($i = 0; $i < strlen($check); $i++)
	{
		$c = $check[$i];
		if( !($c >= 'A' && $c <= 'Z' || $c >= 'a' && $c <= 'z') ) //if not alpha
			return true;
	}
	return false;
}

function UsernameExists($check)
{
	$check = mysql_real_escape_string($check);
	$q = mysql_query("SELECT * FROM users WHERE username='$check'");
	if(!$q) return false;
	return mysql_num_rows($q) !== 0;
}

?>

