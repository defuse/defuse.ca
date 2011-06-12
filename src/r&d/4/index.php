<?php
require_once('inc/constants.inc.php');
require_once('inc/security.inc.php');

$user = Security::GetCurrentUser();

if(isset($_POST['submit']))
{
	$username = $_POST['username'];
	$password = $_POST['password'];

	$res = Security::TryLogin($username, $password);
	if($res)
	{
		Header('Location: userhome.php');
		die();
	}
	else
	{
		$error = ERROR_INVALID_LOGIN;
	}
}


include('inc/uitop.inc.php');
?>
<html>
<head>
	<title>StubShare Alpha</title>
</head>
<body>
	<?php
	if($user === false)
	{
		?>
		<div class="box">
		<h2>Register</h2>
		<?php
		if(isset($_GET['e']))
		{
			echo '<b>' . SafeConstant($_GET['e']) . '</b>';
		}
		?>
		<form action="create.php" method="post" >
		Username: <input type="text" name="username" value="" /> <br />
		Password: <input type="password" name="pass1" value="" /><br />
		Verify Password <input type="password" name="pass2" value="" /><br />
		<input type="submit" name="submit" value="Create Account" /><br />
		</form>
		</div>
		<?
	}
	?>
	<div class="box">
	<h2>Stubshare.net</h2>
	Welcome to StubShare.net.
	</div>
</body>
</html>
<?php include('inc/uibottom.inc.php'); ?>
