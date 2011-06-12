<?php
require_once('inc/constants.inc.php');
//Account creation error message constants



?>
<html>
<head>
	<title>StubShare Alpha</title>
</head>
<body>
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
	<b><a href="login.php">Login Page</a></b>
</body>
</html>
