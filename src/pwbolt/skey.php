<?php
	require_once ('libs/passwordbolt.php');
	if(($user = PB::CheckLogin(false)) == "")
	{		
		header( 'Location: index.php' );
	}
	else
	{
		$key = PB::GetKey($user);
		$token = PB::GetToken($user);
	}

	if(PB::PassedOTP($user, $token, $key))
	{
		Header('Location: passwords.php');
	}

	if(isset($_POST['otp'])) //validate the otp they gave us
	{
		if(PB::ValidateOTP($user, $token, security::smartslashes($_POST['otp'])))
		{
			Header('Location: passwords.php');
		}
		else
		{
			$failed = true;
		}
	}
	
	if(isset($_POST['cancel']))
	{
		PB::Logout($user);
	}
	include ('shortcuts/top.php');
?>
<div  class="box">
	<div class="headerbar"><h3>One Time Password</h3></div>
	<div class="insidebox">
		<?php
			$num = PB::GetOTPNumber($token);
			if($num <= 0)
			{
				echo 'You have run out of one time passwords! Click <a href="setupskey.php">here</a> to get another password sheet.';
			}

			if(isset($failed) && $failed)
			{
				echo "Wrong one time password!<br /><br />";
			}
			echo "A one time password is required to login to your account. Please provide password #" . ($num );
		?>
		
		<form action="skey.php" method="post" >
			<input type="password"" name="otp" value="" />
			<input type="submit" name="submit" value="Login" />
			<input type="submit" name="cancel" value="Cancel" />
		</form>
		Click <a href="setupskey.php">here</a> if you lost your password sheet.
	</div>
</div>
<?php
	include ('shortcuts/footer.php');
	include ('libs/sqlclose.php');
?>
