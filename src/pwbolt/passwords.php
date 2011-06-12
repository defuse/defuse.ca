<?php //The password manager.
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
	require_once ('libs/mysql.login.php');
	require_once ('libs/passwordbolt.php');

	if(($user = PB::CheckLogin(true)) == "")
	{
		header( 'Location: index.php' );
	}
	else
	{
		$key = PB::GetKey($user);
		$token = PB::GetToken($user);
	}

	include('shortcuts/top.php');
	
	//WHAT IS IT?
	$nomatch = false;
	if(isset($_POST['newpassword'])) //USER IS ADDING A NEW PASSWORD
	{
		if($_POST['pass1'] == $_POST['pass2'] && isset($_POST['pass1']))
		{
			$id = "";
			if(isset($_POST['id']))
				$id = security::ServerDecrypt($_POST['id']);

			$passuser = security::smartslashes($_POST['username']);
			$password = security::smartslashes($_POST['pass1']);
			$description = security::smartslashes($_POST['description']);
			$url = security::smartslashes($_POST['url']);

			//encryptionsalt field is set by javascript, also used to determine if it is javascript encrypted or not
			$encryptionsalt = security::smartslashes($_POST['encryptionsalt']);
		
			//if $id has a value, the password will be updated and not added
			PB::AddUpdatePassword($id, $key, $token, $description, $passuser, $password, $url, $encryptionsalt);
			//Determines if javascript encrypted if $encryptionsalt is empty or not
		
			echo '<div  class="box">
				<div class="headerbar"><h3>Password Editor</h3></div>
				<div class="insidebox">';
			if(isset($_POST['id']))
			{
				echo 'Password Changed.';
			}
			else
			{
				echo 'Password Added.';
			}

			echo '
				</div>
				</div>
			';
		}
		else
		{
			$nomatch = true;
		}
	}
	else if (isset($_POST['view'])) //USER IS VIEWING A PASSWORD
	{
		$id = security::ServerDecrypt($_POST['id']);
		$doublecrypt = "";
		$encryptionSalt = "";
		$pwname = "";
		$username = "";
		$url = "";
		$password = "";
		//fills $doublecrypt to $password with their values
		PB::GetPassword($id,$token,$key, $doublecrypt, $encryptionSalt, $pwname, $username, $url, $password);
		$pwname = security::xsssani($pwname);
		$encryptionSalt = security::xsssani($encryptionSalt);
		$username = security::xsssani($username);
		$url = security::xsssani($username);
		$password = security::xsssani($password);
		//display the data:
		echo '
			<div  class="box">
				<div class="headerbar">
					<h3>View Password</h3>
				</div>
				<div class="insidebox">
					<center><h4>Highlight the black area to see your password.</h4>';
		echo '<b>' . $pwname . '</b><br />';
		if(!empty($username))
		{
			echo 'Username: ' . $username . '<br />';			
		}
		if(!empty($url))
		{
			echo 'Link: <a href="' . $url . '">' . $url . '</a>' ;
		}
		if($doublecrypt == "TRUE")
		{
			echo '	<div id="prompt">
					A password is required for in-browser encryption: <br />
					<input type="password" id="jspass" >
					<input type="button" value="Decrypt" id="decrypt" onclick="DecryptP(\'ciphertext\',\'jspass\',\'prompt\', \'' . $encryptionSalt . '\')" />
					<noscript><b>JavaScript is required</b></noscript>
				</div>';
		}
		echo 	'<p id="ciphertext" class="hiddentext">' . $password . '</p>
				<form action="passwords.php" method="post">
					<input type="submit" name="cancel" value="Hide" />
				</form>
			</center>
			</div><!--insidebox-->
		</div><!--box-->
		';
	}
	else if(isset($_POST['delete'])) //USER CLICKED DELETE BUTTON
	{
		$description = security::smartslashes($_POST['description']);
		$id = security::ServerDecrypt($_POST['id']);
		$description = security::xsssani(security::Decrypt($key,$description));
			echo '
				<div  class="box">
					<div class="headerbar">
						<h3>Delete Password</h3>
					</div>
					<div class="insidebox">
						<center>
							<h4>Delete ' . $description . '? </h4>
							<form action="passwords.php" method="post">
								<input type="hidden" name="id" value="' . security::ServerEncrypt($id) . '" />
								<input type="submit" name="finaldelete" value="Delete" />
								<input type="submit" name="cancel" value="Cancel" />
							</form>
						</center>
					</div>
				</div>
					';
	}
	else if(isset($_POST['finaldelete'])) //USER CONFIRMED THE DELETE
	{
		//very important that the decryption authenticates the ciphertext, 
		//or else in CBC mode an attacker can change the decryption result w/o knowing the key
		$id = security::ServerDecrypt($_POST['id']);
		//token is required for added protection
		PB::DeletePassword($id, $token);
	}

	//contains the javascript functions necessary for the crypto
	include('shortcuts/jscrypto.html');
?>

<!--Saved password list-->
<div  class="box">
<div class="headerbar"><h3>Your Passwords</h3></div>
	<div  class="insidebox">

<?php
	$result = PB::GetPasswordList($token, $key);
	if(count($result) == 0)
	{
		echo 'You currently have no passwords.';
	}
	else
	{
		echo '<table style="margin: 0 auto;" border="0" >'; //start the table
		for($i = 0; $i < count($result); $i++)
		{
			$row = $result[$i];
			//antiXSS
			$description = security::xsssani($row['description']);
			$url = security::xsssani($row['url']);
			$id = security::xsssani(security::ServerEncrypt($row['id']));

			//Show the list
			echo '<tr>
				<td  class="namebox">&nbsp;' . $description . '&nbsp;</td>
				<td>
					<form action="passwords.php" method="post">
						<input type="hidden" name="id" value="' . $id . '" />							<input type="submit" name="view" value="View" />
					</form>
				</td>
				<td>
					<form action="passwords.php" method="post">						<input type="hidden" name="id" value="' . $id . '" />
						<input type="submit" name="editpassword" value="Change" />
					</form>
				</td> 
				<td>
					<form action="passwords.php" method="post">
						<input type="hidden" name="id" value="' . $id . '" />
						<input type="hidden" name="description" value="' . security::Encrypt($key,$description) . '" />						<input type="submit" name="delete" value="Delete">
					</form>
				</td>
			</tr>';
		}
		echo "</table>"; // end the table
	}
	
?>
	</div>
</div>

<!--Create new password/edit password box-->
<div  class="box">
<div class="headerbar"><h3><?php if( isset($_POST['editpassword'])) { echo 'Change'; } else { echo 'New'; } ?> Password</h3></div>
	<div class="insidebox">
		<form action="passwords.php" method="post" >
		<?php
			
			//clear the variables just incase an attacker can make isset(post['editpassword]') and have these vars containing diff data using the code above
			$id = "";
			$description = "";
			$url = "";
			$password = "";
			$passuser = "";
			$buttontext = "Add";
			if( isset($_POST['editpassword'])) //the user is editing a password, so load the password they are changing
			{ 
				$id = security::ServerDecrypt($_POST['id']);
				$editdoublecrypt = "";
				$encryptionsalt = "";
				$description = "";
				$passuser = "";
				$url = "";
				$password = "";
				PB::GetPassword($id, $token, $key, $editdoublecrypt, 
						$encryptionSalt, $description, $passuser, $url, $password);
				
				$id = security::xsssani(security::ServerEncrypt($id));
				$description = security::xsssani($description);
				$url = security::xsssani($description);
				$passuser = security::xsssani($passuser);
				$password = security::xsssani($password);

				echo '<input type="hidden" name="id" value="' . $id . '">';
				if($editdoublecrypt == "TRUE") 
				{
					$password = ""; //we want them to have to provide both passwords again for security
					echo '<center><b>This password is encrypted with in-browser encryption. <br />You must provide the original password and encrypt it again.</b></center>';
				}
				$buttontext = "Change";
			}
			if($nomatch) //passwords didn't match (when creating/changing pw)
			{
				echo '<center><p style="color:red">Passwords did not match</p></center>';
			}
			//show the form
			$randencryptionSalt = security::xsssani(bin2hex(security::SuperRand()));
			echo '
				<table style="margin: 0 auto;">
					<tr>
						<td>Name</td>
						<td><input type="text" name="description" value="' . $description . '" autocomplete="off" /></td>
					</tr>

					<tr>
						<td>Link URL</td>
						<td> <input type="text" name="url" value="' . $url . '" autocomplete="off" /> </td>
					</tr>

					<tr>
						<td>Username</td>
						<td> <input type="text" name="username" value="' . $passuser . '" autocomplete="off" /> </td>
					</tr>

					<tr>
						<td>Password</td>
						<td> <input type="password" value="' . $password . '" autocomplete="off" name="pass1" id="pass1" /></td>
					</tr>

					<tr>
						<td>Confirm Password&nbsp;</td>
						<td> <input type="password" value="' . $password . '" autocomplete="off" name="pass2" id="pass2" /></td>
					</tr>
					<tr><td>&nbsp;</td></tr>
				</table>
					
				<div id="encry" >
				<table style="margin: 0 auto;">
					<tr><td><b>In-Browser Encryption</b></td><td>&nbsp;(optional) <noscript><b>Requires JavaScript</b></noscript> </td></tr>
					<tr><td>Password:</td><td><input type="password" id="e1"/><br /></td></tr>
					<tr><td>Confirm:</td><td><input type="password" id="e2"d/><br /></td></tr>
										<!--First arguments are names of html elements to modify, see the function for more details-->	
					<tr><td></td><td><input type="button" value="Encrypt" name="encrypt" onclick="EncryptT(\'pass1\',\'pass2\',\'e1\',\'e2\',\'encry\',\'' . $randencryptionSalt . '\');" /></td></tr>
				</table>					
				</div> 
				<br />
				<table style="margin: 0 auto;">
					<tr>
						<td><input type="submit" name="newpassword" value="' . $buttontext . '" /></td>
						<td><input type="submit" name="cancel" value="Cancel" /></td>
					</tr>
				</table>
				<!--Javascript fills this with the salt when it has been encrypted-->
				<input type="hidden" value="" name="encryptionsalt" id="encryptionsalt" />';
		?>
		</form>
	</div>
</div>

<?php include ('shortcuts/randomdata.php'); ?>

<?php
	include ('shortcuts/footer.php');
	include ('libs/sqlclose.php');
?>
