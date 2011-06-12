<?php
	require_once('inc/constants.inc.php');
	require_once('inc/security.inc.php');
	Security::Logout();
	header('Location: ' . WEBROOT);
?>
