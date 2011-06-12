<?php
require_once('inc/constants.inc.php');
require_once('inc/security.inc.php');
require_once('inc/stubshare.inc.php');

$user = Security::GetCurrentUser();

if($user === false)
{
	header('Location: login.php');
	die();
}

if(isset($_GET['id']))
{
	$stubID = (int)$_GET['id'];
	$info = StubShare::GetStubInfo($stubID);
	if(!$info)
	{
		header('Location: userhome.php');
		die();
	}
}

$image = StubShare::EncodeStubImage($stubID);
$bbcode = StubShare::EncodeStubBBC($stubID);
$plainURL = WEBROOT . "stub.php?id=$stubID";
?>
<html>
<head>
	<title>StubShare User Account Page</title>
</head>
<body>
<a href="userhome.php">Account Home</a> | <a href="products.php">Sell with StubShare</a>
<h1>Stub Codes</h1>
<table cellspacing=30 >
<tr><th>Type</th><th>Preview</th><th>Code</th></tr>
<tr><td>Image</td><td><?php echo $image; ?></td><td><pre><?php echo htmlspecialchars($image, ENT_QUOTES); ?></pre></td></tr>
<tr><td>URL</td><td><?php echo $plainURL; ?></td><td><?php echo $plainURL; ?></td></tr>
<tr><td>BBCode</td><td></td><td></td></tr>
</table>
</body>
</html>
