<?php
require_once('inc/security.inc.php');
require_once('inc/stubshare.inc.php');

$user = Security::GetCurrentUser();

if($user === false)
{
	header('Location: login.php');
	die();
}

if(isset($_POST['submit']) && isset($_POST['stub']))
{
	$post_stub = (int)$_POST['stub'];

	if(true) //TODO: security token in values
	{
		if($url = StubShare::ProcessPurchase($user, $post_stub))
		{
			header("Location: $url");
			die();
		}
	}
	else
	{
		header('Location: stub.php?id=' . $post_stub);
		die();
	}
}
?>
Sorry. You can't afford that item.
