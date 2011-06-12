<?php //This is the register page.

    //This file is part of Password Bolt.

    //Password Bolt is free software: you can redistribute it and/or modify
    //it under the terms of the GNU General Public License as published by
    //the Free Software Foundation, either version 3 of the License, or
    //(at your option) any later version.

    //Password Bolt is distributed in the hope that it will be useful,
    //but WITHOUT ANY WARRANTY; without even the implied warranty of
    //MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    //GNU General Public License for more details.

    //You should have received a copy of the GNU General Public License
    //along with Password Bolt.  If not, see <http://www.gnu.org/licenses/>.

	require_once('libs/passwordbolt.php');

	$user = ""; $email ="";

	if(isset($_POST['user']))
	{
		$user = strtolower(trim(security::smartslashes($_POST['user'])));
		if(isset($_POST['email']))
			$email = security::smartslashes($_POST['email']);
	}
	
	if( !PB::UserExists($user) && !empty($user))
	{
		$keyfile = "";
		if($_FILES["file"]["error"] == 0 && isset($_FILES["file"]["tmp_name"]))
		{
			$keyfile = file_get_contents($_FILES["file"]["tmp_name"]);
		}
		//create the account
		$delete = PB::CreateAccount($user, $email, $pass, $keyfile);
		header( 'Location: index.php?completed=true&delete=' . $delete ) ;
	}
	else if(!isset($_POST['user'])) //not a postback
	{
		include ('shortcuts/top.php');
	}
	//missing field or passwords don't match
	else if ( PB::UserExists($_POST['user'])  ) 
	{
		include ('shortcuts/top.php');

		echo '<div class="box">
			<div class="headerbar"><h3>Error</h3></div>
			<div class="insidebox" style="color:red;">';
		
		//Show the error
		if(PB::UserExists($_POST['user']))
		{
			echo '- Username already taken <br />';
		}
		elseif(empty($_POST['user']))
		{
			echo 'Invalid username.';
		}
		echo '</div></div>';
	}

?> 
<!--Registration info box-->
<div class="box">
<div class="headerbar"><h3>Register</h3></div>
	<div id="login" class="insidebox">
		<form action="createaccount.php" method="post" enctype="multipart/form-data">
			<table style="margin: 0 auto;" cellspacing="10px">
			
			<b>Read the <a href="security.php">security tips</a> before creating your account.</b><br>
		
				<tr><td>Username</td><td> <input type="text" name="user" autocomplete="off"/> </td></tr>
				<tr><td>Email</td><td> <input type="text" name="email" autocomplete="off"/> <a href="privacy.php#stored"  >why?</a></td></tr>
				
				<!--<tr><td>Password</td><td> <input type="password" autocomplete="off" name="pass1" /> </td></tr>
				<tr><td>Confirm Password</td><td> <input type="password" autocomplete="off" name="pass2" /> </td></tr>
				<tr><td>Keyfile*</td><td> <input type="file" name="file" id="file" /> </td><td>&nbsp;<a href="random.php" target="_blank">Random Keyfile</a></td></tr> -->
				<tr><td><input type="submit" name="submit" value="Create Account" /></td></tr>						
			</table>
			You can log in with any password, so you can make a fake account with one password, and a hidden account with another.<br /><br />
			<b>Warning:</b> If you lose your username, password, or keyfile, it is impossible recover your passwords.
			<br />
		</form>
	</div>
</div>
<?php
	include ('shortcuts/randomdata.php');
	include ('shortcuts/footer.php');
	include ('libs/sqlclose.php');
?>
