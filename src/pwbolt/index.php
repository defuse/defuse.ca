<?php //The main homepage, not visible to users that are logged in so don't put important services here.
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
	//if the user is logged in, redirect them to the passwords page.
	$dontshow = false; //only show the login box if the user isn't logged in
	if(($user = PB::CheckLogin(false)) != "")
	{
		if(PB::CheckLogin(true) == "") //user is logged in but hasn't passed the skey stage yet
		{
			Header('Location: skey.php');
		}
		$dontshow = true; //don't show the login box, because they are logged in
	}

	include ('shortcuts/top.php');
?>
<!--LOGIN BOX-->
<?php
//Show the login box and any custom messages such as account created, invalid password etc...
if($dontshow == false) //only show if the user isnt logged in.
{
	echo '
	<div  class="box">
	<div class="headerbar"><h3>Login</h3></div>
		<div id="login" class="insidebox">
			<b>*DO NOT LOG ON FROM AN UNTRUSTED COMPUTER*</b>
			<form action="login.php" method="post" enctype="multipart/form-data">';
				//When an account is created, createaccount redirects to index.php?completed=true
	if(isset($_GET['completed'])) 
	{
		echo 'Your account has been created, you can now login. <br />When deleting your account, you will need to provide this key:<br />' . security::xsssani($_GET['delete']) ;
	} 
	echo '<table style="margin: 0 auto; font-weight:bold" >
		<tr><td>Username:</td><td> <input type="text" name="user" autocomplete="off" /> </td></tr>
		<tr><td>Password:</td><td> <input type="password" autocomplete="off" name="pass" id="passbox" /></td></tr>
		<tr><td>Keyfile:</td><td> <input type="file" name="file" id="file" /> </td></tr>
		<tr><td>Stay logged in for:&nbsp;</td><td> <input type="radio" name="time" value="15" />15 minutes &nbsp;&nbsp;<input type="radio" name="time" value="60" />60 minutes <br /><input type="radio" name="time" value="1440" />24 hours&nbsp;&nbsp;<input type="radio" name="time" value="525600" />Forever</td></tr>
		<tr><td><input type="submit" name="submit" value="Login" />	</td><td><a href="createaccount.php">Register </a></td></tr>';
	//?invalid=true when redirected from login.php (they signed in with bad info)
	if(isset($_GET['invalid'])) { echo 'Wrong username.';} 
								
	echo '</table>
	
			</form>
			<a href="random.php">Download random keyfile</a>
		</div>
	</div>';
}
?>
<!--ABOUT BOX-->
<div class="box">
<div class="headerbar"><h3>About</h3></div>
	<div class="insidebox" >
	<center>
		<h2>Still using the same password for everything?</h2><br />
		
		Password Bolt is a password manager designed from the ground up to provide you with properly implemented, 
		strong encryption so you can use strong passwords for everything, without having to remember them all! 
		<br /><br />
		<b>Easily manage all of your passwords in one location.</b><br />
		<img src="images/passlist.jpg" /><br /><br />
		
		<b>Multifactor authentication - Use keyfiles for increased security.</b><br />
		<img src="images/login.jpg" /><br /><br />
		
		<b>Use strong passwords for everything.</b><br />
		<img src="images/view.jpg" /><br /><br />
	
		<b>Plausible deniability</b><br />
		Login with any password to use fake accounts. You can give your fake password to anyone forcing you to give up your password, they won't be able to prove its fake.
		<br /><br />

		<b>No false claims of security</b><br />
		- Strong cryptography (256 bit AES-TwoFish encryption)<br />
		- Keys derrived from HMAC-SHA512 and HMAC-WHIRLPOOL<br />
		- Optional BLOWFISH encryption done by your web browser <br />
		<br />
		</center>

	</div>
</div>

<!--Ads-->
<div class="box">
	<div class="headerbar"><h3>Good Sites</h3></div>
	<div class="insidebox">
		<center>
			Password Bolt was made possible by:<br />
			<a href="http://www.grc.com" >
				<img src="images/GRC.jpg" alt="Gibson Research Corporation"  />
			</a>		
		</center>
	</div>
</div>
<?php include ('shortcuts/footer.php') ?>

<?php include ('libs/sqlclose.php'); ?>
