<?php
	require_once ('libs/passwordbolt.php');
	require_once ('libs/security.php');
	if(($user = PB::CheckLogin(false)) == "")
	{
		header( 'Location: index.php' );
	}
	else
	{
		$key = PB::GetKey($user);
		$token = PB::GetToken($user);
	}


	function ShowMessage($message)
	{
			echo '	<div  class="box">
				<div class="headerbar"><h3>Disable One Time Passwords</h3></div>
				<div class="insidebox">' .
				$message .
				'</div>
			</div>';
	}

	if(isset($_POST['submit']))
	{
		
		//fail and show top + warning again
		//or work and print the one time passwords
		$oldmaster = "";
		if(isset($_POST['oldmaster']))
			$oldmaster = security::smartslashes($_POST['oldmaster']);
		$newmaster = security::smartslashes($_POST['master1']);
		$confirm = security::smartslashes($_POST['master2']);
		if($confirm == $newmaster)
		{
			if(PB::ValidateAndPrint($token, $key, $oldmaster, $newmaster))
			{
				//password list has been shown so we dont want to show anything else
				exit();
			}
			else
			{
				include ('shortcuts/top.php');
				ShowMessage('Wrong master passsword.');
				//show error include top for both errors
			}
		}
		else
		{
			include('shortcuts/top.php');
			ShowMessage('New master password and the confirmation were not the same.');
			//show error
		}
	}
	else
	{
		include ('shortcuts/top.php');
	}

	if(isset($_POST['disable']))
	{
		$oldmaster = security::smartslashes($_POST['oldmaster']);
		if(PB::DisableOTP($token, $key, $oldmaster))
		{
			ShowMessage('One time passwords have been disabled.');
		}
		else
		{
			ShowMessage('Wrong master password.');
		}
	}
?>
<div  class="box">
	<div class="headerbar"><h3>One Time Password Setup</h3></div>
	<div class="insidebox">
		<b>It is VERY important to use a strong master password. </b><br />Use one from the password generator and save it somewhere safe.<br /><br />
		<form action="setupskey.php" method="post">
			<table>
			<?php
				if(PB::RequiresOTP($token, $key))
				{
					echo '<tr><td>Original Master Password: &nbsp;</td><td><input type="password" name="oldmaster" value="" /></td></tr>';
				}
			?>
			<tr><td>New master password: </td><td><input type="password" name="master1" value="" /></td></tr>
			<tr><td>Confirm: </td><td><input type="password" name="master2" value="" /></td></tr>
			<tr><td></td><td><input type="submit" name="submit" value="Get One Time Passwords" /></td></tr>
			</table>
		</form>
	</div>
</div>
<?php
	if(PB::RequiresOTP($token, $key))
	{
		echo '	<div  class="box">
				<div class="headerbar"><h3>Disable One Time Passwords</h3></div>
				<div class="insidebox">
					<form action="setupskey.php" method="post">
						Master Password: <input type="password" name="oldmaster" value="" />
						<input type="submit" name="disable" value="Disable" />
					</form>
				</div>
			</div>';
	}
	include ('shortcuts/randomdata.php');
	include ('shortcuts/footer.php');
	include ('libs/sqlclose.php');
?>
