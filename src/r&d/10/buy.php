<?php
require_once('inc/security.inc.php');
require_once('inc/stubshare.inc.php');

$user = Security::GetCurrentUser();
$userid = (int)StubShare::GetUserID($user);

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
		$location = "userhome.php";

		if($_POST['submit'] == "Buy and Share" || $_POST['submit'] == "Just Share")
		{
			$stubInfo = StubShare::GetStubInfo($post_stub);
			$sid = StubShare::AddStub($stubInfo['product'], $userid);
			$location = "stubcode.php?id=$sid";
		}

		if(strpos($_POST['submit'], "Buy") !== false )
		{
			if($url = StubShare::ProcessPurchase($user, $post_stub))
			{
				header("Location: $url");
				die();
			}
		}
		else
		{
			header("Location: $location");
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
