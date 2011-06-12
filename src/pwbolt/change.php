<?php	//This page allows the user to change their account password password.
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
require_once ('libs/passwordbolt.php');
	if(($user = PB::CheckLogin()) == "")
	{
		header( 'Location: index.php' ) ;
	}
	else
	{
		$key = PB::GetKey($user);
		$token = PB::GetToken($user);
	}


	if (isset($_POST['delete']))
	{
		include('shortcuts/top.php');
						echo '<div  class="box">
		<div class="headerbar"><h3>Confirm Delete Passwords</h3></div>
			<div id="login" class="insidebox">
			<form action="change.php" method="post" enctype="multipart/form-data">
			<table style="margin: 0 auto;" cellspacing="10px">
						<tr><td>Username</td><td>' . security::xsssani($user) . '</td></tr>
						<tr><td>Old Password</td><td> <input type="password" autocomplete="off" name="origpass" /> </td></tr>
						<tr><td>Old Keyfile</td><td> <input type="file" name="origfile" id="origfile" /> </td></tr>	
					
						<tr><td><input type="submit" name="delete2" value="DELETE PASSWORDS" />	<input type="submit" name="cancel" value="Cancel" /></td><td></td></tr>						
					</table>
				</form>
				<b>Warning:</b> This will delete your passwords for the login information you provided.
			</div>
		</div>';
	}
	else if(isset($_POST['deleteacc']))
	{
				include('shortcuts/top.php');
						echo '<div  class="box">
		<div class="headerbar"><h3>Confirm Delete Account</h3></div>
			<div id="login" class="insidebox">
			To delete your account you must provide the key given to you when you created your account.
			<form action="change.php" method="post" enctype="multipart/form-data">
			<table style="margin: 0 auto;" cellspacing="10px">
						<tr><td>Username</td><td>' . security::xsssani($user) . '</td></tr>
						<tr><td>Deletion Key</td><td> <input type="password" autocomplete="off" name="origpass" /> </td></tr>					
						<tr><td><input type="submit" name="deleteacc2" value="DELETE ACCOUNT" />	<input type="submit" name="cancel" value="Cancel" /></td><td></td></tr>						
					</table>
				</form>
				<b>Warning:</b> This will delete the username from the account list, hash salts, and email address. This will not delete the ciphertexts from the database, but it will make them impossible to decrypt even with your login information.
			</div>
		</div>';
	}
	else if(isset($_POST['deleteacc2']))
	{

		$delkey = security::smartslashes($_POST['origpass']);

		if(PB::DeleteAccount($user, $delkey))
		{
			$reason = "Account deleted.";
		}
		else
		{
			$reason = "Wrong deletion key.";
		}

	}
	else if ( isset($_POST['delete2']))
	{
		PB::DeletePasswords($token);
		$reason = "Passwords for the provided login information deleted. You may delete the notepad manually.";
	}
	else if(isset($_POST['submit'])) //the user has submitted the new password
	{

		//load the old password
		$oldpass = security::smartslashes($_POST['origpass']);
		$filename = security::smartslashes($_FILES["origfile"]["tmp_name"]);
		//load the new password
		$newpass = security::smartslashes($_POST['newpass']);
		$newpass2 = security::smartslashes($_POST['newpass2']);
		$newfilename = security::smartslashes($_FILES["newfile"]["tmp_name"]);
	
		//load the old keyfile
		$keyfile = "";
		if($_FILES["origfile"]["error"] == 0 && isset($_FILES["origfile"]["tmp_name"]))
		{
			if(filesize($filename) < (1024 * 1024 * 10))
			{
				$keyfile = file_get_contents($filename);
			}
		}
 	  
		//load the new keyfile
		$newkeyfile = "";
		if($_FILES["newfile"]["error"] == 0 && isset($_FILES["newfile"]["tmp_name"]))
		{
			if(filesize($newfilename) < (1024 * 1024 * 10))
			{
				$newkeyfile = file_get_contents($newfilename);
			}
		}

		if($newpass == $newpass2)
		{
			$num = PB::ChangePassword($user, $key, $oldpass, $keyfile, $newpass, $newkeyfile);
			if($num == 1) $s = 's were'; else $s = ' was';
			$reason = $num . ' password' . $s . ' successfully moved for the login information you provided. <br />Please <a href="logout.php">login again</a> with the new password.';
		}

	}

	if(!isset($_POST['delete']) && !isset($_POST['deleteacc']))
	{
		include('shortcuts/top.php');
	}

	if(isset($reason))	//there is something that must be displayed to the user such as wrong password or passwords didn't match
	{
		echo '<div  class="box">
			<div class="headerbar"><h3>Results</h3></div>
			<div class="insidebox">';
		echo $reason;
		echo '</div></div>';
	}
?>

<!--Box for the old password info-->
<div  class="box">
<div class="headerbar"><h3>Old</h3></div>
	<div id="login" class="insidebox">
		<form action="change.php" method="post" enctype="multipart/form-data">
			<table cellspacing="10px">
				<tr><td>Username</td><td> <?php echo security::xsssani($user); ?> </td></tr>
				<tr><td>Old Password</td><td> <input type="password" autocomplete="off" name="origpass" /> </td></tr>
				<tr><td>Old Keyfile</td><td> <input type="file" name="origfile" id="origfile" /> </td></tr>					
			</table>

	</div>
</div>

<!--Box for the new password info-->
<div  class="box">
<div class="headerbar"><h3>New</h3></div>
	<div id="login" class="insidebox">
			<table cellspacing="10px">
				<tr><td>Username</td><td> <?php echo security::xsssani($user); ?> </td></tr>
				<tr><td>New Password</td><td> <input type="password" autocomplete="off" name="newpass" /> </td></tr>
				<tr><td>Confirm Password</td><td> <input type="password" autocomplete="off" name="newpass2" /> </td></tr>
				<tr><td>New Keyfile</td><td> <input type="file" name="newfile" id="file" /> </td><td>&nbsp;<a href="random.php" target="_blank">Random Keyfile</a></td></tr>
				<tr><td></td><td><input type="submit" name="submit" value="Change Password" /></td></tr>						
			</table>
		</form>
		<b>Warning:</b> If you lose your username, password, or keyfile, it is impossible to recover your passwords.
	</div>
</div>


<div  class="box">
<div class="headerbar"><h3>One Time Passwords</h3></div>
	<div id="login" class="insidebox">
		<form action="change.php" method="post">
			Click <a href="setupskey.php">here</a> to setup one time password authentication.
		</form>
	</div>
</div>

<div  class="box">
<div class="headerbar"><h3>Delete</h3></div>
	<div id="login" class="insidebox">
		<form action="change.php" method="post">
			<table style="margin: 0 auto;" cellspacing="10px">
				<tr><td><input type="submit" name="delete" value="Delete Passwords" />	</td><td></td></tr>	
				<tr><td><input type="submit" name="deleteacc" value="Delete Account" />	</td><td></td></tr>						
			</table>
		</form>
	</div>
</div>

<?php
include ('shortcuts/footer.php');
include ('libs/sqlclose.php');
?>
