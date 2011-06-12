<?php
require_once('inc/stubshare.inc.php');
//Info to display on page



header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Pragma: no-cache');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
	<title>StubShare.net</title>
	<link rel="stylesheet" media="all" type="text/css" href="style.css" />
	<script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script>
	<script src="http://platform.twitter.com/widgets.js" type="text/javascript"></script>
</head>
<body>
<div id="header">
	<?php
	if($user != false)
	{
		?>
			<a href="userhome.php">
		<?
	}
	else
	{
		?>
			<a href="index.php">
		<?
	}
	?>
	<img id="logo" src="images/stubshare-logo.png" alt="STUBSHARE" /></a>
	<?php
	if($user != false)
	{
		?>
		<div id="rightheader">
			<div id="rightnav">
				<a href="userhome.php"><img src="images/home.png" alt="[Home]" /></a>
				<a href="products.php"><img src="images/sellproducts.png" alt="[Sell Products]"  /></a>
				<a href="accountsettings.php"><img src="images/settings.png"  alt="[Settings]" /></a>
				<a href="logout.php"><img src="images/logout.png" alt="[Log Out]"  /></a>
			</div>
			<div id="rightstats">
			Hello, <?php echo htmlspecialchars($user, ENT_QUOTES); ?>.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Credit: <b><?php $balance = round(StubShare::GetUserBalance($user), 2); echo "$$balance"; ?></b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Donations: <b><?php $charity_contrib = round(StubShare::GetUserCharityContrib($user), 2); echo "$$charity_contrib"; ?></b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Shared Stubs: <b><?php $share_profit = round(StubShare::GetUserShareProfit($user), 2); echo "$$share_profit"; ?></b>
			</div>
		</div>
		<?
	}
	?>
</div>
<div id="undergrad"></div>
<div id="wrapper">
	<div id="leftcontent">

