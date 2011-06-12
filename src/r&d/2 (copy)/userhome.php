<?php
require_once('inc/security.inc.php');
require_once('inc/stubshare.inc.php');

$user = Security::GetCurrentUser();

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
<a href="userhome.php">Account Home</a> | <a href="products.php">Sell with StubShare</a>
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
[table showing their purchases]<br />
One column of this table will be a button that will add the prodcut to their "Shared Stubs" (see below)

<h2>Your Shared Stubs</h2>
Table of stubs that this user is sharing (but may not have purchased themselves). These will show up in their Stub store.
<br />Users add products to share by clicking the button that will be in the above table, or by finding them on the web and adding them (without buying the product).

</body>
</html>
