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
	<div class="box">
	<h2>Stubshare.net</h2>
	Welcome to StubShare.net.
	</div>
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
		<table>
		<tr><td>Username: </td><td><input type="text" name="username" value="" /></td></tr>
		<tr><td>Password:</td><td><input type="password" name="pass1" value="" /></td></tr>
		<tr><td>Verify Password: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td><input type="password" name="pass2" value="" /></td></tr>
		</table>
		<input type="submit" name="submit" value="Create Account" /><br />
		</form>
		</div>
		<?
	}
	else
	{
	?>
		<div class="box">
			<h2>Logged In</h2>
			You are already logged in. <a href="userhome.php">Click here</a> to go to your account homepage.
		</div>
	<?
	}
	?>
<?php include('inc/uibottom.inc.php'); ?>
