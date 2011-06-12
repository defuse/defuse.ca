<?php
require_once('inc/security.inc.php');
require_once('inc/stubshare.inc.php');

$stubID = (int)$_GET['id'];

$user = Security::GetCurrentUser();

if($user === false)
{
	header('Location: login.php?sid=' . $stubID);
	die();
}

$stubInfo = StubShare::GetStubInfo($stubID);
$productInfo = StubShare::GetProductInfo($stubInfo['product']);

if(!$stubInfo)
{
	header('Location: userhome.php');
	die();
}

$userid = StubShare::GetUserID($user);
if($url = StubShare::UserOwns($userid, $productInfo['id']))
{
	header("Location: $url");
	die();
}

?>
<html>
<head>
	<title>StubShare Alpha</title>
</head>
<body>
<h1>Buying: <?php echo htmlspecialchars($productInfo['name'], ENT_QUOTES); ?></h1>
<table border=1 >
<tr>
	<td>Name:</td>
	<td><?php echo htmlspecialchars($productInfo['name'], ENT_QUOTES); ?></td>
</tr>
<tr>
	<td>Description:</td>
	<td><?php echo htmlspecialchars($productInfo['description'], ENT_QUOTES); ?></td>
</tr>
<tr>
	<td>Price:</td>
	<td><?php echo htmlspecialchars($productInfo['price'], ENT_QUOTES); ?></td>
</tr>
</table>

<form action="buy.php" method="post">
<input type="submit" name="submit" value="Buy" />
<input type="hidden" name="stub" value="<?php echo $stubID; ?>" />
</form>
Press your browser's BACK button to cancel and return to the previous page.
</body>
</html>
