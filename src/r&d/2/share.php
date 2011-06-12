<?php
require_once('inc/security.inc.php');
require_once('inc/stubshare.inc.php');
require_once('inc/db.inc.php');
$user = Security::GetCurrentUser();
$userid = (int)StubShare::GetUserID($user);
if($user === false)
{
	header('Location: login.php');
	die();
}

$toShare = (int)$_GET['id'];

StubShare::AddStub($toShare, $userid);

header('Location: userhome.php');
?>
