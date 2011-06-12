<?php
require_once('inc/constants.inc.php');
require_once('inc/security.inc.php');

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


?>
<html>
<head>
	<title>StubShare Login Page</title>
</head>
<body>
	<?php
	if(isset($error))
	{
		echo '<b>' . $error . '</b>';
	}
	if(isset($_GET['c']))
	{
		echo '<b>' . 'Account created, please login.' .  '</b>';
	}
	?>
	<form action="login.php" method="post" >
	Username: <input type="text" name="username" value="" /> <br />
	Password: <input type="password" name="password" value="" /><br />
	<input type="submit" name="submit" value="Login" /><br />
	</form>
</body>
</html>
