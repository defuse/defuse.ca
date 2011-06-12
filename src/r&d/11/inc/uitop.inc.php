<?php
//Info to display on page



header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Pragma: no-cache');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
<link  href="http://fonts.googleapis.com/css?family=Ubuntu:300,300italic,regular,italic,500,500italic,bold,bolditalic" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="style.css" type="text/css">
<script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script>
<script src="http://platform.twitter.com/widgets.js" type="text/javascript"></script>
</head>
<body>
<div id="everything">
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
	<img id="logo" src="images/stubshare.png" alt="Stubshare" /></a>
	<div id="controlbar">
		<ul>
			<?php
			if($user != false)
			{
				?>
				<li id="leftmost">Logged in as <?php echo htmlspecialchars($user, ENT_QUOTES); ?></li>
				<li><a href="userhome.php">Home</a></li>
				<li><a href="products.php">Sell Products</a></li>
				<li><a href="accountsettings.php">Settings</a></li>
				<li><a href="logout.php">Logout</a></li>
				<?
			}
			?>
		</ul>
	</div>
</div>
<div id="content">
	<div id="main">
	<?php
	if(strpos($_SERVER['REQUEST_URI'], "userhome") === false && strpos($_SERVER['REQUEST_URI'], "index") === false && substr($_SERVER['REQUEST_URI'], -1) != "/")
	{
	?>
	<div class="box">
		<a href="userhome.php">Back to account homepage</a>
	</div>
	<?
	}
	?>
