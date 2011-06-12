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
	header('Location: userhome.php');
}

include('inc/uitop.inc.php');
?>
<div class="box">
<h2>Add Credit</h2>
<form action="addbalance.php" method="post">
<input type="text" name="amt" value="10.00" /><input type="submit" name="addcredit" value="Add" />
</form>
</div>
<?php include('inc/uibottom.inc.php'); ?>
