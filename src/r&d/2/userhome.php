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

if(isset($_POST['addcredit']))
{
	$add = (double)$_POST['amt'];
	StubShare::AddUserBalance($user, $add);
}

//Info to display on page
$htmlsafe_user = htmlspecialchars($user, ENT_QUOTES);
$balance = (double)StubShare::GetUserBalance($user);
?>
<html>
<head>
	<title>StubShare User Account Page</title>
</head>
<body>
<a href="userhome.php">Account Home</a> | <a href="products.php">Sell with StubShare</a> | <a href="stubstore.php">Stub Store/Archive</a> 
<h1>Your StubShare Account</h1>
Balance: <?php echo $balance; ?><br />
Donations to charity from your purchases: <br />
Donations to charity from your shared stubs:<br />
Profit you've made from sharing stubs: <br />
<h2>Add Credit</h2>
<form action="userhome.php" method="post">
<input type="text" name="amt" value="10.00" /><input type="submit" name="addcredit" />
</form>
<h2>Your Purchases</h2>
<table border=1 >
<tr>
	<th>Product</th>
	<th>Owner</th>
	<th>Dispute</th>
	<th>Share</th>
</tr>
<?php
$q = mysql_query("SELECT * FROM purchases WHERE user='$userid'");
while($ary = mysql_fetch_array($q))
{
	$productInfo = StubShare::GetProductInfo($ary['product']);

	$productName = htmlspecialchars($productInfo['name'], ENT_QUOTES);
	$productID = (int)$productInfo['id'];	
	$ownerID = $productInfo['owner'];

	$ownerInfo = StubShare::GetUserInfo(StubShare::GetUsernameFromID($ownerID));
	$ownerName = htmlspecialchars($ownerInfo['username'], ENT_QUOTES);

	$shareLink = WEBROOT . "share.php?id=$productID";

	$linkbefore = "";
	$linkafter = "";
	if($url = StubShare::UserOwns($userid, $productID))
	{
		$linkbefore = "<a href=\"$url\">";
		$linkafter = "</a>";
	}
	echo "<tr><td>$linkbefore$productName$linkafter</td><td>$ownerName</td><td>[dispute link]</td><td><a href=\"$shareLink\">Share</a></td></tr>";
}
?>
</table>

One column of this table will be a button that will add the prodcut to their "Shared Stubs" (see below)

<h2>Your Stubs</h2>
<table border=1 >
<tr>
	<th>Product</th>
	<th>Owner</th>
	<th>Get Share Codes</th>
</tr>
<?php
$q = mysql_query("SELECT * FROM stubs WHERE owner='$userid'");
while($ary = mysql_fetch_array($q))
{
	$stubID = (int)$ary['id'];
	$productID = (int)$ary['product'];		
	$productInfo = StubShare::GetProductInfo($productID);
	
	$ownerInfo = StubShare::GetUserInfo(StubShare::GetUsernameFromID($productInfo['owner']));
	
	$product = htmlspecialchars($productInfo['name'], ENT_QUOTES);
	$owner = htmlspecialchars($ownerInfo['username'], ENT_QUOTES); 

	$linkbefore = "";
	$linkafter = "";
	if($url = StubShare::UserOwns($userid, $productID))
	{
		$linkbefore = "<a href=\"$url\">";
		$linkafter = "</a>";
	}
	else
	{
		$linkbefore = "<a href=\"". WEBROOT . "stubshare/stub.php?id=$stubID\">";
		$linkafter = "</a>";
	}
	echo "<tr><td>$linkbefore$product$linkafter</td><td>$owner</td><td><a href=\"" . WEBROOT . "stubcode.php?id=$stubID\">Get Codes</a></tr>";
}

?>
</table>
</body>
</html>
