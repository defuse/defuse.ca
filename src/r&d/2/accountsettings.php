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

if(isset($_POST['changecharity']))
{
	$charity_id = (int)$_POST['charity'];
	$profit_for_charity = (double)$_POST['pct'];
	if(StubShare::GetCharityName($charity_id) && $profit_for_charity >= 0 && $profit_for_charity <= 100)
	{
		mysql_query("UPDATE users SET charity='$charity_id', profit_for_charity='$profit_for_charity' WHERE id='$userid'");
	}
}


$userInfo = StubShare::GetUserInfo($user);
$pct_charity = (double)$userInfo['profit_for_charity'];
?>
<html>
<head>
	<title>StubShare User Account Page</title>
</head>
<body>
<a href="userhome.php">Account Home</a> | <a href="products.php">Sell with StubShare</a>
<h1>Account Settings</h1>
<h2>Charity</h2>
<form action="accountsettings.php" method="post">
Charity donations go to: 
<select name="charity">
<?php
	//TODO: show current charity
	$userInfo = StubShare::GetUserInfo($user);
	$q = mysql_query("SELECT * FROM charities");
	while($info = mysql_fetch_array($q))
	{
		$safe_id = (int)$info['id'];
		$safe_name = htmlspecialchars($info['name']);

		$selected = "";
		if($userInfo['charity'] == $safe_id)
		{
			$selected = "selected";
		}
		echo "<option value=\"$safe_id\" $selected >$safe_name</option>";
	}
?>
</select>
<br /><br />
TODO: script to show charity info
<br />
Percent of your profit (from sharing stubs) to give to your charity:
<input type="text" name="pct" value="<?php echo $pct_charity; ?>"/>
<input type="submit" name="changecharity" value="Save" />
</form>


<input type="submit" name="
</body>
</html>
