<?php
if(isset($_GET['created']))
{
	echo "Your account has been created. Please login below.";
}
?>
<form action="checklogin.php" method="post" >
<table cellspacing="10px">
<tr><td>Username:</td><td><input type="text" name="user" value="" /></td></tr>
<tr><td>Password:</td><td><input type="password" name="pass" value="" /></td></tr>
<tr><td><input type="submit" name="submit" value="Login" /></td></tr>
</table>
</form>